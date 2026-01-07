<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_list_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_list_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamps();

            $table->unique(['contact_list_id', 'contact_id']);
            $table->index('contact_list_id');
            $table->index('contact_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_list_contacts');
    }
};


