<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('email_campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['sent', 'delivered', 'opened', 'clicked', 'bounced', 'unsubscribed', 'subscribed', 'updated'])->default('sent');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index('contact_id');
            $table->index('email_campaign_id');
            $table->index('type');
            $table->index('occurred_at');
            $table->index(['contact_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_activities');
    }
};


