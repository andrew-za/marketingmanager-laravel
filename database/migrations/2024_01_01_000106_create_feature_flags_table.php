<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(false);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};


