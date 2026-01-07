<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publishing_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('published_post_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('platform');
            $table->string('error_code')->nullable();
            $table->string('error_type')->nullable();
            $table->text('error_message');
            $table->json('error_details')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('scheduled_post_id');
            $table->index('published_post_id');
            $table->index('organization_id');
            $table->index('platform');
            $table->index('is_resolved');
            $table->index(['organization_id', 'is_resolved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publishing_errors');
    }
};


