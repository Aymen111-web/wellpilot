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
        Schema::create('wellness_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('challenge_name');
            $table->string('challenge_name_am');
            $table->text('description');
            $table->text('description_am');
            $table->integer('duration_days');
            $table->integer('reward_points');
            $table->string('category');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wellness_challenges');
    }
};
