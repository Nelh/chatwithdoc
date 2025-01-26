<?php

namespace App\Filament\Resources\DocumentResource\Actions;

use App\Models\Document;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Str;
use App\Jobs\ProcessPDFDocument;
use App\Services\OpenAITokenEstimator;
use App\Filament\Resources\DocumentResource;
use Filament\Notifications\Notification;

class ImportAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make('import')
            ->label('Import PDF - Chat w/ AI')
            ->color('success')
            ->icon('heroicon-o-document-text')
            ->action(function (array $data) {
                try {
                    $pdf_file = $data['pdf_file'];
                    $originalName = basename($pdf_file);

                    // $fileStoragePath = env('AWS_EXT_URL') . '/' . Storage::path($pdf_file->getRealPath());
                    $fileStoragePath = Storage::disk('s3')->url($pdf_file->getRealPath());

                    // Generate new filename with UUID
                    $uuid = Str::uuid();
                    $extension = pathinfo($pdf_file, PATHINFO_EXTENSION);
                    $newFilename = "{$uuid}.{$extension}";

                    $newFilenamePath = "pdf/{$newFilename}pdf";

                    // Move file to new location with UUID filename
                    Storage::disk(config('filesystems.default'))->put(
                        $newFilenamePath,
                        file_get_contents($fileStoragePath)
                    );

                    // Parse PDF content
                    $parser = new Parser();
                    $pdf = $parser->parseFile($fileStoragePath);
                    $content = $pdf->getText();
                    $context = $pdf->getText();

                    // Count tokens
                    $tokenEstimator = new OpenAITokenEstimator();
                    $tokenInfo = $tokenEstimator->countCompletionTokens($content);

                    // Check token limit (optional)
                    if ($tokenInfo['token_count'] > auth()->user()->available_tokens) { // Adjust limit as needed
                        throw new \Exception('PDF content exceeds maximum tokens allocated on your account. Current count: ' .
                            number_format($tokenInfo['token_count']));
                    }

                    // Create document record
                    $document = Document::create([
                        'title' => $data['title'],
                        'content' => $content,
                        'context' => $context,
                        'uuid' => $uuid,
                        'type' => 'imported',
                        'file_path' => $newFilenamePath,
                        'processing_status' => 'processing',
                        'user_id' => auth()->user()->id,
                        'meta' => [
                            'original_filename' => $originalName,
                            'stored_filename' => $newFilename,
                            'token_count' => $tokenInfo['token_count'],
                            'imported_at' => now()->toDateTimeString(),
                        ],
                    ]);

                    ProcessPDFDocument::dispatch($document);

                    // Clean up temporary file
                    Storage::disk(config('filesystems.default'))->delete($pdf_file->getRealPath());

                    Notification::make()
                        ->success()
                        ->title('PDF Import Started')
                        ->body("Your document is being processed ({$tokenInfo['token_count']} tokens). You will be notified when complete.")
                        ->persistent();
                }
                catch(\Exception $e) {
                    Notification::make()
                        ->danger()
                        ->title('Import Failed')
                        ->body($e->getMessage())
                        ->send();
                }
            })
            ->form([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\FileUpload::make('pdf_file')
                    ->label('PDF File')
                    ->required()
                    ->acceptedFileTypes(['application/pdf'])
                    ->storeFiles(false)
                    ->deletable(false)
                    ->panelLayout('grid')
                    ->visibility('public')
                    ->afterStateUpdated(function ($state, Forms\Components\FileUpload $component) {
                        if (!$state) return;

                        try {
                            // $fileStoragePath = env('AWS_EXT_URL') . '/' . Storage::path($state->getRealPath());
                            $fileStoragePath = Storage::disk('s3')->url($state->getRealPath());


                            // Quick token count check
                            $parser = new Parser();
                            $pdf = $parser->parseFile($fileStoragePath);
                            $content = $pdf->getText();

                            $tokenEstimator = new OpenAITokenEstimator();
                            $tokenInfo = $tokenEstimator->countCompletionTokens($content);

                            // Add informational message about token count
                            $component->helperText("Document contains approximately " .
                                number_format($tokenInfo['token_count']) . " tokens");

                            if ($tokenInfo['token_count'] > auth()->user()->available_tokens) {
                                $component->helperText('Warning: Document exceeds maximum tokens allocated on your account. Estimated tokens required '.
                                number_format($tokenInfo['token_count']) . " tokens");
                            }
                        } catch (\Exception $e) {
                            $component->helperText('Error analyzing PDF: ' . $e->getMessage());
                        }
                    }),
            ])
            ->modalHeading('Import PDF Document')
            ->disabledForm(auth()->user() && !auth()->user()->hasVerifiedEmail())
            ->modalContent(view('components.import-action'))
            ->successNotificationTitle('PDF imported successfully');
    }
}
