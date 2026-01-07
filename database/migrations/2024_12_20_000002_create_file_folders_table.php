<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('file_folders')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('parent_id');
            $table->index(['organization_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_folders');
    }
};

