<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeCompletion extends Model
{
    protected $fillable = [
        'nickname',
        'challenge_id',
        'reflection_text',
        'points_awarded',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function challenge()
    {
        return $this->belongsTo(WellnessChallenge::class, 'challenge_id');
    }
}
