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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('requires_guard_day')->default(false);
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('activity_user', function (Blueprint $table) {
            $table->id();
            $table->date('date_add_to_user_activity')->nullable();
            $table->boolean('recommendation_letter')->default(false);
            $table->foreignId('activity_id')->constrained('activities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['activity_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('activity_user');
    }
};
