<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('published_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('social_connection_id')->constrained()->onDelete('cascade');
            $table->string('platform');
            $table->string('external_post_id')->nullable();
            $table->string('external_post_url')->nullable();
            $table->enum('status', ['published', 'failed', 'pending', 'scheduled'])->default('pending');
            $table->timestamp('published_at')->nullable();
            $table->json('platform_response')->nullable();
            $table->json('metrics')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('scheduled_post_id');
            $table->index('organization_id');
            $table->index('social_connection_id');
            $table->index('platform');
            $table->index('status');
            $table->index('published_at');
            $table->index(['scheduled_post_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('published_posts');
    }
};


