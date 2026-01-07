<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('summary')->nullable();
            $table->text('audience')->nullable();
            $table->text('guidelines')->nullable();
            $table->string('tone_of_voice')->nullable();
            $table->json('keywords')->nullable();
            $table->json('avoid_keywords')->nullable();
            $table->string('logo')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('business_model')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};


