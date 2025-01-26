<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;
use Exception;

class Docsign extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.docsign';

    protected static bool $shouldRegisterNavigation = false;

    public Document $document;
    public $document_path = null;

    public static function getSlug(): string
    {
        return 'docsign/{uuid?}';
    }

    public function mount(?string $uuid = null)
    {
        if($uuid) {
            $doc = Document::where('uuid', $uuid)->where('status', '!=', 'signed')->first();
            if(!$doc) {
                abort(404);
            }

            $this->document = $doc;

            if($this->document){
                $path = 'docusign/' . $this->document->uuid . '.pdf';
            }

            if(Storage::disk(config('filesystems.default'))->exists($path)) {
                $this->document_path = Storage::disk('s3')->url($path);
            }
        }
    }
}
