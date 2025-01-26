<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAiService {

    public function getOpenAiChat(string $model, $prompt, $instruct = "You are a helpful assistant.")
    {
        if(str_starts_with($model, 'gpt-3.5')) {
            $response = OpenAI::completions()->create([
                'model' => config('openai.model'),
                'prompt' => $prompt,
                'temperature' => 0.6,
                'max_tokens' => 3500
            ]);

            return [
                'content' => $response['choices'][0]['text'],
                'token' => $response['usage']['total_tokens']
            ];
        }

        else {
            $response = OpenAI::chat()->create([
                'model' => config('openai.model_chat'),
                'temperature' => 0.6,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $instruct
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ],
                ],
            ]);

            return [
                'content' => $response['choices'][0]['message']['content'],
                'token' => $response['usage']['total_tokens']
            ];
        }

    }

    public function getOpenAiEmbedding($text)
    {
        return OpenAI::embeddings()->create([
            'model' => config('openai.embedding_model'),
            'input' => $text,
        ]);
    }
}
