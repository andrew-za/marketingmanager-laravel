<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaign_contact_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_list_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['email_campaign_id', 'contact_list_id']);
            $table->index('email_campaign_id');
            $table->index('contact_list_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_contact_lists');
    }
};


