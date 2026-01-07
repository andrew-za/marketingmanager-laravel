<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_id')->constrained()->onDelete('cascade');
            $table->foreignId('conversation_id')->nullable()->constrained('chatbot_conversations')->onDelete('set null');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->json('custom_fields')->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'converted', 'lost'])->default('new');
            $table->timestamps();

            $table->index('chatbot_id');
            $table->index('conversation_id');
            $table->index('email');
            $table->index('status');
            $table->index(['chatbot_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_leads');
    }
};


