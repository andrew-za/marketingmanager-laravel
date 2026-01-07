<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->morphs('metricable');
            $table->string('metric_name');
            $table->decimal('value', 15, 2);
            $table->date('metric_date');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index(['metricable_type', 'metricable_id']);
            $table->index('metric_name');
            $table->index('metric_date');
            $table->index(['organization_id', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_metrics');
    }
};


