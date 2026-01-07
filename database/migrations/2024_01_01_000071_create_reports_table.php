<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['campaign', 'analytics', 'financial', 'custom'])->default('custom');
            $table->json('config')->nullable();
            $table->json('data')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('format', ['pdf', 'excel', 'csv', 'json'])->default('pdf');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('type');
            $table->index('created_by');
            $table->index(['organization_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};


