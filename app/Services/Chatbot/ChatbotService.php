<?php

namespace App\Services\Chatbot;

use App\Models\Chatbot;
use App\Models\ChatbotConversation;
use App\Models\ChatbotLead;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatbotService
{
    public function createChatbot(array $data, User $user): Chatbot
    {
        return DB::transaction(function () use ($data, $user) {
            $chatbot = Chatbot::create([
                ...$data,
                'organization_id' => $user->primaryOrganization()->id,
                'created_by' => $user->id,
                'embed_code' => $this->generateEmbedCode(),
            ]);

            return $chatbot;
        });
    }

    public function updateChatbot(Chatbot $chatbot, array $data): Chatbot
    {
        return DB::transaction(function () use ($chatbot, $data) {
            $chatbot->update($data);
            return $chatbot->fresh();
        });
    }

    public function deleteChatbot(Chatbot $chatbot): bool
    {
        return DB::transaction(function () use ($chatbot) {
            return $chatbot->delete();
        });
    }

    public function startConversation(Chatbot $chatbot, string $sessionId, array $visitorData = []): ChatbotConversation
    {
        return DB::transaction(function () use ($chatbot, $sessionId, $visitorData) {
            return ChatbotConversation::create([
                'chatbot_id' => $chatbot->id,
                'session_id' => $sessionId,
                'visitor_name' => $visitorData['name'] ?? null,
                'visitor_email' => $visitorData['email'] ?? null,
                'ip_address' => $visitorData['ip_address'] ?? null,
                'started_at' => now(),
            ]);
        });
    }

    public function endConversation(ChatbotConversation $conversation): ChatbotConversation
    {
        return DB::transaction(function () use ($conversation) {
            $conversation->update(['ended_at' => now()]);
            return $conversation->fresh();
        });
    }

    public function captureLead(Chatbot $chatbot, array $leadData, ?ChatbotConversation $conversation = null): ChatbotLead
    {
        return DB::transaction(function () use ($chatbot, $leadData, $conversation) {
            return ChatbotLead::create([
                'chatbot_id' => $chatbot->id,
                'conversation_id' => $conversation?->id,
                'name' => $leadData['name'],
                'email' => $leadData['email'],
                'phone' => $leadData['phone'] ?? null,
                'message' => $leadData['message'] ?? null,
                'custom_fields' => $leadData['custom_fields'] ?? [],
                'status' => 'new',
            ]);
        });
    }

    public function updateConversationFlow(Chatbot $chatbot, array $flow): Chatbot
    {
        $chatbot->update(['conversation_flow' => $flow]);
        return $chatbot->fresh();
    }

    public function updateSupportedLanguages(Chatbot $chatbot, array $languages, string $defaultLanguage = 'en'): Chatbot
    {
        $chatbot->update([
            'supported_languages' => $languages,
            'default_language' => $defaultLanguage,
        ]);
        return $chatbot->fresh();
    }

    public function updateBrandInformation(Chatbot $chatbot, array $brandInfo): Chatbot
    {
        $chatbot->update(['brand_information' => $brandInfo]);
        return $chatbot->fresh();
    }

    public function getAnalytics(Chatbot $chatbot, array $options = []): array
    {
        $startDate = $options['start_date'] ?? now()->subDays(30);
        $endDate = $options['end_date'] ?? now();

        $conversations = $chatbot->conversations()
            ->whereBetween('started_at', [$startDate, $endDate])
            ->get();

        $leads = $chatbot->leads()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'total_conversations' => $conversations->count(),
            'total_leads' => $leads->count(),
            'average_conversation_duration' => $conversations->avg('duration_seconds') ?? 0,
            'conversion_rate' => $conversations->count() > 0 
                ? ($leads->count() / $conversations->count()) * 100 
                : 0,
            'leads_by_status' => $leads->groupBy('status')->map->count(),
        ];
    }

    private function generateEmbedCode(): string
    {
        return '<script src="' . config('app.url') . '/chatbot/widget.js" data-chatbot-id="' . Str::random(16) . '"></script>';
    }
}

