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
        Schema::create('resort_recommendations', function (Blueprint $table) {
            $table->id();
            $table->string('wellness_category');
            $table->string('activity_name');
            $table->string('activity_name_am');
            $table->text('description');
            $table->text('description_am');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resort_recommendations');
    }
};
