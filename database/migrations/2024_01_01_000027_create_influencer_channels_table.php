<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('influencer_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('influencer_name');
            $table->string('platform');
            $table->string('handle')->nullable();
            $table->string('url')->nullable();
            $table->integer('follower_count')->nullable();
            $table->decimal('engagement_rate', 5, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->json('metrics')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('platform');
            $table->index('status');
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('influencer_channels');
    }
};


