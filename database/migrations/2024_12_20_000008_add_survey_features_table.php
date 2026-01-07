<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->json('distribution_settings')->nullable()->after('status');
            $table->json('analytics_settings')->nullable()->after('distribution_settings');
        });

        Schema::create('survey_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->enum('distribution_type', ['email', 'link', 'embed'])->default('link');
            $table->string('distribution_key')->unique();
            $table->json('settings')->nullable();
            $table->integer('sent_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('completed_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('survey_id');
            $table->index('distribution_type');
            $table->index('distribution_key');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn(['distribution_settings', 'analytics_settings']);
        });

        Schema::dropIfExists('survey_distributions');
    }
};

