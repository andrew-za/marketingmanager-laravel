<?php

namespace App\Services\AI\Contracts;

interface AiProviderInterface
{
    public function generateContent(string $prompt, array $options = []): array;
    
    public function generateImage(string $prompt, array $options = []): array;
    
    public function calculateCost(int $tokens, string $model): float;
    
    public function getProviderName(): string;
}


