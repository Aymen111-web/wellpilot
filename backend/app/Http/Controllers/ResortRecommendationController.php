<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ResortRecommendation;
use App\Models\WellnessAssessment;

class ResortRecommendationController extends Controller
{
    public function index(Request $request)
    {
        $nickname = $request->query('nickname');
        $allActivities = ResortRecommendation::all();

        // Retrieve latest assessment for the provided nickname if any
        $assessment = null;
        if ($nickname) {
            $assessment = WellnessAssessment::where('nickname', $nickname)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        // Fallback default mock assessment if no history is found
        if (!$assessment) {
            $assessment = new WellnessAssessment([
                'nickname' => $nickname ?: 'Guest',
                'stress_level' => 5,
                'sleep_hours' => 7.5,
                'water_intake' => 2.5,
                'activity_level' => 'medium',
                'mood_level' => 'neutral',
                'wellness_score' => 70,
            ]);
        }

        // Categorize Wellness Zone based on score
        $score = $assessment->wellness_score;
        if ($score >= 80) {
            $insight = [
                'zone_name' => 'Thriving Zone',
                'zone_name_am' => 'የበለጸገ ደረጃ',
                'description' => 'Exceptional self-care! You have highly balanced wellness habits. Keep up this incredible routine and enjoy active resort experiences.',
                'description_am' => 'ልዩ የሆነ ራስን የመንከባከብ ልምድ! ከፍተኛ ሚዛናዊ የሆኑ የጤና ልምዶች አሉዎት። ይህንን አስደናቂ ልማድ ይቀጥሉበት እና በንቁ የሪዞርት ተሞክሮዎች ይደሰቱ።',
                'recommended_action' => 'Try participating in active or social wellness events to maintain your momentum.',
                'recommended_action_am' => 'ይህን ለማስቀጠል ንቁ የአካል ብቃት እንቅስቃሴዎች ወይም ማህበራዊ የጤና ፕሮግራሞች ላይ ለመሳተፍ ይሞክሩ።',
            ];
        } elseif ($score >= 60) {
            $insight = [
                'zone_name' => 'Balancing Zone',
                'zone_name_am' => 'የተመጣጠነ ደረጃ',
                'description' => 'Good foundation! You are maintaining a healthy wellness baseline. Focused attention on weaker wellness areas can significantly improve your overall well-being.',
                'description_am' => 'ጥሩ መሠረት አለዎት። በአጠቃላይ ጤናዎን በጥሩ ሁኔታ እየጠበቁ ነው። ነገር ግን ዝቅተኛ ውጤት ባሳዩባቸው ዘርፎች ላይ ትኩረት ካደረጉ የተሻለ ውጤት ማግኘት ይችላሉ።',
                'recommended_action' => 'Try participating in a Sunrise Beach Yoga session to enhance balance and mindfulness.',
                'recommended_action_am' => 'የበለጠ ሚዛናዊነትን እና ንቁነትን ለማግኘት በማለዳ የባህር ዳርቻ ዮጋ ላይ ለመሳተፍ ይሞክሩ።',
            ];
        } else {
            $insight = [
                'zone_name' => 'Healing Zone',
                'zone_name_am' => 'የማገገሚያ ደረጃ',
                'description' => 'Recovery needed. Your mind and body are sending warning signs. This is the perfect time to step back, rest, and engage in deeply restorative activities.',
                'description_am' => 'ማገገም ያስፈልጋል። አእምሮዎ እና አካልዎ የማስጠንቀቂያ ምልክቶችን እየላኩ ነው። ይህ ጊዜ እረፍት ለመውሰድ እና አካልን የሚያድሱ ተግባራት ላይ ለመሳተፍ ትክክለኛው ጊዜ ነው።',
                'recommended_action' => 'Begin with a Guided Forest Mindful Meditation or Thermal Mineral Hot Springs Soak to release deep tension.',
                'recommended_action_am' => 'የተከማቸ ውጥረትን ለማርገብ በጫካ ውስጥ በሚመራ አእምሮ ማረጋጊያ ወይም በሙቅ ፍልውሃ መዘፈቅ ይጀምሩ።',
            ];
        }

        // Personalized matching logic
        $matched = collect();
        $reasons = [];

        // Check Stress deficit
        if ($assessment->stress_level >= 6) {
            $activities = ResortRecommendation::where('wellness_category', 'Stress')->get();
            $matched = $matched->merge($activities);
            foreach ($activities as $act) {
                $reasons[$act->id] = [
                    'why' => "Recommended because your stress level is high ({$assessment->stress_level}/10), requiring relaxing body-mind therapies to lower cortisol.",
                    'why_am' => "የጭንቀትዎ መጠን ከፍተኛ ({$assessment->stress_level}/10) በመሆኑ የሰውነትና የአእምሮ ውጥረትን ለመቀነስ ይህ ተግባር ተመርጧል።"
                ];
            }
        }

        // Check Sleep deficit
        if ($assessment->sleep_hours < 7) {
            $activities = ResortRecommendation::where('wellness_category', 'Sleep')->get();
            $matched = $matched->merge($activities);
            foreach ($activities as $act) {
                $reasons[$act->id] = [
                    'why' => "Recommended due to your low sleep duration ({$assessment->sleep_hours} hrs), aiming to restore natural sleep cycles and promote deep rest.",
                    'why_am' => "የእንቅልፍ ሰዓትዎ አነስተኛ ({$assessment->sleep_hours} ሰዓት) በመሆኑ ተፈጥሯዊ የእንቅልፍ ዑደትን ለማስተካከልና ጥልቅ እረፍት ለማግኘት ተመርጧል።"
                ];
            }
        }

        // Check Activity deficit
        if ($assessment->activity_level === 'low') {
            $activities = ResortRecommendation::where('wellness_category', 'Physical Activity')->get();
            $matched = $matched->merge($activities);
            foreach ($activities as $act) {
                $reasons[$act->id] = [
                    'why' => "Recommended because of your sedentary/low physical activity level, designed to introduce gentle movement and boost energy.",
                    'why_am' => "የአካል ብቃት እንቅስቃሴዎ አነስተኛ በመሆኑ፣ ቀላል እንቅስቃሴዎችን በመጀመር የሰውነትን የሜታቦሊም ፍጥነት ለመጨመር ተመርጧል።"
                ];
            }
        }

        // Check Hydration deficit
        if ($assessment->water_intake < 2.5) {
            $activities = ResortRecommendation::where('wellness_category', 'Hydration')->get();
            $matched = $matched->merge($activities);
            foreach ($activities as $act) {
                $reasons[$act->id] = [
                    'why' => "Recommended because your water intake is below the recommended level ({$assessment->water_intake} L), helping to restore cellular hydration.",
                    'why_am' => "የዕለታዊ የውሃ ፍጆታዎ ({$assessment->water_intake} ሊትር) ዝቅተኛ በመሆኑ የሰውነትዎን ፈሳሽ መጠን ለመተካት ተመርጧል።"
                ];
            }
        }

        // Check Mood deficit
        if (in_array($assessment->mood_level, ['sad', 'stressed'])) {
            $activities = ResortRecommendation::where('wellness_category', 'Mood')->get();
            $matched = $matched->merge($activities);
            foreach ($activities as $act) {
                $reasons[$act->id] = [
                    'why' => "Recommended because you reported feeling {$assessment->mood_level}, helping to elevate mood and release positive endorphins.",
                    'why_am' => "የዛሬ ስሜትዎ ({$assessment->mood_level}) ዝቅተኛ ሆኖ በመመዝገቡ፣ ስሜትዎን ለማሻሻልና አዎንታዊ ሆርሞኖችን ለማመንጨት ተመርጧል።"
                ];
            }
        }

        // Check Excellent Wellness Score (75+)
        if ($score >= 75) {
            $activeMood = ResortRecommendation::whereIn('wellness_category', ['Physical Activity', 'Mood'])->get();
            $matched = $matched->merge($activeMood);
            foreach ($activeMood as $act) {
                if (!isset($reasons[$act->id])) {
                    $reasons[$act->id] = [
                        'why' => "Recommended because of your excellent wellness profile ({$score}/100) to keep you active, social, and thriving.",
                        'why_am' => "ምርጥ የጤና ውጤት (${score}/100) ስላስመዘገቡ፣ ንቁ እና ማህበራዊ ሆነው እንዲቀጥሉ ተመርጧል።"
                    ];
                }
            }
        }

        // Fallback to Balanced Wellness mix if empty
        if ($matched->isEmpty()) {
            $balanced = ResortRecommendation::whereIn('wellness_category', ['Stress', 'Mood', 'Sleep'])->get();
            $matched = $matched->merge($balanced);
            foreach ($balanced as $act) {
                $reasons[$act->id] = [
                    'why' => "Recommended to support your overall wellness balance and maintain healthy body-mind alignment.",
                    'why_am' => "አጠቃላይ የአካልና አእምሮ ጤናዎን ለማገዝና ጤናማ ሚዛንን ለመጠበቅ ተመርጧል።"
                ];
            }
        }

        // De-duplicate matched list by ID
        $matched = $matched->unique('id')->values();

        // Enforce Limits (Min 2, Max 4)
        if ($matched->count() > 4) {
            $matched = $matched->take(4);
        } elseif ($matched->count() < 2) {
            $fillers = ResortRecommendation::whereNotIn('id', $matched->pluck('id'))->get();
            $needed = 2 - $matched->count();
            $matched = $matched->merge($fillers->take($needed));
        }

        // Map recommendations with explanations
        $recommendations = $matched->map(function ($act) use ($reasons) {
            $why = $reasons[$act->id]['why'] ?? "Recommended to support your overall wellness balance.";
            $whyAm = $reasons[$act->id]['why_am'] ?? "አጠቃላይ የአካልና አእምሮ ጤናዎን ለማገዝ ተመርጧል።";

            $actArray = $act->toArray();
            $actArray['why_recommended'] = $why;
            $actArray['why_recommended_am'] = $whyAm;
            return $actArray;
        });

        return response()->json([
            'nickname' => $assessment->nickname,
            'has_history' => $nickname && WellnessAssessment::where('nickname', $nickname)->exists(),
            'wellness_insight' => $insight,
            'recommendations' => $recommendations,
            'all_activities' => $allActivities
        ]);
    }
}
