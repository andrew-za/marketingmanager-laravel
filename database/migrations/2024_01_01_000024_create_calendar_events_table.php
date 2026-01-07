<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('scheduled_post_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['post', 'campaign', 'meeting', 'deadline', 'reminder', 'custom'])->default('custom');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->string('timezone')->default('UTC');
            $table->boolean('is_all_day')->default(false);
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('campaign_id');
            $table->index('scheduled_post_id');
            $table->index('type');
            $table->index('start_time');
            $table->index(['organization_id', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};


