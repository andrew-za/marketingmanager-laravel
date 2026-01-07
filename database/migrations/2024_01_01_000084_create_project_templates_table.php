<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('default_status', ['planning', 'in_progress', 'review', 'completed', 'cancelled'])->default('planning');
            $table->json('default_member_roles')->nullable();
            $table->json('task_templates')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index('organization_id');
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_templates');
    }
};

