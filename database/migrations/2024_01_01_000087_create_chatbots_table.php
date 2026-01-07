<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('welcome_message')->nullable();
            $table->json('training_data')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('embed_code')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('is_active');
            $table->index(['organization_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbots');
    }
};


