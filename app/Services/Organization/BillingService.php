<?php

namespace App\Services\Organization;

use App\Models\Organization;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\UsageTracking;
use App\Models\AiUsageLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingService
{
    public function getCurrentSubscription(Organization $organization): ?Subscription
    {
        return $organization->subscription;
    }

    public function createSubscription(Organization $organization, int $planId, bool $isTrial = false): Subscription
    {
        return DB::transaction(function () use ($organization, $planId, $isTrial) {
            $plan = SubscriptionPlan::findOrFail($planId);
            
            $subscription = Subscription::create([
                'organization_id' => $organization->id,
                'plan_id' => $planId,
                'status' => $isTrial ? 'trial' : 'active',
                'current_period_start' => now(),
                'current_period_end' => $isTrial 
                    ? now()->addDays(14) 
                    : ($plan->billing_cycle === 'yearly' ? now()->addYear() : now()->addMonth()),
                'trial_ends_at' => $isTrial ? now()->addDays(14) : null,
            ]);

            $organization->update(['subscription_plan_id' => $planId]);

            return $subscription;
        });
    }

    public function upgradeSubscription(Subscription $subscription, int $newPlanId): Subscription
    {
        return DB::transaction(function () use ($subscription, $newPlanId) {
            $newPlan = SubscriptionPlan::findOrFail($newPlanId);
            
            $subscription->update([
                'plan_id' => $newPlanId,
                'status' => 'active',
            ]);

            $subscription->organization->update(['subscription_plan_id' => $newPlanId]);

            return $subscription->fresh();
        });
    }

    public function cancelSubscription(Subscription $subscription, bool $cancelAtPeriodEnd = true): Subscription
    {
        return DB::transaction(function () use ($subscription, $cancelAtPeriodEnd) {
            $subscription->update([
                'cancel_at_period_end' => $cancelAtPeriodEnd,
                'status' => $cancelAtPeriodEnd ? 'active' : 'cancelled',
            ]);

            return $subscription->fresh();
        });
    }

    public function generateInvoice(Organization $organization, Subscription $subscription, array $items): Invoice
    {
        return DB::transaction(function () use ($organization, $subscription, $items) {
            $subtotal = collect($items)->sum('total');
            $tax = $subtotal * 0.1; // 10% tax
            $total = $subtotal + $tax;

            $invoice = Invoice::create([
                'organization_id' => $organization->id,
                'subscription_id' => $subscription->id,
                'invoice_number' => 'INV-' . strtoupper(Str::random(10)),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'currency' => 'USD',
                'due_date' => now()->addDays(30),
            ]);

            foreach ($items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? $item['total'],
                    'total' => $item['total'],
                ]);
            }

            return $invoice->fresh();
        });
    }

    public function getUsageStats(Organization $organization, string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        $aiUsage = AiUsageLog::where('organization_id', $organization->id)
            ->where('usage_date', '>=', $startDate)
            ->selectRaw('SUM(tokens_used) as total_tokens, SUM(cost) as total_cost, COUNT(*) as total_requests')
            ->first();

        $usageTracking = UsageTracking::where('organization_id', $organization->id)
            ->where('date', '>=', $startDate)
            ->selectRaw('feature, SUM(value) as total_value')
            ->groupBy('feature')
            ->get()
            ->keyBy('feature');

        return [
            'ai_usage' => [
                'total_tokens' => $aiUsage->total_tokens ?? 0,
                'total_cost' => $aiUsage->total_cost ?? 0,
                'total_requests' => $aiUsage->total_requests ?? 0,
            ],
            'feature_usage' => $usageTracking->toArray(),
            'period' => $period,
            'start_date' => $startDate,
        ];
    }

    public function getInvoices(Organization $organization, int $limit = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Invoice::where('organization_id', $organization->id)
            ->with(['subscription.plan', 'payments'])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    public function markInvoiceAsPaid(Invoice $invoice, string $transactionId, string $paymentMethod = 'stripe'): Payment
    {
        return DB::transaction(function () use ($invoice, $transactionId, $paymentMethod) {
            $payment = Payment::create([
                'organization_id' => $invoice->organization_id,
                'invoice_id' => $invoice->id,
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
                'amount' => $invoice->total,
                'currency' => $invoice->currency,
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            return $payment;
        });
    }
}

