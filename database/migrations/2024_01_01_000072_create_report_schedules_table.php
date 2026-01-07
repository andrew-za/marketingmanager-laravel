<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'custom'])->default('weekly');
            $table->string('schedule_config')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('recipients')->nullable();
            $table->timestamps();

            $table->index('report_id');
            $table->index('frequency');
            $table->index('next_run_at');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_schedules');
    }
};


