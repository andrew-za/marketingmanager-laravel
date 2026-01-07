<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('sku')->unique();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['brand_id', 'status']);
            $table->index('sku');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

