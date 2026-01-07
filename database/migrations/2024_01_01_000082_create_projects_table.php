<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('organizations')->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['planning', 'in_progress', 'review', 'completed', 'cancelled'])->default('planning');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('client_id');
            $table->index('status');
            $table->index('project_manager_id');
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};


