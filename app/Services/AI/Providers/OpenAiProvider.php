<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', '');
    }

    public function generateContent(string $prompt, array $options = []): array
    {
        $model = $options['model'] ?? 'gpt-4';
        $maxTokens = $options['max_tokens'] ?? 1000;
        $temperature = $options['temperature'] ?? 0.7;
        $systemPrompt = $options['system_prompt'] ?? 'You are a helpful assistant.';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);

            if ($response->failed()) {
                throw new \Exception('OpenAI API error: ' . $response->body());
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            $tokensUsed = $data['usage']['total_tokens'] ?? 0;
            $cost = $this->calculateCost($tokensUsed, $model);

            return [
                'content' => $content,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
                'model' => $model,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI API error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): array
    {
        $size = $options['size'] ?? '1024x1024';
        $quality = $options['quality'] ?? 'standard';
        $style = $options['style'] ?? 'vivid';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/images/generations", [
                'prompt' => $prompt,
                'n' => 1,
                'size' => $size,
                'quality' => $quality,
                'style' => $style,
            ]);

            if ($response->failed()) {
                throw new \Exception('OpenAI API error: ' . $response->body());
            }

            $data = $response->json();
            $imageUrl = $data['data'][0]['url'] ?? '';
            $cost = $this->calculateImageCost($size, $quality);

            return [
                'image_url' => $imageUrl,
                'cost' => $cost,
                'size' => $size,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI Image API error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function calculateCost(int $tokens, string $model): float
    {
        $pricing = [
            'gpt-4' => ['input' => 0.03 / 1000, 'output' => 0.06 / 1000],
            'gpt-4-turbo' => ['input' => 0.01 / 1000, 'output' => 0.03 / 1000],
            'gpt-3.5-turbo' => ['input' => 0.0005 / 1000, 'output' => 0.0015 / 1000],
        ];

        $modelPricing = $pricing[$model] ?? $pricing['gpt-3.5-turbo'];
        
        return ($tokens * 0.5 * $modelPricing['input']) + ($tokens * 0.5 * $modelPricing['output']);
    }

    private function calculateImageCost(string $size, string $quality): float
    {
        $baseCost = 0.04;
        
        if ($quality === 'hd') {
            $baseCost = 0.08;
        }

        return $baseCost;
    }

    public function getProviderName(): string
    {
        return 'openai';
    }
}


