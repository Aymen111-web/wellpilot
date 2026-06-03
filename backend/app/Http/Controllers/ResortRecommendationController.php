<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResortRecommendationController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category');
        
        if ($category) {
            $recommendations = \App\Models\ResortRecommendation::where('wellness_category', $category)->get();
        } else {
            $recommendations = \App\Models\ResortRecommendation::all();
        }

        return response()->json($recommendations);
    }
}
