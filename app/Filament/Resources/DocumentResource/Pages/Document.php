<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Pages\Docsign;
use App\Filament\Resources\DocumentResource;
use Filament\Resources\Pages\Page;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use App\Models\Template;
use Illuminate\Support\Str;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Form;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;

class Document extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static string $resource = DocumentResource::class;

    protected static string $view = 'filament.resources.document-resource.pages.document';

    public ?array $data = [];

    public $signers = [];

    public $record;

    public $currentTemplate = null;

    public $showSignerBlock = true;

    public $errorMessage = null;

    public $suggestion_key;

    public function getHeading(): string
    {
        return "";
    }

    public function mount(int | string $record)
    {
        if (auth()->user() && !auth()->user()->hasVerifiedEmail()) {
            return redirect()->to(route('filament.app.resources.documents.index'))->with([
                'email-verification' => 'You must first confirm your email address for full access to the system.'
            ]);
        }

        $this->record = $this->resolveRecord($record);
        abort_if(is_null($this->record), 404);
        $this->form->fill($this->record->toArray());
        $this->getTemplate();
        $this->suggestion_key = Str::random(10);
    }

    protected function getListeners()
    {
        return [
            'signers-updated' => 'handleSignersUpdate'
        ];
    }

    public function handleSignersUpdate($data)
    {
        $this->signers = $data['signers'];
    }

    public function getTemplate()
    {
        if($this->record->template) {
            $this->currentTemplate = Template::query()->where('title', $this->record->template)?->first();
        }
    }

    public function generateContent($data)
    {
        try {
            $finalPrompt = $data['selectedText']
                ? "Selected text: \"{$data['selectedText']}\"\n\nInstructions: {$data['prompt']}"
                : $data['prompt'];

            $response = app('openaiservice')->getOpenAiChat(config('openai.model'), $finalPrompt);

            $cleanedContent = trim(str_replace(['"""', "\n"], '', $response['content']));

            $this->dispatch('content-generated',
                content: $cleanedContent,
                hadSelection: !empty($data['selectedText'])
            );

            $tokensUsed = (int) $response['token'];
            auth()->user()->useTokens($tokensUsed, 'chat');

            return $cleanedContent;
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Failed to generate content')
                ->body($e->getMessage())
                ->send();
            $this->dispatch('error-occurred', message: 'Failed to generate content: ' . $e->getMessage());
        }
    }

    public function save()
    {
        preg_match_all('/\/x(\d+)_sign\//', $this->data['content'], $matches);
        $codePattern = $matches[0];

        $this->data['code'] = [
            'code' => $codePattern
        ];

        $this->record->update($this->data);

        $this->isDocumentNotValidForDocuSign();

        app('documentservice')->dispatchNotificationService('Saved!');
    }

    public function deleteDocument()
    {
        $this->record->delete();

        app('documentservice')->dispatchNotificationService('Document deleted!');

        return redirect()->to(DocumentResource::getUrl());
    }

    protected function getFormModel(): string
    {
        return Document::class;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Document title')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state) {
                        $this->record->update(['title' => $state]);
                        app('documentservice')->dispatchNotificationService('Title updated!');
                    }),

                DatePicker::make('expiration_date')
                    ->native(false)
                    ->prefix('Expired at')
                    ->minDate(now())
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->record->update(['expiration_date' => $state]);
                        app('documentservice')->dispatchNotificationService('Document expiration date updated!');
                    })
                    ->closeOnDateSelection()
                    ->helperText(function ($state) {
                        if (!$state) return null;

                        $date = \Carbon\Carbon::parse($state);
                        $isExpired = $date->isPast();

                        return view('components.status-helper-text', [
                            'text' => $isExpired
                                ? 'Expired ' . $date->diffForHumans(['parts' => 2])
                                : 'Expires in ' . now()->diffForHumans($date, [
                                    'parts' => 2,
                                    'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE
                                ]),
                            'isExpired' => $isExpired
                        ]);
                    }),

                Toggle::make('expiration_date_reminder')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-calendar')
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        logger('Reminder updated');
                        $this->record->update(['expiration_date_reminder' => $state]);
                        app('documentservice')->dispatchNotificationService('Expiration Reminder updated!');
                    })
            ])
            ->statePath('data');
    }

    protected function resolveRecord(int | string $key): mixed
    {
        return static::getResource()::resolveRecordRouteBinding($key);
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function saveDocument()
    {
        app('documentservice')->saveDocumentService($this->record);
    }

    public function isDocumentNotValidForDocuSign()
    {
        if (empty($this->record->content) || $this->record->content == "<p></p>") {
            $this->showSignerBlock = false;
            $this->errorMessage = "The document content cannot be empty.";
            return true;
        }

        if (!Str::contains($this->record->content, ['/x1_sign/', '/x2_sign/'], ignoreCase: true)) {
            $this->showSignerBlock = false;
            $this->errorMessage = "The document should contain a signature designated area.\n\nSelect a signature block from the DocuSign action menu and drag into the document.";
            return true;
        }

        if (count($this->signers) == 0) {
            $this->showSignerBlock = true;
            $this->errorMessage = "Signers cannot be empty.";
            return true;
        }

        return false;
    }

    public function signDocuSignDocument()
    {
        try {
            $this->validate([
                'signers.*.name' => 'required|string',
                'signers.*.email' => 'required|email',
                'signers.*.code' => 'required|string'
            ]);

            app('documentservice')->saveDocumentToStorageService($this->record, 'docusign', false);

            $this->record->update([
                'status' => 'pending',
                'code' => array_merge($this->document->code ?? [], [
                    'signers' => $this->signers
                ])
            ]);

            return redirect(Docsign::getUrl(panel: 'app') . '/' . $this->record->uuid);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Something went wrong!')
                ->danger()
                ->send();
        }
    }


    public function downloadDocument()
    {
        return app('documentservice')->downloadDocumentService($this->record);
    }

    private function saveDocumentToStorage(string $folder, bool $saveRecord): void
    {
        app('documentservice')->saveDocumentToStorageService($this->record, $folder, $saveRecord);
    }
}

