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
        Schema::create('wellness_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('nickname');
            $table->integer('stress_level');
            $table->double('sleep_hours');
            $table->double('water_intake');
            $table->string('activity_level');
            $table->string('mood_level');
            $table->integer('wellness_score');
            $table->text('suggestions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wellness_assessments');
    }
};
