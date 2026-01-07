<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_message_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reaction');
            $table->timestamps();

            $table->unique(['chat_message_id', 'user_id', 'reaction']);
            $table->index('chat_message_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_reactions');
    }
};


