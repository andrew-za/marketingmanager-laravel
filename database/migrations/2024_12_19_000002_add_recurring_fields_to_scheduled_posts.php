<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('status');
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly', 'custom'])->nullable()->after('is_recurring');
            $table->json('recurrence_config')->nullable()->after('recurrence_type');
            $table->dateTime('recurrence_end_date')->nullable()->after('recurrence_config');
            $table->integer('recurrence_count')->nullable()->after('recurrence_end_date');
            $table->foreignId('parent_post_id')->nullable()->constrained('scheduled_posts')->onDelete('cascade')->after('recurrence_count');
            
            $table->index('is_recurring');
            $table->index('parent_post_id');
            $table->index(['is_recurring', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_posts', function (Blueprint $table) {
            $table->dropForeign(['parent_post_id']);
            $table->dropIndex(['is_recurring', 'scheduled_at']);
            $table->dropIndex(['parent_post_id']);
            $table->dropIndex(['is_recurring']);
            $table->dropColumn([
                'is_recurring',
                'recurrence_type',
                'recurrence_config',
                'recurrence_end_date',
                'recurrence_count',
                'parent_post_id',
            ]);
        });
    }
};


