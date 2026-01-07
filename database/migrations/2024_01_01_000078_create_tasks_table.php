<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'review', 'completed', 'cancelled'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('project_id');
            $table->index('assignee_id');
            $table->index('status');
            $table->index('priority');
            $table->index('due_date');
            $table->index(['organization_id', 'assignee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};


