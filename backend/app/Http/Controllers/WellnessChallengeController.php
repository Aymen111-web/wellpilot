<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WellnessChallengeController extends Controller
{
    public function index()
    {
        return response()->json(\App\Models\WellnessChallenge::all());
    }
}
