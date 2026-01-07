<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sentiment_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->morphs('analysable');
            $table->enum('sentiment', ['positive', 'negative', 'neutral'])->default('neutral');
            $table->decimal('sentiment_score', 5, 2)->default(0);
            $table->text('content');
            $table->json('keywords')->nullable();
            $table->json('entities')->nullable();
            $table->date('analysis_date');
            $table->timestamps();

            $table->index('organization_id');
            $table->index(['analysable_type', 'analysable_id']);
            $table->index('sentiment');
            $table->index('analysis_date');
            $table->index(['organization_id', 'sentiment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sentiment_analyses');
    }
};


