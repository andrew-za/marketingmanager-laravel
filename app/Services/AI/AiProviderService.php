<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AiProviderInterface;
use App\Services\AI\Providers\OpenAiProvider;
use App\Services\AI\Providers\GeminiProvider;
use InvalidArgumentException;

class AiProviderService
{
    private array $providers = [];

    public function __construct()
    {
        $this->providers['openai'] = new OpenAiProvider();
        $this->providers['gemini'] = new GeminiProvider();
    }

    public function getProvider(string $provider = null): AiProviderInterface
    {
        $provider = $provider ?? config('services.ai.default_provider', 'openai');
        
        if (!isset($this->providers[$provider])) {
            throw new InvalidArgumentException("AI provider '{$provider}' is not supported.");
        }

        return $this->providers[$provider];
    }

    public function registerProvider(string $name, AiProviderInterface $provider): void
    {
        $this->providers[$name] = $provider;
    }
}


