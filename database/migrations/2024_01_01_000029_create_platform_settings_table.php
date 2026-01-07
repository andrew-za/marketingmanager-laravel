<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('platform', ['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'pinterest', 'youtube', 'general'])->default('general');
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_global')->default(false);
            $table->timestamps();

            $table->unique(['organization_id', 'platform', 'key']);
            $table->index('organization_id');
            $table->index('platform');
            $table->index('key');
            $table->index('is_global');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};


