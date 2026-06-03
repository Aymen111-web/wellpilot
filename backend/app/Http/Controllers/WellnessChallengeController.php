<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WellnessChallengeController extends Controller
{
    public function index()
    {
        return response()->json(\App\Models\WellnessChallenge::all());
    }

    public function complete(Request $request, $id)
    {
        $request->validate([
            'nickname' => 'required|string|max:191',
            'reflection_text' => 'nullable|string|max:5000',
        ]);

        $nickname = trim($request->nickname);
        $challenge = \App\Models\WellnessChallenge::findOrFail($id);
        $category = $challenge->category;

        // Check if there is already a completion in the same category today
        $todayStart = \Carbon\Carbon::today();
        $todayEnd = \Carbon\Carbon::today()->endOfDay();

        $alreadyCompleted = \App\Models\ChallengeCompletion::where('nickname', $nickname)
            ->whereHas('challenge', function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->whereBetween('completed_at', [$todayStart, $todayEnd])
            ->exists();

        if ($alreadyCompleted) {
            return response()->json([
                'error' => 'You have already completed a challenge in this category today. Please come back tomorrow.',
                'error_am' => 'ዛሬ በዚህ ዘርፍ ሌላ ፈተና አጠናቀዋል። እባክዎን ነገ ይመለሱ።'
            ], 400);
        }

        // Complete the challenge
        $completion = \App\Models\ChallengeCompletion::create([
            'nickname' => $nickname,
            'challenge_id' => $challenge->id,
            'reflection_text' => $request->reflection_text,
            'points_awarded' => $challenge->reward_points,
            'completed_at' => \Carbon\Carbon::now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Challenge completed successfully!',
            'completion' => $completion,
            'points_earned' => $challenge->reward_points,
        ]);
    }

    public function stats(Request $request)
    {
        $nickname = trim($request->query('nickname', 'Guest'));

        $completions = \App\Models\ChallengeCompletion::where('nickname', $nickname)
            ->with('challenge')
            ->orderBy('completed_at', 'desc')
            ->get();

        $totalCompleted = $completions->count();
        $totalPoints = $completions->sum('points_awarded');

        // Calculate streak
        $streak = 0;
        if ($totalCompleted > 0) {
            $dates = $completions->map(function ($c) {
                return \Carbon\Carbon::parse($c->completed_at)->startOfDay();
            })->unique()->values();

            $today = \Carbon\Carbon::today();
            $yesterday = \Carbon\Carbon::yesterday();

            if ($dates[0]->equalTo($today) || $dates[0]->equalTo($yesterday)) {
                $streak = 1;
                for ($i = 0; $i < $dates->count() - 1; $i++) {
                    $diff = $dates[$i]->diffInDays($dates[$i + 1]);
                    if ($diff === 1) {
                        $streak++;
                    } elseif ($diff > 1) {
                        break;
                    }
                }
            }
        }

        // Categories completed today
        $todayStart = \Carbon\Carbon::today();
        $todayEnd = \Carbon\Carbon::today()->endOfDay();

        $completedCategoriesToday = \App\Models\ChallengeCompletion::where('nickname', $nickname)
            ->whereBetween('completed_at', [$todayStart, $todayEnd])
            ->get()
            ->map(function ($c) {
                return $c->challenge->category;
            })
            ->unique()
            ->values()
            ->toArray();

        // Recent reflections
        $recentReflections = $completions->filter(function ($c) {
            return !empty($c->reflection_text);
        })->take(10)->map(function ($c) {
            return [
                'challenge_name' => $c->challenge->challenge_name,
                'challenge_name_am' => $c->challenge->challenge_name_am,
                'reflection_text' => $c->reflection_text,
                'completed_at' => $c->completed_at->toIso8601String(),
                'category' => $c->challenge->category,
            ];
        })->values()->toArray();

        return response()->json([
            'nickname' => $nickname,
            'total_completed' => $totalCompleted,
            'wellness_points' => $totalPoints,
            'streak' => $streak,
            'completed_categories_today' => $completedCategoriesToday,
            'recent_reflections' => $recentReflections,
        ]);
    }
}
