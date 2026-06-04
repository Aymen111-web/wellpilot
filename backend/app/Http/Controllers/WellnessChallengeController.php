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
        $user = \App\Models\User::firstOrCreate(['nickname' => $nickname]);
        $challenge = \App\Models\WellnessChallenge::findOrFail($id);
        $category = $challenge->category;

        // Check if there is already a completion in the same category today
        $todayStart = \Carbon\Carbon::today();
        $todayEnd = \Carbon\Carbon::today()->endOfDay();

        $alreadyCompleted = \App\Models\ChallengeCompletion::where('user_id', $user->id)
            ->whereHas('challenge', function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->whereBetween('completion_date', [$todayStart, $todayEnd])
            ->exists();

        if ($alreadyCompleted) {
            return response()->json([
                'error' => 'You have already completed a challenge in this category today. Please come back tomorrow.',
                'error_am' => 'ዛሬ በዚህ ዘርፍ ሌላ ፈተና አጠናቀዋል። እባክዎን ነገ ይመለሱ።'
            ], 400);
        }

        // Complete the challenge
        $completion = \App\Models\ChallengeCompletion::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'points_awarded' => $challenge->reward_points,
            'completion_date' => \Carbon\Carbon::now(),
        ]);

        if (!empty($request->reflection_text)) {
            \App\Models\ChallengeReflection::create([
                'user_id' => $user->id,
                'challenge_completion_id' => $completion->id,
                'reflection' => $request->reflection_text,
            ]);
        }

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
        $user = \App\Models\User::where('nickname', $nickname)->first();

        if (!$user) {
            return response()->json([
                'nickname' => $nickname,
                'total_completed' => 0,
                'wellness_points' => 0,
                'streak' => 0,
                'completed_categories_today' => [],
                'recent_reflections' => [],
            ]);
        }

        $completions = \App\Models\ChallengeCompletion::where('user_id', $user->id)
            ->with('challenge')
            ->orderBy('completion_date', 'desc')
            ->get();

        $totalCompleted = $completions->count();
        $totalPoints = $completions->sum('points_awarded');

        // Calculate streak
        $streak = 0;
        if ($totalCompleted > 0) {
            $dates = $completions->map(function ($c) {
                return \Carbon\Carbon::parse($c->completion_date)->startOfDay();
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

        $completedCategoriesToday = \App\Models\ChallengeCompletion::where('user_id', $user->id)
            ->whereBetween('completion_date', [$todayStart, $todayEnd])
            ->get()
            ->map(function ($c) {
                return $c->challenge->category;
            })
            ->unique()
            ->values()
            ->toArray();

        // Recent reflections
        $recentReflections = \App\Models\ChallengeReflection::where('user_id', $user->id)
            ->with('challengeCompletion.challenge')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($r) {
                return [
                    'challenge_name' => $r->challengeCompletion->challenge->challenge_name ?? '',
                    'challenge_name_am' => $r->challengeCompletion->challenge->challenge_name_am ?? '',
                    'reflection_text' => $r->reflection,
                    'completed_at' => $r->created_at->toIso8601String(),
                    'category' => $r->challengeCompletion->challenge->category ?? '',
                ];
            })
            ->values()
            ->toArray();

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
