<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_name')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->timestamp('last_activity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('session_id');
            $table->index(['user_id', 'is_active']);
            $table->index('last_activity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};


