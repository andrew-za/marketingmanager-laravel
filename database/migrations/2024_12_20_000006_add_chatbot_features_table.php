<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbots', function (Blueprint $table) {
            $table->json('conversation_flow')->nullable()->after('training_data');
            $table->json('supported_languages')->nullable()->after('conversation_flow');
            $table->string('default_language')->default('en')->after('supported_languages');
            $table->json('brand_information')->nullable()->after('default_language');
            $table->json('analytics_settings')->nullable()->after('brand_information');
        });
    }

    public function down(): void
    {
        Schema::table('chatbots', function (Blueprint $table) {
            $table->dropColumn([
                'conversation_flow',
                'supported_languages',
                'default_language',
                'brand_information',
                'analytics_settings',
            ]);
        });
    }
};

