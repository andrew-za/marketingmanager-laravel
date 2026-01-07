<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('responded_by')->constrained('users')->onDelete('cascade');
            $table->text('response');
            $table->enum('response_type', ['public', 'private'])->default('public');
            $table->timestamps();

            $table->index('review_id');
            $table->index('organization_id');
            $table->index('responded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_responses');
    }
};


