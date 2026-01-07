<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->text('question');
            $table->enum('type', ['text', 'textarea', 'radio', 'checkbox', 'select', 'rating', 'date'])->default('text');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('survey_id');
            $table->index('order');
            $table->index(['survey_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};


