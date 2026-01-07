<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('image_library', function (Blueprint $table) {
            $table->foreignId('folder_id')->nullable()->after('organization_id')->constrained('file_folders')->onDelete('set null');
            $table->foreignId('parent_file_id')->nullable()->after('folder_id')->constrained('image_library')->onDelete('set null');
            $table->integer('version')->default(1)->after('parent_file_id');
            $table->enum('storage_provider', ['local', 's3', 'google_drive', 'dropbox'])->default('local')->after('source');
            $table->string('storage_path')->nullable()->after('storage_provider');
            $table->boolean('is_shared')->default(false)->after('storage_path');
            $table->json('shared_with')->nullable()->after('is_shared');
            $table->json('permissions')->nullable()->after('shared_with');
            
            $table->index('folder_id');
            $table->index('parent_file_id');
            $table->index(['organization_id', 'folder_id']);
            $table->index('storage_provider');
        });
    }

    public function down(): void
    {
        Schema::table('image_library', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropForeign(['parent_file_id']);
            $table->dropIndex(['folder_id']);
            $table->dropIndex(['parent_file_id']);
            $table->dropIndex(['organization_id', 'folder_id']);
            $table->dropIndex(['storage_provider']);
            $table->dropColumn([
                'folder_id',
                'parent_file_id',
                'version',
                'storage_provider',
                'storage_path',
                'is_shared',
                'shared_with',
                'permissions',
            ]);
        });
    }
};

