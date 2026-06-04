<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeCompletion extends Model
{
    protected $fillable = [
        'user_id',
        'challenge_id',
        'points_awarded',
        'completion_date'
    ];

    protected $casts = [
        'completion_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challenge()
    {
        return $this->belongsTo(WellnessChallenge::class, 'challenge_id');
    }

    public function reflections()
    {
        return $this->hasMany(ChallengeReflection::class);
    }
}
