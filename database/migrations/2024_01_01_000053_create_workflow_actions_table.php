<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
            $table->string('action_type');
            $table->integer('order')->default(0);
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('workflow_id');
            $table->index('action_type');
            $table->index(['workflow_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_actions');
    }
};


