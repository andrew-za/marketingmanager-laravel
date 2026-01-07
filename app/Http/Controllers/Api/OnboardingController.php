<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Onboarding\OnboardingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OnboardingController extends Controller
{
    private OnboardingService $onboardingService;

    public function __construct(OnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    /**
     * Handle onboarding chat message
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'confirmedData' => ['sometimes', 'array'],
            'conversationHistory' => ['sometimes', 'array'],
        ]);

        $user = $request->user();
        $confirmedData = $request->input('confirmedData', []);
        $conversationHistory = $request->input('conversationHistory', []);

        $response = $this->onboardingService->processChatMessage(
            $conversationHistory,
            $request->input('message'),
            $confirmedData
        );

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    /**
     * Complete onboarding and create organization
     */
    public function complete(Request $request): JsonResponse
    {
        $request->validate([
            'confirmedData' => ['required', 'array'],
            'confirmedData.name' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $confirmedData = $request->input('confirmedData');

        $organization = $this->onboardingService->completeOnboarding($user, $confirmedData);

        return response()->json([
            'success' => true,
            'data' => [
                'organizationId' => $organization->id,
                'organization' => $organization,
            ],
            'message' => 'Onboarding completed successfully',
        ]);
    }
}

