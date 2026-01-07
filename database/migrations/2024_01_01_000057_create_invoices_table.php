<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
            $table->string('invoice_number')->unique();
            $table->enum('status', ['draft', 'pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('subscription_id');
            $table->index('invoice_number');
            $table->index('status');
            $table->index('due_date');
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};


