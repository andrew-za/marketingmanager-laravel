<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('task_description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->integer('estimated_hours')->nullable();
            $table->json('checklist')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index('organization_id');
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};


