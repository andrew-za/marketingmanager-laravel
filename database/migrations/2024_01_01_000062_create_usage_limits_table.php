<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('cascade');
            $table->string('feature');
            $table->string('limit_type')->default('monthly');
            $table->decimal('limit_value', 15, 2)->nullable();
            $table->boolean('is_unlimited')->default(false);
            $table->timestamps();

            $table->unique(['subscription_plan_id', 'feature', 'limit_type']);
            $table->index('subscription_plan_id');
            $table->index('feature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_limits');
    }
};


