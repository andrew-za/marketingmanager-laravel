<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('email_template_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'paused', 'cancelled'])->default('draft');
            $table->string('subject');
            $table->text('from_name')->nullable();
            $table->string('from_email');
            $table->string('reply_to_email')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->integer('bounced_count')->default(0);
            $table->integer('unsubscribed_count')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('campaign_id');
            $table->index('email_template_id');
            $table->index('status');
            $table->index('scheduled_at');
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};


