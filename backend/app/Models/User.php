<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['nickname'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public function wellnessAssessments()
    {
        return $this->hasMany(WellnessAssessment::class);
    }

    public function challengeCompletions()
    {
        return $this->hasMany(ChallengeCompletion::class);
    }

    public function challengeReflections()
    {
        return $this->hasMany(ChallengeReflection::class);
    }

    public function aiConversations()
    {
        return $this->hasMany(AiConversation::class);
    }
}
