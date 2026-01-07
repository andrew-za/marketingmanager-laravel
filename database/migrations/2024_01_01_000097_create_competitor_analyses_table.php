<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitor_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competitor_id')->constrained()->onDelete('cascade');
            $table->date('analysis_date');
            $table->json('metrics')->nullable();
            $table->json('insights')->nullable();
            $table->timestamps();

            $table->index('competitor_id');
            $table->index('analysis_date');
            $table->unique(['competitor_id', 'analysis_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_analyses');
    }
};


