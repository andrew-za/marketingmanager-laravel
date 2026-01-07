<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('model_type');
            $table->json('parameters')->nullable();
            $table->decimal('accuracy', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('model_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_models');
    }
};


