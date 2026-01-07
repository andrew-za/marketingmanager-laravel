<?php

namespace App\Services\Onboarding;

use App\Models\Organization;
use App\Models\User;
use App\Services\AI\AiProviderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnboardingService
{
    private AiProviderService $aiProviderService;

    public function __construct(AiProviderService $aiProviderService)
    {
        $this->aiProviderService = $aiProviderService;
    }

    /**
     * Process onboarding chat message and return AI response
     */
    public function processChatMessage(array $conversationHistory, string $userMessage, array $confirmedData): array
    {
        $systemPrompt = $this->buildSystemPrompt($confirmedData);
        $conversationContext = $this->buildConversationContext($conversationHistory, $userMessage);

        $provider = $this->aiProviderService->getProvider();
        
        $response = $provider->generateContent($conversationContext, [
            'system_prompt' => $systemPrompt,
            'model' => 'gpt-4',
            'temperature' => 0.7,
            'max_tokens' => 500,
        ]);

        return $this->parseAiResponse($response['content'], $userMessage, $confirmedData);
    }

    /**
     * Complete onboarding and create organization
     */
    public function completeOnboarding(User $user, array $confirmedData): Organization
    {
        return DB::transaction(function () use ($user, $confirmedData) {
            $organization = Organization::create([
                'name' => $confirmedData['name'] ?? 'My Organization',
                'slug' => Str::slug($confirmedData['name'] ?? 'my-organization'),
                'timezone' => $confirmedData['timezone'] ?? config('app.timezone', 'UTC'),
                'locale' => $confirmedData['locale'] ?? config('localization.default_locale', 'en'),
                'country_code' => $confirmedData['country_code'] ?? 'US',
                'status' => 'trial',
            ]);

            $user->organizations()->attach($organization->id, ['role_id' => 1]);

            return $organization;
        });
    }

    /**
     * Build system prompt for AI conversation
     */
    private function buildSystemPrompt(array $confirmedData): string
    {
        $prompt = "You are Jenna, a friendly and helpful AI marketing partner assistant helping users set up their MarketPulse account. ";
        $prompt .= "Your goal is to collect the following information through a natural conversation:\n";
        $prompt .= "1. Organization name (required)\n";
        $prompt .= "2. Website URL (optional, can be skipped)\n";
        $prompt .= "3. Business focus/industry (optional, can be detected from website)\n";
        $prompt .= "4. Business model: B2B, B2C, or D2C (optional)\n\n";

        if (!empty($confirmedData)) {
            $prompt .= "Already confirmed:\n";
            foreach ($confirmedData as $key => $value) {
                if ($value) {
                    $prompt .= "- " . ucfirst($key) . ": " . $value . "\n";
                }
            }
            $prompt .= "\n";
        }

        $prompt .= "Guidelines:\n";
        $prompt .= "- Be conversational and friendly\n";
        $prompt .= "- Ask one question at a time\n";
        $prompt .= "- If user says 'skip', acknowledge and move to next question\n";
        $prompt .= "- When organization name is confirmed, respond with: [CONFIRMED:name=value]\n";
        $prompt .= "- When website is confirmed, respond with: [CONFIRMED:website=value]\n";
        $prompt .= "- When business focus is confirmed, respond with: [CONFIRMED:focus=value]\n";
        $prompt .= "- When business model is confirmed, respond with: [CONFIRMED:businessModel=value]\n";
        $prompt .= "- When all required info is collected, say: [COMPLETE]\n";
        $prompt .= "- For business model question, suggest buttons: B2B, B2C, D2C, or skip\n";

        return $prompt;
    }

    /**
     * Build conversation context from history
     */
    private function buildConversationContext(array $conversationHistory, string $userMessage): string
    {
        $context = "";
        
        foreach ($conversationHistory as $message) {
            $role = $message['role'] === 'assistant' ? 'Jenna' : 'User';
            $context .= "{$role}: {$message['content']}\n\n";
        }

        $context .= "User: {$userMessage}\n\n";
        $context .= "Jenna:";

        return $context;
    }

    /**
     * Parse AI response and extract confirmed data
     */
    private function parseAiResponse(string $aiResponse, string $userMessage, array $confirmedData): array
    {
        $response = [
            'message' => $aiResponse,
            'confirmedData' => [],
            'completed' => false,
            'actionButtons' => null,
        ];

        if (strpos($aiResponse, '[COMPLETE]') !== false) {
            $response['completed'] = true;
            $response['message'] = str_replace('[COMPLETE]', '', $aiResponse);
        }

        if (preg_match('/\[CONFIRMED:(\w+)=([^\]]+)\]/', $aiResponse, $matches)) {
            $key = $matches[1];
            $value = trim($matches[2]);
            $response['confirmedData'][$key] = $value;
            $response['message'] = str_replace($matches[0], '', $aiResponse);
        }

        if (strpos(strtolower($aiResponse), 'business model') !== false || 
            strpos(strtolower($userMessage), 'business model') !== false) {
            $response['actionButtons'] = [
                ['label' => 'B2B', 'value' => 'B2B'],
                ['label' => 'B2C', 'value' => 'B2C'],
                ['label' => 'D2C', 'value' => 'D2C'],
                ['label' => 'Skip', 'value' => 'skip'],
            ];
        }

        if (in_array(strtoupper($userMessage), ['B2B', 'B2C', 'D2C'])) {
            $response['confirmedData']['businessModel'] = strtoupper($userMessage);
        }

        if (strtolower($userMessage) === 'skip') {
            $response['message'] = "No problem! We can skip that for now.";
        }

        $response['message'] = trim($response['message']);

        return $response;
    }
}

