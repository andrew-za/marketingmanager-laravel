<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SubscriptionPlan;
use App\Services\Organization\BillingService;
use App\Http\Requests\Organization\CreateSubscriptionRequest;
use App\Http\Requests\Organization\UpgradeSubscriptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Organization Billing Controller
 * Handles subscription and billing management
 * Requires organization admin access
 */
class BillingController extends Controller
{
    public function __construct(
        private BillingService $billingService
    ) {}

    /**
     * Display billing dashboard
     */
    public function index(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $subscription = $this->billingService->getCurrentSubscription($organization);
        $plans = SubscriptionPlan::where('is_active', true)->get();
        $invoices = $this->billingService->getInvoices($organization, 10);
        $usageStats = $this->billingService->getUsageStats($organization, $request->get('period', 'month'));

        return view('organization.billing.index', [
            'organization' => $organization,
            'subscription' => $subscription,
            'plans' => $plans,
            'invoices' => $invoices,
            'usageStats' => $usageStats,
        ]);
    }

    /**
     * Create a new subscription
     */
    public function createSubscription(CreateSubscriptionRequest $request, Organization $organization): JsonResponse
    {
        $subscription = $this->billingService->createSubscription(
            $organization,
            $request->input('plan_id'),
            $request->boolean('is_trial', false)
        );

        return response()->json([
            'success' => true,
            'message' => 'Subscription created successfully.',
            'data' => $subscription->load('plan'),
        ], 201);
    }

    /**
     * Upgrade subscription
     */
    public function upgradeSubscription(UpgradeSubscriptionRequest $request, Organization $organization): JsonResponse
    {
        $subscription = $this->billingService->getCurrentSubscription($organization);

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found.',
            ], 404);
        }

        $subscription = $this->billingService->upgradeSubscription(
            $subscription,
            $request->input('plan_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Subscription upgraded successfully.',
            'data' => $subscription->load('plan'),
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('update', $organization);

        $subscription = $this->billingService->getCurrentSubscription($organization);

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found.',
            ], 404);
        }

        $subscription = $this->billingService->cancelSubscription(
            $subscription,
            $request->boolean('cancel_at_period_end', true)
        );

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully.',
            'data' => $subscription->load('plan'),
        ]);
    }

    /**
     * Get invoices
     */
    public function getInvoices(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        $invoices = $this->billingService->getInvoices($organization, $request->get('limit', 10));

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats(Request $request, Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        $stats = $this->billingService->getUsageStats(
            $organization,
            $request->get('period', 'month')
        );

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

