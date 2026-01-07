<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 10)->default('en')->after('timezone');
            $table->string('country_code', 2)->nullable()->after('locale');
            
            $table->index('locale');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->string('locale', 10)->default('en')->after('timezone');
            $table->string('country_code', 2)->nullable()->after('locale');
            $table->json('supported_locales')->nullable()->after('country_code');
            
            $table->index('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['locale']);
            $table->dropColumn(['locale', 'country_code']);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex(['locale']);
            $table->dropColumn(['locale', 'country_code', 'supported_locales']);
        });
    }
};

