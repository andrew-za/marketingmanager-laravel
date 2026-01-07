<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Invoice Reminder Notification
 * Sends reminder notifications for pending and overdue invoices
 */
class InvoiceReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $status = $this->invoice->status === 'overdue' ? 'overdue' : 'pending';
        $subject = $status === 'overdue' 
            ? "Overdue Invoice Reminder: {$this->invoice->invoice_number}"
            : "Invoice Reminder: {$this->invoice->invoice_number}";

        return (new MailMessage)
            ->subject($subject)
            ->line("This is a reminder that invoice {$this->invoice->invoice_number} is {$status}.")
            ->line("Amount: {$this->invoice->currency} {$this->invoice->total}")
            ->line("Due Date: {$this->invoice->due_date->format('M d, Y')}")
            ->action('View Invoice', route('agency.billing', ['agency' => $this->invoice->organization->agencies()->first()?->id]))
            ->line('Thank you for your attention to this matter.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'invoice_reminder',
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->total,
            'currency' => $this->invoice->currency,
            'status' => $this->invoice->status,
            'due_date' => $this->invoice->due_date->toDateString(),
            'organization_id' => $this->invoice->organization_id,
        ];
    }
}

