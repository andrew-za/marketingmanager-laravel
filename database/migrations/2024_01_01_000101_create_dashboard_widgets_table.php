<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('widget_type');
            $table->string('title');
            $table->json('config')->nullable();
            $table->integer('position_x')->default(0);
            $table->integer('position_y')->default(0);
            $table->integer('width')->default(4);
            $table->integer('height')->default(4);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('organization_id');
            $table->index('widget_type');
            $table->index(['user_id', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};


