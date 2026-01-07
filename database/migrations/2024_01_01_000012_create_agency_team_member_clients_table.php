<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_team_member_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['agency_id', 'user_id', 'organization_id']);
            $table->index(['agency_id', 'user_id']);
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_team_member_clients');
    }
};

