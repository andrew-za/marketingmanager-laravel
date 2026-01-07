<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('subject');
            $table->text('html_content')->nullable();
            $table->text('text_content')->nullable();
            $table->json('variables')->nullable();
            $table->enum('category', ['newsletter', 'promotional', 'transactional', 'notification', 'custom'])->default('custom');
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('category');
            $table->index('is_public');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};


