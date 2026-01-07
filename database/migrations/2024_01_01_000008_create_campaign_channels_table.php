<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->decimal('budget', 15, 2)->default(0);
            $table->decimal('spent', 15, 2)->default(0);
            $table->enum('status', ['active', 'paused', 'completed'])->default('active');
            $table->timestamps();

            $table->unique(['campaign_id', 'channel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_channels');
    }
};


