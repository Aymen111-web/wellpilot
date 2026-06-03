<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WellnessAssessmentController extends Controller
{
    public function index()
    {
        // Return all assessments in chronological order to plot trends in the dashboard
        $assessments = \App\Models\WellnessAssessment::orderBy('created_at', 'asc')->get();
        return response()->json($assessments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nickname' => 'required|string|max:191',
            'stress_level' => 'required|integer|min:1|max:10',
            'sleep_hours' => 'required|numeric|min:0|max:24',
            'water_intake' => 'required|numeric|min:0|max:20',
            'activity_level' => 'required|string|in:low,medium,high',
            'mood_level' => 'required|string|in:sad,stressed,neutral,happy,excited',
        ]);

        // 1. Calculate Stress Score (Inverted: low stress is good)
        $stressScore = (10 - $request->stress_level) * 10;

        // 2. Calculate Sleep Score
        $sleep = $request->sleep_hours;
        if ($sleep >= 8) $sleepScore = 100;
        elseif ($sleep >= 7) $sleepScore = 85;
        elseif ($sleep >= 6) $sleepScore = 65;
        elseif ($sleep >= 5) $sleepScore = 40;
        else $sleepScore = 20;

        // 3. Calculate Water Intake Score
        $water = $request->water_intake;
        if ($water >= 3) $waterScore = 100;
        elseif ($water >= 2.5) $waterScore = 90;
        elseif ($water >= 2) $waterScore = 75;
        elseif ($water >= 1) $waterScore = 40;
        else $waterScore = 20;

        // 4. Calculate Activity Score
        $activity = $request->activity_level;
        if ($activity === 'high') $activityScore = 100;
        elseif ($activity === 'medium') $activityScore = 80;
        else $activityScore = 40;

        // 5. Calculate Mood Score
        $mood = $request->mood_level;
        if ($mood === 'excited') $moodScore = 100;
        elseif ($mood === 'happy') $moodScore = 90;
        elseif ($mood === 'neutral') $moodScore = 70;
        elseif ($mood === 'stressed') $moodScore = 40;
        else $moodScore = 30; // sad

        // Calculate final average wellness score
        $wellnessScore = (int) round(($stressScore + $sleepScore + $waterScore + $activityScore + $moodScore) / 5);

        // Generate tailored suggestions
        $suggestionItems = [];

        if ($request->stress_level >= 6) {
            $suggestionItems[] = "Your stress levels are elevated. Prioritize deep breathing exercises, quiet nature walks, and try to carve out 10-15 minute mindfulness breaks during busy hours.";
        }
        if ($sleep < 7) {
            $suggestionItems[] = "Your sleep duration is below the recommended range. Try establishing a calming, screen-free pre-sleep wind-down routine and aim for a consistent bedtime.";
        }
        if ($water < 2.5) {
            $suggestionItems[] = "Your water intake is sub-optimal. Keep a reusable water bottle near your desk, and try setting hourly reminders to sip water. Aim for at least 8-10 glasses.";
        }
        if ($activity === 'low') {
            $suggestionItems[] = "Your activity level is low. Add short periods of low-intensity movement to your schedule—such as a 20-minute post-lunch walk or a brief swimming session.";
        }
        if (in_array($mood, ['sad', 'stressed'])) {
            $suggestionItems[] = "Your mood suggests emotional exhaustion. Be kind to yourself, practice expressive journal writing, and try engaging in creative, tactile activities like clay modeling or painting.";
        }

        // Add overall zone feedback
        if ($wellnessScore >= 80) {
            $zoneFeedback = "Thriving Zone: Exceptional self-care! You have highly balanced wellness habits. Keep up this incredible routine, and consider rewarding yourself with a luxurious resort experience like our Deep Tissue Massage or a Sunset Lagoon Kayaking adventure.";
        } elseif ($wellnessScore >= 60) {
            $zoneFeedback = "Balancing Zone: Good foundation! You are maintaining a healthy baseline, but focused attention on weaker areas will significantly boost your well-being. Try adding a Sunrise Beach Yoga session to bring extra centering energy.";
        } else {
            $zoneFeedback = "Healing Zone: Recovery needed. Your mind and body are sending warning signs. This is the perfect time to step back, rest, and engage in deeply restorative activities like our Acoustic Sound Bath or guided Forest Meditation.";
        }

        // Combine into a formatted text string
        $suggestions = $zoneFeedback;
        if (count($suggestionItems) > 0) {
            $suggestions .= "\n\nSpecific Recommendations:\n- " . implode("\n- ", $suggestionItems);
        }

        $assessment = \App\Models\WellnessAssessment::create([
            'nickname' => $request->nickname,
            'stress_level' => $request->stress_level,
            'sleep_hours' => $request->sleep_hours,
            'water_intake' => $request->water_intake,
            'activity_level' => $request->activity_level,
            'mood_level' => $request->mood_level,
            'wellness_score' => $wellnessScore,
            'suggestions' => $suggestions,
        ]);

        return response()->json($assessment, 217); // 217 created status
    }
}
