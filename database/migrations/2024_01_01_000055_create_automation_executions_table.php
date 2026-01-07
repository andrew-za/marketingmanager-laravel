<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_rule_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->json('input_data')->nullable();
            $table->json('output_data')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('automation_rule_id');
            $table->index('status');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_executions');
    }
};


