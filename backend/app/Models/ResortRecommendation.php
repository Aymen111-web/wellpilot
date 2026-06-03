<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResortRecommendation extends Model
{
    protected $fillable = [
        'wellness_category',
        'activity_name',
        'description'
    ];
}
