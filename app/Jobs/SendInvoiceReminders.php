<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Notifications\InvoiceReminder;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Send Invoice Reminders Job
 * Processes automated invoice reminders for overdue and pending invoices
 */
class SendInvoiceReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public array $organizationIds
    ) {}

    public function handle(InvoiceService $invoiceService): void
    {
        try {
            // Update overdue status first
            $invoiceService->updateOverdueStatus();

            // Get overdue and pending invoices
            $overdueInvoices = $invoiceService->getOverdueInvoices($this->organizationIds);
            
            $pendingInvoices = Invoice::whereIn('organization_id', $this->organizationIds)
                ->where('status', 'pending')
                ->where('due_date', '<=', now()->addDays(3))
                ->with('organization')
                ->get();

            $invoicesToRemind = $overdueInvoices->merge($pendingInvoices);

            foreach ($invoicesToRemind as $invoice) {
                if ($invoice->organization && $invoice->organization->owner) {
                    $invoice->organization->owner->notify(new InvoiceReminder($invoice));
                    Log::info("Invoice reminder sent for invoice {$invoice->id} to organization {$invoice->organization_id}");
                }
            }

            Log::info("Invoice reminders processed: {$invoicesToRemind->count()} invoices");
        } catch (\Exception $e) {
            Log::error("Failed to send invoice reminders: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendInvoiceReminders job failed after {$this->tries} attempts: " . $exception->getMessage());
    }
}

