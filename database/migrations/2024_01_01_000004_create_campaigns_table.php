<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->decimal('budget', 15, 2)->default(0);
            $table->decimal('spent', 15, 2)->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['organization_id', 'status', 'start_date']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};

