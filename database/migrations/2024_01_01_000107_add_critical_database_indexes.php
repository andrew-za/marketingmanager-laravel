<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table) {
            $table->index('scheduled_at');
            $table->index('organization_id');
        });

        Schema::table('published_posts', function (Blueprint $table) {
            $table->index(['scheduled_post_id', 'status']);
        });

        Schema::table('usage_tracking', function (Blueprint $table) {
            $table->index(['organization_id', 'date']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->index(['chat_topic_id', 'created_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read', 'created_at']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['organization_id', 'assignee_id', 'status']);
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->index(['organization_id', 'status', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table) {
            $table->dropIndex(['scheduled_at']);
            $table->dropIndex(['organization_id']);
        });

        Schema::table('published_posts', function (Blueprint $table) {
            $table->dropIndex(['scheduled_post_id', 'status']);
        });

        Schema::table('usage_tracking', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'date']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropIndex(['chat_topic_id', 'created_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_read', 'created_at']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'assignee_id', 'status']);
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'status', 'start_date']);
        });
    }
};

