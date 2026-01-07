<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('level', ['debug', 'info', 'warning', 'error', 'critical'])->default('info');
            $table->string('message');
            $table->json('context')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('level');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['level', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};


