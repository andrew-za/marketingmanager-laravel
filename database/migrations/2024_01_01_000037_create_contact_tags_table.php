<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->string('tag');
            $table->timestamps();

            $table->unique(['contact_id', 'tag']);
            $table->index('contact_id');
            $table->index('tag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_tags');
    }
};


