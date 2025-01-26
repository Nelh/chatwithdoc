<?php

namespace App\Services;

use Rahul900day\Tiktoken\Tiktoken;

class OpenAITokenEstimator
{
    /**
     * Model specific token limits
     */
    const MODEL_LIMITS = [
        'gpt-4' => 8192,
        'gpt-4-32k' => 32768,
        'gpt-4o' => 128000,
        'gpt-3.5-turbo' => 4096,
        'gpt-3.5-turbo-16k' => 16384,
        'text-davinci-003' => 4097
    ];

    private $tiktoken;

    public function __construct()
    {
        $this->tiktoken = new Tiktoken();
    }

    /**
     * Count tokens for a chat completion request
     *
     * @param array $messages Array of message objects
     * @param string $model The model to use
     * @return array Contains token count and limit information
     */
    public function countChatTokens(array $messages, string $model = 'gpt-3.5-turbo'): array
    {
        $totalTokens = 0;

        // Token overhead for chat format
        $totalTokens += 3; // Every reply is primed with <|start|>assistant<|message|>

        foreach ($messages as $message) {
            // Add tokens for message role
            $totalTokens += 4; // Tokens for role formatting

            // Count tokens in content
            $content = $message['content'] ?? '';
            $encoder = $this->tiktoken->getEncodingForModel($model);
            $tokens = $encoder->encode($content);
            $totalTokens += count($tokens);

            // Add tokens for function calls if present
            if (isset($message['function_call'])) {
                $functionCall = json_encode($message['function_call']);
                $functionTokens = $encoder->encode($functionCall);
                $totalTokens += count($functionTokens);
            }
        }

        $modelLimit = self::MODEL_LIMITS[$model] ?? PHP_INT_MAX;

        return [
            'token_count' => $totalTokens,
            'exceeds_limit' => $totalTokens > $modelLimit,
            'model_limit' => $modelLimit,
            'remaining_tokens' => $modelLimit - $totalTokens
        ];
    }

    /**
     * Count tokens for a completion request
     *
     * @param string $prompt The prompt text
     * @param string $model The model to use
     * @return array Contains token count and limit information
     */
    public function countCompletionTokens(string $prompt, string $model = 'text-davinci-003'): array
    {
        $encoder = $this->tiktoken->getEncodingForModel($model);
        $tokens = $encoder->encode($prompt);
        $tokenCount = count($tokens);

        $modelLimit = self::MODEL_LIMITS[$model] ?? PHP_INT_MAX;

        return [
            'token_count' => $tokenCount,
            'exceeds_limit' => $tokenCount > $modelLimit,
            'model_limit' => $modelLimit,
            'remaining_tokens' => $modelLimit - $tokenCount
        ];
    }

    /**
     * Get raw token count for any text
     *
     * @param string $text
     * @param string $model
     * @return int
     */
    public function getTokenCount(string $text, string $model = 'gpt-3.5-turbo'): int
    {
        $encoder = $this->tiktoken->getEncodingForModel($model);
        $tokens = $encoder->encode($text);
        return count($tokens);
    }
}
