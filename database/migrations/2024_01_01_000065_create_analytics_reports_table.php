<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['campaign', 'social_media', 'email', 'overall', 'custom'])->default('overall');
            $table->date('start_date');
            $table->date('end_date');
            $table->json('data')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('type');
            $table->index(['start_date', 'end_date']);
            $table->index(['organization_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_reports');
    }
};


