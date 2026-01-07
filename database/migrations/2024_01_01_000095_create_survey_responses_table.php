<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained('survey_questions')->onDelete('cascade');
            $table->string('respondent_email')->nullable();
            $table->text('response');
            $table->timestamps();

            $table->index('survey_id');
            $table->index('question_id');
            $table->index('respondent_email');
            $table->index(['survey_id', 'respondent_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};


