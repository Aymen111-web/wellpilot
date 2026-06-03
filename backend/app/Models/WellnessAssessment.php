<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WellnessAssessment extends Model
{
    protected $fillable = [
        'nickname',
        'stress_level',
        'sleep_hours',
        'water_intake',
        'activity_level',
        'mood_level',
        'wellness_score',
        'suggestions'
    ];
}
