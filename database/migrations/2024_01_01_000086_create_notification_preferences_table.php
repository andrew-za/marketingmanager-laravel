<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('notification_type');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('push_enabled')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'notification_type']);
            $table->index('user_id');
            $table->index('notification_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};


