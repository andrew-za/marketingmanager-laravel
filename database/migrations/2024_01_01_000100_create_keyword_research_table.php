<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keyword_research', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('keyword');
            $table->integer('search_volume')->nullable();
            $table->integer('difficulty')->nullable();
            $table->decimal('cpc', 10, 2)->nullable();
            $table->json('related_keywords')->nullable();
            $table->json('trends')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('keyword');
            $table->index(['organization_id', 'keyword']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_research');
    }
};


