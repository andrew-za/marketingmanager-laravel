<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['reach', 'engagement', 'conversions', 'revenue', 'awareness', 'custom'])->default('engagement');
            $table->decimal('target_value', 15, 2)->nullable();
            $table->string('target_unit')->nullable();
            $table->decimal('current_value', 15, 2)->default(0);
            $table->date('target_date')->nullable();
            $table->boolean('is_achieved')->default(false);
            $table->timestamps();

            $table->index('campaign_id');
            $table->index('type');
            $table->index(['campaign_id', 'is_achieved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_goals');
    }
};


