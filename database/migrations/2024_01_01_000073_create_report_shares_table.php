<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->foreignId('shared_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('shared_with')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('share_token')->unique();
            $table->enum('access_level', ['view', 'edit'])->default('view');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('report_id');
            $table->index('shared_by');
            $table->index('shared_with');
            $table->index('share_token');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_shares');
    }
};


