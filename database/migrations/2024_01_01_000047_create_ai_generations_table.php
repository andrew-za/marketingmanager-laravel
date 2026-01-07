<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['content', 'image', 'email', 'ad_copy', 'blog', 'press_release', 'seo', 'other'])->default('content');
            $table->string('provider')->default('openai');
            $table->string('model')->nullable();
            $table->text('prompt');
            $table->text('generated_content')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 10, 4)->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('status');
            $table->index(['organization_id', 'type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
};


