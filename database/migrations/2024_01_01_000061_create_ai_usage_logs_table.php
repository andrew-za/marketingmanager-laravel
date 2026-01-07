<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('ai_generation_id')->nullable()->constrained()->onDelete('set null');
            $table->string('provider');
            $table->string('model')->nullable();
            $table->enum('type', ['content', 'image', 'other'])->default('content');
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 10, 4)->default(0);
            $table->date('usage_date');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('user_id');
            $table->index('provider');
            $table->index('usage_date');
            $table->index(['organization_id', 'usage_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};


