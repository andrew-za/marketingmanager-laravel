<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('press_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('media_outlet')->nullable();
            $table->enum('type', ['journalist', 'blogger', 'influencer', 'media_outlet', 'other'])->default('journalist');
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('organization_id');
            $table->index('email');
            $table->index('type');
            $table->index('is_active');
            $table->index(['organization_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('press_contacts');
    }
};


