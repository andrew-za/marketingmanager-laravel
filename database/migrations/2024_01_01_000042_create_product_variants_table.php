<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('name')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('compare_at_price', 15, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->string('barcode')->nullable();
            $table->json('attributes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('product_id');
            $table->index('sku');
            $table->index('is_active');
            $table->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};


