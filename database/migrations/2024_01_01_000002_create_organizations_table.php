<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('timezone')->default('UTC');
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans');
            $table->enum('status', ['active', 'inactive', 'trial', 'suspended'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->index('slug');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};


