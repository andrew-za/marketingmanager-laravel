<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'sent', 'delivered', 'opened', 'clicked', 'bounced', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->integer('open_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['email_campaign_id', 'contact_id']);
            $table->index('email_campaign_id');
            $table->index('contact_id');
            $table->index('status');
            $table->index(['email_campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
    }
};


