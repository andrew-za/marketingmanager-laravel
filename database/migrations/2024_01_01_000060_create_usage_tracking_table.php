<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('feature');
            $table->string('metric');
            $table->decimal('value', 15, 2)->default(0);
            $table->date('date');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('feature');
            $table->index('date');
            $table->unique(['organization_id', 'feature', 'metric', 'date']);
            $table->index(['organization_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_tracking');
    }
};


