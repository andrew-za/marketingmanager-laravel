<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();

            $table->unique(['organization_id', 'key']);
            $table->index('organization_id');
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};


