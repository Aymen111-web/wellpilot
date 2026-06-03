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
        Schema::create('challenge_completions', function (Blueprint $table) {
            $table->id();
            $table->string('nickname');
            $table->unsignedBigInteger('challenge_id');
            $table->text('reflection_text')->nullable();
            $table->integer('points_awarded');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('challenge_id')->references('id')->on('wellness_challenges')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_completions');
    }
};
