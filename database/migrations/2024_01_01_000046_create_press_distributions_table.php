<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('press_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('press_release_id')->constrained()->onDelete('cascade');
            $table->foreignId('press_contact_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'sent', 'delivered', 'opened', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('press_release_id');
            $table->index('press_contact_id');
            $table->index('status');
            $table->index(['press_release_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('press_distributions');
    }
};


