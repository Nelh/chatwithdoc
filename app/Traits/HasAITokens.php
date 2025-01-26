<?php

namespace App\Traits;
use Filament\Notifications\Notification;

trait HasAITokens
{
    public function hasEnoughTokens(int $requiredTokens): bool
    {
        return $this->available_tokens >= $requiredTokens;
    }


    public function hasEnoughStorage(int $requiredStorage): bool
    {
        return $this->storage_limit >= $requiredStorage;
    }

    public function useTokens(int $tokens, string $purpose, int $storage = 0): void
    {
        if (!$this->hasEnoughTokens($tokens)) {
            throw new \Exception('Insufficient tokens');
        }

        $this->available_tokens -= $tokens;
        $this->storage_limit -= $storage;

        // Add to token history
        $history = $this->token_history ?? [];
        $history[] = [
            'tokens_used' => $tokens,
            'storage_used' => $storage,
            'purpose' => $purpose,
            'date' => now()->toDateTimeString()
        ];

        $this->token_history = $history;
        $this->save();
    }

    public function addTokens(int $tokens): void
    {
        $this->available_tokens += $tokens;
        $this->save();
    }

    public function addStorage(int $storage): void
    {
        $this->storage_limit += $storage;
        $this->save();
    }

    public function setStorageAndToken(int $tokens, int $storage): void
    {
        $this->available_tokens = $tokens;
        $this->storage_limit = $storage;
        $this->save();
    }

    public function getTokenHistory(int $limit = 5): array
    {
        return array_slice($this->token_history ?? [], -$limit);
    }
}
