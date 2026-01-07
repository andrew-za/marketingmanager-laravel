<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
            $table->string('trigger_type');
            $table->json('conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('workflow_id');
            $table->index('trigger_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_triggers');
    }
};


