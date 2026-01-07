<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->dateTime('scheduled_at');
            $table->dateTime('published_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'published', 'failed'])->default('pending');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['organization_id', 'scheduled_at']);
            $table->index(['status', 'scheduled_at']);
            $table->index('channel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_posts');
    }
};


