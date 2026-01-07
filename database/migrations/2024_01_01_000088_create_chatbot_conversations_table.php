<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained()->onDelete('cascade');
            $table->string('session_id');
            $table->string('visitor_name')->nullable();
            $table->string('visitor_email')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index('chatbot_id');
            $table->index('session_id');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_conversations');
    }
};


