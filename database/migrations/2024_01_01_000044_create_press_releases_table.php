<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('press_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('content');
            $table->text('summary')->nullable();
            $table->enum('status', ['draft', 'pending_review', 'approved', 'distributed', 'published'])->default('draft');
            $table->timestamp('release_date')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('campaign_id');
            $table->index('status');
            $table->index('release_date');
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('press_releases');
    }
};


