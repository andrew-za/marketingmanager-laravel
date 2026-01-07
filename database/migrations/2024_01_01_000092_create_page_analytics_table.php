<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('landing_page_variants')->onDelete('set null');
            $table->date('analytics_date');
            $table->integer('visits')->default(0);
            $table->integer('unique_visitors')->default(0);
            $table->integer('conversions')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->integer('bounce_rate')->default(0);
            $table->integer('avg_session_duration')->default(0);
            $table->timestamps();

            $table->index('landing_page_id');
            $table->index('variant_id');
            $table->index('analytics_date');
            $table->unique(['landing_page_id', 'variant_id', 'analytics_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_analytics');
    }
};


