<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_page_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('html_content')->nullable();
            $table->json('page_data')->nullable();
            $table->integer('traffic_percentage')->default(50);
            $table->integer('conversions')->default(0);
            $table->integer('visits')->default(0);
            $table->boolean('is_winner')->default(false);
            $table->timestamps();

            $table->index('landing_page_id');
            $table->index('is_winner');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_page_variants');
    }
};


