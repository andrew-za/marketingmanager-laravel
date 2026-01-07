<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->string('custom_domain')->nullable()->after('slug');
            $table->json('seo_settings')->nullable()->after('custom_domain');
            $table->json('template_data')->nullable()->after('seo_settings');
        });

        Schema::create('landing_page_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('template_content');
            $table->json('template_data')->nullable();
            $table->string('category')->nullable();
            $table->json('preview_images')->nullable();
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('organization_id');
            $table->index('category');
            $table->index('is_public');
            $table->index(['organization_id', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::table('landing_pages', function (Blueprint $table) {
            $table->dropColumn(['custom_domain', 'seo_settings', 'template_data']);
        });

        Schema::dropIfExists('landing_page_templates');
    }
};

