<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('display_name');
            $table->enum('type', ['email', 'whatsapp', 'amplify', 'paid_ads', 'press_release', 'influencer', 'social']);
            $table->string('platform')->nullable();
            $table->enum('status', ['active', 'inactive', 'disconnected'])->default('active');
            $table->timestamps();

            $table->index(['organization_id', 'type']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};

