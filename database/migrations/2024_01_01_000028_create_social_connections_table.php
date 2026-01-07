<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('channel_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('platform', ['facebook', 'instagram', 'twitter', 'linkedin', 'tiktok', 'pinterest', 'youtube'])->default('facebook');
            $table->string('account_name');
            $table->string('account_id')->nullable();
            $table->string('account_type')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('token_metadata')->nullable();
            $table->enum('status', ['connected', 'disconnected', 'expired', 'error'])->default('connected');
            $table->text('error_message')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('channel_id');
            $table->index('platform');
            $table->index('status');
            $table->index(['organization_id', 'platform']);
            $table->index('token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_connections');
    }
};


