<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('author_name');
            $table->string('author_title')->nullable();
            $table->string('author_company')->nullable();
            $table->string('author_avatar')->nullable();
            $table->text('content');
            $table->integer('rating')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('organization_id');
            $table->index('status');
            $table->index('is_featured');
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};


