<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'rejected', 'changes_requested'])->default('pending');
            $table->text('comments')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('scheduled_post_id');
            $table->index('organization_id');
            $table->index('requested_by');
            $table->index('approved_by');
            $table->index('status');
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_approvals');
    }
};


