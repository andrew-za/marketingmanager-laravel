<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('prediction_model_id')->nullable()->constrained()->onDelete('set null');
            $table->morphs('predictable');
            $table->string('prediction_type');
            $table->decimal('predicted_value', 15, 2)->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->json('prediction_data')->nullable();
            $table->date('prediction_date');
            $table->date('target_date')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('prediction_model_id');
            $table->index(['predictable_type', 'predictable_id']);
            $table->index('prediction_type');
            $table->index('prediction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};


