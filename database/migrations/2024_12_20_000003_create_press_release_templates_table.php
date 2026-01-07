<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('press_release_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('template_content');
            $table->json('variables')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('is_public');
            $table->index(['organization_id', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('press_release_templates');
    }
};

