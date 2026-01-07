<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paid_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('platform', ['facebook', 'instagram', 'google', 'linkedin', 'twitter', 'tiktok', 'pinterest', 'other'])->default('facebook');
            $table->enum('status', ['draft', 'pending', 'active', 'paused', 'completed', 'cancelled'])->default('draft');
            $table->decimal('budget', 15, 2);
            $table->decimal('spent', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->enum('budget_type', ['daily', 'lifetime'])->default('daily');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->json('targeting')->nullable();
            $table->json('ad_creative')->nullable();
            $table->string('external_campaign_id')->nullable();
            $table->json('metrics')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('campaign_id');
            $table->index('platform');
            $table->index('status');
            $table->index(['organization_id', 'status']);
            $table->index('start_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paid_campaigns');
    }
};


