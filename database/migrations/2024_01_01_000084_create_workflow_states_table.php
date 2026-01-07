<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('project_id');
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_states');
    }
};


