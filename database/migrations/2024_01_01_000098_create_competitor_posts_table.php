<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitor_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competitor_id')->constrained()->onDelete('cascade');
            $table->string('platform');
            $table->string('post_id')->nullable();
            $table->text('content')->nullable();
            $table->string('url')->nullable();
            $table->integer('likes')->default(0);
            $table->integer('comments')->default(0);
            $table->integer('shares')->default(0);
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index('competitor_id');
            $table->index('platform');
            $table->index('posted_at');
            $table->index(['competitor_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_posts');
    }
};


