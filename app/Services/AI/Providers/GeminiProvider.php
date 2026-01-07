<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
    }

    public function generateContent(string $prompt, array $options = []): array
    {
        $model = $options['model'] ?? 'gemini-pro';
        $maxTokens = $options['max_tokens'] ?? 1000;
        $temperature = $options['temperature'] ?? 0.7;
        $systemPrompt = $options['system_prompt'] ?? 'You are a helpful assistant.';

        try {
            $response = Http::post("{$this->baseUrl}/models/{$model}:generateContent?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $systemPrompt . "\n\n" . $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => $maxTokens,
                    'temperature' => $temperature,
                ],
            ]);

            if ($response->failed()) {
                throw new \Exception('Gemini API error: ' . $response->body());
            }

            $data = $response->json();
            $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $tokensUsed = $data['usageMetadata']['totalTokenCount'] ?? 0;
            $cost = $this->calculateCost($tokensUsed, $model);

            return [
                'content' => $content,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
                'model' => $model,
            ];
        } catch (\Exception $e) {
            Log::error('Gemini API error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): array
    {
        throw new \Exception('Gemini does not support image generation. Use OpenAI DALL-E instead.');
    }

    public function calculateCost(int $tokens, string $model): float
    {
        $pricing = [
            'gemini-pro' => 0.00025 / 1000,
            'gemini-pro-vision' => 0.00025 / 1000,
        ];

        $pricePerToken = $pricing[$model] ?? $pricing['gemini-pro'];
        
        return $tokens * $pricePerToken;
    }

    public function getProviderName(): string
    {
        return 'gemini';
    }
}


