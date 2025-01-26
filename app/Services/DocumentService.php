<?php

namespace App\Services;

use App\Models\Document;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessPDFDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;

class DocumentService
{

    public function saveDocumentService(Document $document)
    {
        if(empty($document->content) || $document->content == "<p></p>") {
            Notification::make()
                ->title('Document cannot be empty')
                ->warning()
                ->send();
            return;
        }

        try {
            $this->saveDocumentToStorageService($document, 'pdf', true);

            ProcessPDFDocument::dispatch($document);

            $this->dispatchNotificationService('document saved as pdf');

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Something went wrong')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function downloadDocumentService(Document $document)
    {
        if(empty($document->content) || $document->content == "<p></p>") {
            Notification::make()
                ->title('Document cannot be empty')
                ->warning()
                ->send();
            return;
        }

        try {
            $pdf = PDF::loadView('components.pdf-download', [
                'title' => $document->title,
                'content' => $document->content,
                'template' => $document->template,
                'font' => $document->font,
                'date' => now()->format('Y-m-d'),

                'users' => []
            ])
            ->setWarnings(false)
            ->setOptions([
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'dpi' => 150,
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial',
                "defaultPaperSize" => "a4",
                'enable_css_float' => true,
                'enable_html5_parser' => true
            ]);

            $fileName = $document->uuid . '.pdf';

            return response()->streamDownload(
                fn () => print($pdf->output()),
                $fileName
            );

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    public function saveDocumentToStorageService(Document $document, string $folder, bool $saveRecord): void
    {
        $pdf = PDF::loadView('components.preview-lg', [
            'title' => $document->title,
            'content' => $document->content,
            'template' => $document->template,
            'font' => $document->font,
            'date' => now()->format('Y-m-d'),
        ]);

        $fileName = $document->uuid . '.pdf';

        $path = $folder . '/' . $fileName;

        if(Storage::disk(config('filesystems.default'))->exists($path)) {
            Storage::disk(config('filesystems.default'))->delete($path);
        }

        if($saveRecord) {
            // Create document record
            $document->update([
                'type' => 'imported',
                'file_path' => $path,
                'processing_status' => 'processing',
                'meta' => [
                    'stored_filename' => $document->uuid,
                    'imported_at' => now()->toDateTimeString(),
                ],
            ]);
        }

        Storage::disk(config('filesystems.default'))->put($path, $pdf->output(), config('filesystems.default'));
    }


    public function dispatchNotificationService($message, $type = 'success'): void
    {
        Notification::make()
            ->title($message)
            ->$type()
            ->send();
    }
}
