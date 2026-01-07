<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['social_media', 'email', 'content', 'paid_ads', 'press_release', 'general'])->default('general');
            $table->json('template_data')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('category');
            $table->index('is_public');
            $table->index('is_featured');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_templates');
    }
};


