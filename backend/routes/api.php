<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WellnessAssessmentController;
use App\Http\Controllers\WellnessChallengeController;
use App\Http\Controllers\ResortRecommendationController;
use App\Http\Controllers\AiCoachController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/assessments', [WellnessAssessmentController::class, 'index']);
Route::post('/assessments', [WellnessAssessmentController::class, 'store']);
Route::get('/challenges', [WellnessChallengeController::class, 'index']);
Route::get('/challenges/stats', [WellnessChallengeController::class, 'stats']);
Route::post('/challenges/{id}/complete', [WellnessChallengeController::class, 'complete']);
Route::get('/resorts', [ResortRecommendationController::class, 'index']);
Route::post('/ai-coach', [AiCoachController::class, 'chat']);
Route::get('/ai-coach/status', [AiCoachController::class, 'status']);
Route::get('/ai-coach/tts', [AiCoachController::class, 'speak']);
