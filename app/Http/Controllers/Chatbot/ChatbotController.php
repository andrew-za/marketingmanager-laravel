<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chatbot\CreateChatbotRequest;
use App\Http\Requests\Chatbot\UpdateChatbotRequest;
use App\Http\Resources\Chatbot\ChatbotResource;
use App\Models\Chatbot;
use App\Services\Chatbot\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbotService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $query = Chatbot::where('organization_id', $organizationId)
            ->with(['creator', 'conversations', 'leads']);

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $chatbots = $query->orderBy('created_at', 'desc')->paginate();

        return ChatbotResource::collection($chatbots);
    }

    public function store(CreateChatbotRequest $request): JsonResponse
    {
        $chatbot = $this->chatbotService->createChatbot(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new ChatbotResource($chatbot),
            'message' => 'Chatbot created successfully.',
        ], 201);
    }

    public function show(Chatbot $chatbot): JsonResponse
    {
        $this->authorize('view', $chatbot);

        $chatbot->load(['creator', 'conversations', 'leads']);

        return response()->json([
            'success' => true,
            'data' => new ChatbotResource($chatbot),
        ]);
    }

    public function update(UpdateChatbotRequest $request, Chatbot $chatbot): JsonResponse
    {
        $this->authorize('update', $chatbot);

        $chatbot = $this->chatbotService->updateChatbot(
            $chatbot,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new ChatbotResource($chatbot),
            'message' => 'Chatbot updated successfully.',
        ]);
    }

    public function destroy(Chatbot $chatbot): JsonResponse
    {
        $this->authorize('delete', $chatbot);

        $this->chatbotService->deleteChatbot($chatbot);

        return response()->json([
            'success' => true,
            'message' => 'Chatbot deleted successfully.',
        ]);
    }

    public function startConversation(Request $request, Chatbot $chatbot): JsonResponse
    {
        $request->validate([
            'session_id' => ['required', 'string'],
            'visitor_name' => ['nullable', 'string'],
            'visitor_email' => ['nullable', 'email'],
            'ip_address' => ['nullable', 'ip'],
        ]);

        $conversation = $this->chatbotService->startConversation(
            $chatbot,
            $request->session_id,
            $request->only(['visitor_name', 'visitor_email', 'ip_address'])
        );

        return response()->json([
            'success' => true,
            'data' => $conversation,
            'message' => 'Conversation started successfully.',
        ], 201);
    }

    public function captureLead(Request $request, Chatbot $chatbot): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
            'conversation_id' => ['nullable', 'exists:chatbot_conversations,id'],
        ]);

        $conversation = $request->conversation_id 
            ? \App\Models\ChatbotConversation::find($request->conversation_id)
            : null;

        $lead = $this->chatbotService->captureLead(
            $chatbot,
            $request->only(['name', 'email', 'phone', 'message', 'custom_fields']),
            $conversation
        );

        return response()->json([
            'success' => true,
            'data' => $lead,
            'message' => 'Lead captured successfully.',
        ], 201);
    }

    public function updateConversationFlow(Request $request, Chatbot $chatbot): JsonResponse
    {
        $this->authorize('update', $chatbot);

        $request->validate([
            'flow' => ['required', 'array'],
        ]);

        $chatbot = $this->chatbotService->updateConversationFlow(
            $chatbot,
            $request->flow
        );

        return response()->json([
            'success' => true,
            'data' => new ChatbotResource($chatbot),
            'message' => 'Conversation flow updated successfully.',
        ]);
    }

    public function updateLanguages(Request $request, Chatbot $chatbot): JsonResponse
    {
        $this->authorize('update', $chatbot);

        $request->validate([
            'languages' => ['required', 'array'],
            'default_language' => ['required', 'string', 'size:2'],
        ]);

        $chatbot = $this->chatbotService->updateSupportedLanguages(
            $chatbot,
            $request->languages,
            $request->default_language
        );

        return response()->json([
            'success' => true,
            'data' => new ChatbotResource($chatbot),
            'message' => 'Languages updated successfully.',
        ]);
    }

    public function updateBrandInfo(Request $request, Chatbot $chatbot): JsonResponse
    {
        $this->authorize('update', $chatbot);

        $request->validate([
            'brand_information' => ['required', 'array'],
        ]);

        $chatbot = $this->chatbotService->updateBrandInformation(
            $chatbot,
            $request->brand_information
        );

        return response()->json([
            'success' => true,
            'data' => new ChatbotResource($chatbot),
            'message' => 'Brand information updated successfully.',
        ]);
    }

    public function analytics(Request $request, Chatbot $chatbot): JsonResponse
    {
        $this->authorize('view', $chatbot);

        $analytics = $this->chatbotService->getAnalytics($chatbot, [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }
}

