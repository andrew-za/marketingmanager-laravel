<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_action_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('action_type');
            $table->json('config_schema')->nullable();
            $table->json('default_config')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index('action_type');
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_action_templates');
    }
};

