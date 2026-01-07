<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('organization_id');
            $table->index('slug');
            $table->index('parent_id');
            $table->index(['organization_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};


