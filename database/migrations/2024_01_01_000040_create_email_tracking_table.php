<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->string('tracking_token')->unique();
            $table->enum('event_type', ['sent', 'delivered', 'opened', 'clicked', 'bounced', 'unsubscribed'])->default('sent');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('link_url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index('email_campaign_id');
            $table->index('contact_id');
            $table->index('tracking_token');
            $table->index('event_type');
            $table->index('occurred_at');
            $table->index(['email_campaign_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_tracking');
    }
};


