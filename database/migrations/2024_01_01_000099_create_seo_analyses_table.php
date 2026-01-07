<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('url');
            $table->json('meta_tags')->nullable();
            $table->json('keywords')->nullable();
            $table->integer('word_count')->nullable();
            $table->integer('reading_time')->nullable();
            $table->json('recommendations')->nullable();
            $table->decimal('seo_score', 5, 2)->nullable();
            $table->date('analyzed_at');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('url');
            $table->index('analyzed_at');
            $table->index(['organization_id', 'analyzed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_analyses');
    }
};


