<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\PdfToText\Pdf;
use Smalot\PdfParser\Parser;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Storage;
use App\Events\DocumentProcessingComplete;

class ProcessPDFDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $document;
    public $timeout = 3600;

    public function __construct($document)
    {
        $this->document = $document;
    }

    public function handle()
    {
        try {

            $path = env('AWS_EXT_URL') . '/' . $this->document->file_path;
            event(new DocumentProcessingComplete($this->document->id, 'processing'));
            // Parse PDF content
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();

            // Split into chunks (approximately 1000 tokens each)
            $chunks = $this->splitIntoChunks($text);

            // Process each chunk
            foreach ($chunks as $index => $chunk) {
                // Create embeddings using OpenAI
                $questionEmbedding = app('openaiservice')->getOpenAiEmbedding($chunk);

                $tokensUsed = $questionEmbedding['usage']['total_tokens'];
                // Use tokens via trait
                auth()->user()->useTokens($tokensUsed, 'embedding');

                // Store chunk with embeddings
                $this->document->chunks()->create([
                    'document_id' => $this->document->id,
                    'content' => $chunk,
                    'chunk_order' => $index,
                    'embeddings' => $questionEmbedding['data'][0]['embedding'],
                ]);
            }


            $instruct = 'Create a concise summary of the following document that can be used as context for future questions:';
            $finalPrompt = substr($text, 0, 15000);

            $response = app('openaiservice')->getOpenAiChat(config('openai.model_chat'), $finalPrompt, $instruct);
            $cleanedContent = trim(str_replace(['"""', "\n"], '', $response['content']));

            $this->document->update([
                'context' => $cleanedContent,
                'processing_status' => 'completed'
            ]);

            $tokensUsed = $response['token'];
            auth()->user()->useTokens($tokensUsed, 'chat');

            event(new DocumentProcessingComplete($this->document->id, 'completed'));

            return redirect()->to(route('filament.app.resources.documents.document', [ 'record' => $this->document->uuid]));
        }
        catch(\Exception $e) {
            throw new \Exception($e->getMessage());
            $this->document->update([
                'processing_status' => 'failed'
            ]);

            event(new DocumentProcessingComplete($this->document->id, 'failed'));

            return redirect()->to(route('filament.app.resources.documents.document', [ 'record' => $this->document->uuid]));
        }
    }

    private function splitIntoChunks($text, $maxChunkSize = 1000)
    {
        $chunks = [];
        $sentences = preg_split('/(?<=[.!?])\s+/', $text);
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            if (strlen($currentChunk) + strlen($sentence) > $maxChunkSize) {
                $chunks[] = $currentChunk;
                $currentChunk = $sentence;
            } else {
                $currentChunk .= ' ' . $sentence;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }
}
