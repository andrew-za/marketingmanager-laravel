<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('flagged_by')->constrained('users')->onDelete('cascade');
            $table->enum('reason', ['inappropriate', 'spam', 'copyright', 'misinformation', 'other'])->default('other');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'dismissed'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('scheduled_post_id');
            $table->index('organization_id');
            $table->index('flagged_by');
            $table->index('status');
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_flags');
    }
};


