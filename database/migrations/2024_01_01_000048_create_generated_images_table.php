<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ai_generation_id')->nullable()->constrained()->onDelete('set null');
            $table->string('provider')->default('dalle');
            $table->string('model')->nullable();
            $table->text('prompt');
            $table->string('image_url');
            $table->string('image_path')->nullable();
            $table->json('metadata')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->decimal('cost', 10, 4)->default(0);
            $table->timestamps();

            $table->index('organization_id');
            $table->index('user_id');
            $table->index('ai_generation_id');
            $table->index('provider');
            $table->index(['organization_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_images');
    }
};


