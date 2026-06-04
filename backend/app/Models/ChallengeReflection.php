<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeReflection extends Model
{
    protected $fillable = [
        'user_id',
        'challenge_completion_id',
        'reflection'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function challengeCompletion()
    {
        return $this->belongsTo(ChallengeCompletion::class);
    }
}
