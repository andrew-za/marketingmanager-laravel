<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_library', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_url')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->json('tags')->nullable();
            $table->enum('source', ['upload', 'generated', 'imported', 'stock'])->default('upload');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('source');
            $table->index('uploaded_by');
            $table->index(['organization_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_library');
    }
};


