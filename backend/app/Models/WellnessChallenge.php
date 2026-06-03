<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WellnessChallenge extends Model
{
    protected $fillable = [
        'challenge_name',
        'challenge_name_am',
        'description',
        'description_am',
        'duration_days',
        'reward_points',
        'category'
    ];

    public function completions()
    {
        return $this->hasMany(ChallengeCompletion::class, 'challenge_id');
    }
}
