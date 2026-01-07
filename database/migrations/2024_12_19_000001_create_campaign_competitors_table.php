<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_competitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('competitor_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['campaign_id', 'competitor_id']);
            $table->index('campaign_id');
            $table->index('competitor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_competitors');
    }
};


