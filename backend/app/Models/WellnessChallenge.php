<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WellnessChallenge extends Model
{
    protected $fillable = [
        'challenge_name',
        'description',
        'duration_days',
        'reward_points'
    ];
}
