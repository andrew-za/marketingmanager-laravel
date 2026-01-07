<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['logo', 'image', 'font', 'color', 'other'])->default('other');
            $table->string('url');
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['brand_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_assets');
    }
};

