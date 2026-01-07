<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Invoice Service
 * Handles invoice management and summary calculations
 */
class InvoiceService
{
    /**
     * Get invoices for organizations
     */
    public function getInvoicesForOrganizations(array $organizationIds, array $filters = []): LengthAwarePaginator
    {
        $query = Invoice::whereIn('organization_id', $organizationIds)
            ->with('organization')
            ->orderBy('created_at', 'desc');

        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Calculate invoice summary statistics
     */
    public function calculateSummary(array $organizationIds): array
    {
        $invoices = Invoice::whereIn('organization_id', $organizationIds)->get();

        return [
            'total_billed' => $invoices->where('status', 'paid')->sum('total'),
            'pending' => $invoices->where('status', 'pending')->sum('total'),
            'overdue' => $invoices->where('status', 'overdue')->sum('total'),
            'total_count' => $invoices->count(),
            'paid_count' => $invoices->where('status', 'paid')->count(),
            'pending_count' => $invoices->where('status', 'pending')->count(),
            'overdue_count' => $invoices->where('status', 'overdue')->count(),
        ];
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            return $invoice->fresh(['organization']);
        });
    }

    /**
     * Get overdue invoices
     */
    public function getOverdueInvoices(array $organizationIds): Collection
    {
        return Invoice::whereIn('organization_id', $organizationIds)
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->with('organization')
            ->get();
    }

    /**
     * Update invoice status to overdue
     */
    public function updateOverdueStatus(): int
    {
        return Invoice::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);
    }
}

