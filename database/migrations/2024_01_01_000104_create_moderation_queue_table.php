<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moderation_queue', function (Blueprint $table) {
            $table->id();
            $table->morphs('moderatable');
            $table->enum('type', ['content', 'user', 'review', 'comment', 'other'])->default('content');
            $table->enum('status', ['pending', 'approved', 'rejected', 'flagged'])->default('pending');
            $table->foreignId('flagged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reason')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['moderatable_type', 'moderatable_id']);
            $table->index('type');
            $table->index('status');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_queue');
    }
};


