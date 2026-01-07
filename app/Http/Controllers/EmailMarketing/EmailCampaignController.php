<?php

namespace App\Http\Controllers\EmailMarketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailMarketing\CreateEmailCampaignRequest;
use App\Http\Requests\EmailMarketing\UpdateEmailCampaignRequest;
use App\Http\Resources\EmailMarketing\EmailCampaignResource;
use App\Models\EmailCampaign;
use App\Services\EmailMarketing\EmailCampaignService;
use App\Jobs\SendEmailCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmailCampaignController extends Controller
{
    public function __construct(
        private EmailCampaignService $emailCampaignService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $organizationId = auth()->user()->primaryOrganization()->id;
        $query = EmailCampaign::where('organization_id', $organizationId)
            ->with(['emailTemplate', 'contactLists']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $campaigns = $query->orderBy('created_at', 'desc')->paginate();

        return EmailCampaignResource::collection($campaigns);
    }

    public function store(CreateEmailCampaignRequest $request): JsonResponse
    {
        $campaign = $this->emailCampaignService->createCampaign(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'data' => new EmailCampaignResource($campaign),
            'message' => 'Email campaign created successfully.',
        ], 201);
    }

    public function show(Request $request, EmailCampaign $emailCampaign): JsonResponse
    {
        $this->authorize('view', $emailCampaign);

        $emailCampaign->load(['emailTemplate', 'contactLists', 'recipients.contact']);

        return response()->json([
            'success' => true,
            'data' => new EmailCampaignResource($emailCampaign),
        ]);
    }

    public function update(UpdateEmailCampaignRequest $request, EmailCampaign $emailCampaign): JsonResponse
    {
        $this->authorize('update', $emailCampaign);

        $campaign = $this->emailCampaignService->updateCampaign(
            $emailCampaign,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new EmailCampaignResource($campaign),
            'message' => 'Email campaign updated successfully.',
        ]);
    }

    public function destroy(EmailCampaign $emailCampaign): JsonResponse
    {
        $this->authorize('delete', $emailCampaign);

        $this->emailCampaignService->deleteCampaign($emailCampaign);

        return response()->json([
            'success' => true,
            'message' => 'Email campaign deleted successfully.',
        ]);
    }

    public function send(Request $request, EmailCampaign $emailCampaign): JsonResponse
    {
        $this->authorize('send', $emailCampaign);

        if (!$emailCampaign->canSend()) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign cannot be sent. Ensure it has recipients.',
            ], 400);
        }

        SendEmailCampaign::dispatch($emailCampaign);

        return response()->json([
            'success' => true,
            'message' => 'Email campaign queued for sending.',
        ]);
    }

    public function schedule(Request $request, EmailCampaign $emailCampaign): JsonResponse
    {
        $this->authorize('update', $emailCampaign);

        $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        try {
            $campaign = $this->emailCampaignService->scheduleCampaign(
                $emailCampaign,
                now()->parse($request->scheduled_at)
            );

            return response()->json([
                'success' => true,
                'data' => new EmailCampaignResource($campaign),
                'message' => 'Email campaign scheduled successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function pause(EmailCampaign $emailCampaign): JsonResponse
    {
        $this->authorize('update', $emailCampaign);

        try {
            $emailCampaign->pause();
            return response()->json([
                'success' => true,
                'data' => new EmailCampaignResource($emailCampaign),
                'message' => 'Email campaign paused successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function resume(EmailCampaign $emailCampaign): JsonResponse
    {
        $this->authorize('update', $emailCampaign);

        try {
            $emailCampaign->resume();
            return response()->json([
                'success' => true,
                'data' => new EmailCampaignResource($emailCampaign),
                'message' => 'Email campaign resumed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function cancel(EmailCampaign $emailCampaign): JsonResponse
    {
        $this->authorize('update', $emailCampaign);

        try {
            $emailCampaign->cancel();
            return response()->json([
                'success' => true,
                'data' => new EmailCampaignResource($emailCampaign),
                'message' => 'Email campaign cancelled successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getMetrics(EmailCampaign $emailCampaign): JsonResponse
    {
        $this->authorize('view', $emailCampaign);

        $emailCampaign->updateMetrics();

        return response()->json([
            'success' => true,
            'data' => [
                'total_recipients' => $emailCampaign->total_recipients,
                'sent_count' => $emailCampaign->sent_count,
                'delivered_count' => $emailCampaign->delivered_count,
                'opened_count' => $emailCampaign->opened_count,
                'clicked_count' => $emailCampaign->clicked_count,
                'bounced_count' => $emailCampaign->bounced_count,
                'unsubscribed_count' => $emailCampaign->unsubscribed_count,
                'open_rate' => $emailCampaign->sent_count > 0 
                    ? round(($emailCampaign->opened_count / $emailCampaign->sent_count) * 100, 2) 
                    : 0,
                'click_rate' => $emailCampaign->sent_count > 0 
                    ? round(($emailCampaign->clicked_count / $emailCampaign->sent_count) * 100, 2) 
                    : 0,
                'bounce_rate' => $emailCampaign->sent_count > 0 
                    ? round(($emailCampaign->bounced_count / $emailCampaign->sent_count) * 100, 2) 
                    : 0,
            ],
        ]);
    }
}

