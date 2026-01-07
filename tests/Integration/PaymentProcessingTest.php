<?php

namespace Tests\Integration;

use App\Models\Invoice;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentProcessingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->organization = Organization::factory()->create();
        $this->user->organizations()->attach($this->organization->id, ['role_id' => 1]);
        
        $this->actingAs($this->user);
    }

    public function testUserCanViewInvoices(): void
    {
        Invoice::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->get("/main/{$this->organization->id}/billing/invoices");

        $response->assertStatus(200);
    }

    public function testUserCanDownloadInvoice(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->get("/main/{$this->organization->id}/billing/invoices/{$invoice->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function testUserCanMarkInvoiceAsPaid(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'pending',
        ]);

        $response = $this->post("/main/{$this->organization->id}/billing/invoices/{$invoice->id}/pay", [
            'payment_method' => 'stripe',
            'payment_id' => 'test_payment_id',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total,
        ]);
    }

    public function testPaymentWebhookHandlesStripeEvents(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/webhooks/stripe', [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'test_payment_intent',
                    'metadata' => [
                        'invoice_id' => $invoice->id,
                    ],
                    'amount' => $invoice->total * 100,
                ],
            ],
        ]);

        $response->assertStatus(200);
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
    }

    public function testPaymentWebhookHandlesFailedPayments(): void
    {
        $invoice = Invoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/webhooks/stripe', [
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'test_payment_intent',
                    'metadata' => [
                        'invoice_id' => $invoice->id,
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200);
        $invoice->refresh();
        $this->assertEquals('pending', $invoice->status);
    }
}

