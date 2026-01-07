<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('restrict');
            $table->enum('status', ['active', 'cancelled', 'expired', 'trial', 'past_due'])->default('trial');
            $table->string('stripe_subscription_id')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('subscription_plan_id');
            $table->index('status');
            $table->index('stripe_subscription_id');
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};


