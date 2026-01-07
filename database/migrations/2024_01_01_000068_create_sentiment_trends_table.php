<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sentiment_trends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->morphs('trendable');
            $table->date('trend_date');
            $table->integer('positive_count')->default(0);
            $table->integer('negative_count')->default(0);
            $table->integer('neutral_count')->default(0);
            $table->decimal('average_sentiment_score', 5, 2)->default(0);
            $table->timestamps();

            $table->index('organization_id');
            $table->index(['trendable_type', 'trendable_id']);
            $table->index('trend_date');
            $table->unique(['organization_id', 'trendable_type', 'trendable_id', 'trend_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sentiment_trends');
    }
};


