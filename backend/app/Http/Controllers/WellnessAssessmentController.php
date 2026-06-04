<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WellnessAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $nickname = $request->query('nickname');
        if (empty($nickname)) {
            return response()->json([]);
        }
        $user = \App\Models\User::where('nickname', $nickname)->first();
        if (!$user) {
            return response()->json([]);
        }
        // Return only the current user's assessments in chronological order to plot trends in the dashboard
        $assessments = \App\Models\WellnessAssessment::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get();
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
            'lang' => 'nullable|string|in:en,am',
        ]);

        $isAm = ($request->lang === 'am');

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

        // Generate tailored suggestions matching new doc.md Resort Recommendation Logic
        $suggestionItems = [];

        // Condition: High Stress (stress score < 40)
        if ($stressScore < 40) {
            $suggestionItems[] = $isAm 
                ? "ከፍተኛ ውጥረት/ጭንቀት ተገኝቷል (የውጥረት መጠን ከ40 በታች)። የነርቭ ሥርዓትዎን ለማረጋጋት እንደ ስፓ ቴራፒ (Spa therapy)፣ ማሰላሰል (Meditation)፣ ተፈጥሮአዊ የእግር ጉዞ (Nature walks) እና የመዝናኛ ክፍለ-ጊዜዎች (Relaxation sessions) ባሉ ዘና የሚያደርጉ የሪዞርት እንቅስቃሴዎች ላይ እንዲሳተፉ እንመክራለን።"
                : "High Stress detected (Stress score < 40). We recommend participating in relaxing resort activities like Spa therapy, Meditation, Nature walks, and Relaxation sessions to help calm your nervous system.";
        }

        // Condition: Low Activity (activity level low)
        if ($activity === 'low') {
            $suggestionItems[] = $isAm 
                ? "አነስተኛ የአካል እንቅስቃሴ ተገኝቷል። እንደ ዋና (Swimming)፣ የአካል ብቃት ፕሮግራሞች (Fitness programs) እና የዮጋ ክፍለ-ጊዜዎች (Yoga sessions) ያሉ የአካል እንቅስቃሴዎችን በቆይታዎ ውስጥ ለማካተት ይሞክሩ።"
                : "Low Activity detected. Try incorporating movement into your stay, such as Swimming, Fitness programs, and Yoga sessions.";
        }

        // Condition: Poor Sleep (< 6 hrs)
        if ($sleep < 6) {
            $suggestionItems[] = $isAm 
                ? "አነስተኛ የእንቅልፍ ጥራት ተገኝቷል (ከ6 ሰዓት በታች)። የእንቅልፍ ልምድዎን እና ጉልበትዎን ለመመለስ የእንቅልፍ ጤና ፕሮግራሞችን (Sleep wellness programs) እና የመዝናኛ ማገገሚያዎችን (Relaxation retreats) እንዲጎበኙ እንመክራለን።"
                : "Poor Sleep Quality detected (under 6 hours). We recommend exploring Sleep wellness programs and Relaxation retreats to restore your sleep hygiene and energy.";
        }

        // Condition: Good Wellness (score >= 75)
        if ($wellnessScore >= 75) {
            $suggestionItems[] = $isAm 
                ? "ጥሩ የጤና ውጤት (75+)! በጣም ጥሩ እየሰሩ ነው። ይህንን ሚዛን ይጠብቁ እና እንደ ጀብዱ እንቅስቃሴዎች (Adventure activities) እና ማህበራዊ የጤና ዝግጅቶች (Social wellness events) ባሉ ንቁ የሪዞርት ተሞክሮዎች ይደሰቱ።"
                : "Good Wellness Score (75+)! You are doing great. Keep up the balance and enjoy active resort experiences like Adventure activities and Social wellness events.";
        }

        // Add overall zone feedback
        if ($wellnessScore >= 80) {
            $zoneFeedback = $isAm 
                ? "የበለጸገ ደረጃ (Thriving Zone)፦ ልዩ የሆነ ራስን የመንከባከብ ልምድ! ከፍተኛ ሚዛናዊ የሆኑ የጤና ልምዶች አሉዎት። ይህንን አስደናቂ ልማድ ይቀጥሉበት፣ እና እንደ ጥልቅ የአካል ማሸት (Deep Tissue Massage) ወይም የፀሐይ መጥለቂያ ካያኪንግ (Sunset Lagoon Kayaking) ባሉ የቅንጦት የሪዞርት ተሞክሮዎች እራስዎን ይሸልሙ።"
                : "Thriving Zone: Exceptional self-care! You have highly balanced wellness habits. Keep up this incredible routine, and consider rewarding yourself with a luxurious resort experience like our Deep Tissue Massage or a Sunset Lagoon Kayaking adventure.";
        } elseif ($wellnessScore >= 60) {
            $zoneFeedback = $isAm 
                ? "የተመጣጠነ ደረጃ (Balancing Zone)፦ ጥሩ መሠረት! ጤናማ ሁኔታን እየጠበቁ ነው፣ ነገር ግን ደካማ ለሆኑ ክፍሎች ትኩረት መስጠት ደህንነትዎን በእጅጉ ያሻሽላል። ተጨማሪ የማረጋጋት ኃይልን ለማግኘት የማለዳ የባህር ዳርቻ ዮጋ (Sunrise Beach Yoga) ለመሞከር ይሞክሩ።"
                : "Balancing Zone: Good foundation! You are maintaining a healthy baseline, but focused attention on weaker areas will significantly boost your well-being. Try adding a Sunrise Beach Yoga session to bring extra centering energy.";
        } else {
            $zoneFeedback = $isAm 
                ? "የማገገሚያ ደረጃ (Healing Zone)፦ ማገገም ያስፈልጋል። አእምሮዎ እና አካልዎ የማስጠንቀቂያ ምልክቶችን እየላኩ ነው። ይህ ጊዜ እረፍት ለመውሰድ እና እንደ የድምፅ መታጠቢያ (Acoustic Sound Bath) ወይም ጫካ ውስጥ ማሰላሰል (Forest Meditation) ባሉ ጥልቅ የሰውነት ማደሻ ተግባራት ላይ ለመሳተፍ ትክክለኛው ጊዜ ነው።"
                : "Healing Zone: Recovery needed. Your mind and body are sending warning signs. This is the perfect time to step back, rest, and engage in deeply restorative activities like our Acoustic Sound Bath or guided Forest Meditation.";
        }

        // Combine into a formatted text string
        $suggestions = $zoneFeedback;
        if (count($suggestionItems) > 0) {
            $header = $isAm ? "\n\nየተመረጡ ምክሮች፦\n- " : "\n\nSpecific Recommendations:\n- ";
            $suggestions .= $header . implode($isAm ? "\n- " : "\n- ", $suggestionItems);
        }

        $user = \App\Models\User::firstOrCreate(['nickname' => $request->nickname]);

        $assessment = \App\Models\WellnessAssessment::create([
            'user_id' => $user->id,
            'stress_level' => $request->stress_level,
            'sleep_hours' => $request->sleep_hours,
            'water_intake' => $request->water_intake,
            'activity_level' => $request->activity_level,
            'mood_level' => $request->mood_level,
            'wellness_score' => $wellnessScore,
            'suggestions' => $suggestions,
        ]);

        // Match categories for resort experiences catalog recommendations
        $matched = collect();
        $reasons = [];
        $fallbackReasons = [
            'Stress' => [
                'why' => "Recommended to help release mental tension and lower daily cortisol levels.",
                'why_am' => "የአእምሮ ውጥረትን ለመቀነስ እና የእለት ተእለት ጭንቀትን ለማቃለል ተመርጧል።"
            ],
            'Sleep' => [
                'why' => "Recommended to encourage deep muscle relaxation and prepare your body for restorative sleep.",
                'why_am' => "ጥልቅ የጡንቻ መዝናናትን ለማበረታታት እና ሰውነትዎን ለተሟላ እንቅልፍ ለማዘጋጀት ተመርጧል።"
            ],
            'Physical Activity' => [
                'why' => "Recommended to build core strength and increase overall cardiovascular vitality.",
                'why_am' => "የሰውነትዎን ጥንካሬ ለመገንባት እና የልብና የደም ዝውውርን ለማነቃቃት ተመርጧል።"
            ],
            'Hydration' => [
                'why' => "Recommended to support natural detoxification and improve cellular recovery.",
                'why_am' => "የሰውነት መርዛማ ንጥረ ነገሮችን ለማስወገድ እና የሴሎች ማገገምን ለመደገፍ ተመርጧል።"
            ],
            'Mood' => [
                'why' => "Recommended to stimulate endorphin release and lift your emotional outlook.",
                'why_am' => "የደስታ ሆርሞኖችን ለማነቃቃት እና ስሜታዊ ደህንነትዎን ከፍ ለማድረግ ተመርጧል።"
            ]
        ];

        // Check Stress deficit
        if ($request->stress_level >= 6) {
            $activities = \App\Models\ResortRecommendation::where('wellness_category', 'Stress')->get();
            $matched = $matched->merge($activities);
            foreach ($activities as $act) {
                $reasons[$act->id] = [
                    'why' => "Recommended because your stress level is high ({$request->stress_level}/10), requiring relaxing body-mind therapies to lower cortisol.",
                    'why_am' => "የጭንቀትዎ መጠን ከፍተኛ ({$request->stress_level}/10) በመሆኑ የሰውነትና የአእምሮ ውጥረትን ለመቀነስ ይህ ተግባር ተመርጧል።"
                ];
            }
        }

        // Check Sleep deficit
        if ($request->sleep_hours < 7) {
            $activities = \App\Models\ResortRecommendation::where('wellness_category', 'Sleep')->get();
            $matched = $matched->merge($activities);
            foreach ($activities as $act) {
                $reasons[$act->id] = [
                    'why' => "Recommended due to your low sleep duration ({$request->sleep_hours} hrs), aiming to restore natural sleep cycles and promote deep rest.",
                    'why_am' => "የእንቅልፍ ሰዓትዎ አነስተኛ ({$request->sleep_hours} ሰዓት) በመሆኑ ተፈጥሯዊ የእንቅልፍ ዑደትን ለማስተካከልና ጥልቅ እረፍት ለማግኘት ተመርጧል።"
                ];
            }
        }

        // Check Activity deficit
        if ($request->activity_level === 'low') {
            $activities = \App\Models\ResortRecommendation::where('wellness_category', 'Physical Activity')->get();
            $matched = $matched->merge($activities);
            foreach ($activities as $act) {
                $reasons[$act->id] = [
                    'why' => "Recommended because of your sedentary/low physical activity level, designed to introduce gentle movement and boost energy.",
                    'why_am' => "የአካል ብቃት እንቅስቃሴዎ አነስተኛ በመሆኑ፣ ቀላል እንቅስቃሴዎችን በመጀመር የሰውነትን የሜታቦሊም ፍጥነት ለመጨመር ተመርጧል።"
                ];
            }
        }

        // Check Hydration deficit
        if ($request->water_intake < 2.5) {
            $activities = \App\Models\ResortRecommendation::where('wellness_category', 'Hydration')->get();
            $matched = $matched->merge($activities);
            foreach ($activities as $act) {
                $reasons[$act->id] = [
                    'why' => "Recommended because your water intake is below the recommended level ({$request->water_intake} L), helping to restore cellular hydration.",
                    'why_am' => "የዕለታዊ የውሃ ፍጆታዎ ({$request->water_intake} ሊትር) ዝቅተኛ በመሆኑ የሰውነትዎን ፈሳሽ መጠን ለመተካት ተመርጧል።"
                ];
            }
        }

        // Check Mood deficit
        if (in_array($request->mood_level, ['sad', 'stressed'])) {
            $activities = \App\Models\ResortRecommendation::where('wellness_category', 'Mood')->get();
            $matched = $matched->merge($activities);
            foreach ($activities as $act) {
                $reasons[$act->id] = [
                    'why' => "Recommended because you reported feeling {$request->mood_level}, helping to elevate mood and release positive endorphins.",
                    'why_am' => "የዛሬ ስሜትዎ ({$request->mood_level}) ዝቅተኛ ሆኖ በመመዝገቡ፣ ስሜትዎን ለማሻሻልና አዎንታዊ ሆርሞኖችን ለማመንጨት ተመርጧል።"
                ];
            }
        }

        // Check Excellent Wellness Score (75+)
        if ($wellnessScore >= 75) {
            $activeMood = \App\Models\ResortRecommendation::whereIn('wellness_category', ['Physical Activity', 'Mood'])->get();
            $matched = $matched->merge($activeMood);
            foreach ($activeMood as $act) {
                if (!isset($reasons[$act->id])) {
                    if ($act->wellness_category === 'Physical Activity') {
                        $reasons[$act->id] = [
                            'why' => "Recommended because of your excellent wellness profile ({$wellnessScore}/100) to optimize your strength, flexibility, and physical endurance.",
                            'why_am' => "ምርጥ የጤና ውጤት ({$wellnessScore}/100) ስላስመዘገቡ፣ ጥንካሬዎን፣ ተለዋዋጭነትዎን እና የአካል ብቃትዎን ለማጎልበት ተመርጧል።"
                        ];
                    } else {
                        $reasons[$act->id] = [
                            'why' => "Recommended because of your excellent wellness profile ({$wellnessScore}/100) to keep you vibrant, mindful, and thriving.",
                            'why_am' => "ምርጥ የጤና ውጤት ({$wellnessScore}/100) ስላስመዘገቡ፣ ሁልጊዜ ደስተኛ፣ ንቁ እና ብሩህ አእምሮ እንዲኖርዎት ተመርጧል።"
                        ];
                    }
                }
            }
        }

        // Fallback to Balanced Wellness mix if empty
        if ($matched->isEmpty()) {
            $balanced = \App\Models\ResortRecommendation::whereIn('wellness_category', ['Stress', 'Mood', 'Sleep'])->get();
            $matched = $matched->merge($balanced);
            foreach ($balanced as $act) {
                if (!isset($reasons[$act->id])) {
                    $reasons[$act->id] = $fallbackReasons[$act->wellness_category] ?? [
                        'why' => "Recommended to support your overall wellness balance and maintain healthy body-mind alignment.",
                        'why_am' => "አጠቃላይ የአካልና አእምሮ ጤናዎን ለማገዝና ጤናማ ሚዛንን ለመጠበቅ ተመርጧል።"
                    ];
                }
            }
        }

        // De-duplicate matched list by wellness_category to ensure unique categories
        $matched = $matched->unique('wellness_category')->values();

        // Enforce Limits (Min 2, Max 4)
        if ($matched->count() > 4) {
            $matched = $matched->take(4);
        } elseif ($matched->count() < 2) {
            $matchedCategories = $matched->pluck('wellness_category')->toArray();
            $fillers = \App\Models\ResortRecommendation::whereNotIn('wellness_category', $matchedCategories)
                ->get()
                ->unique('wellness_category');
            $needed = 2 - $matched->count();
            $selectedFillers = $fillers->take($needed);
            foreach ($selectedFillers as $act) {
                if (!isset($reasons[$act->id])) {
                    $reasons[$act->id] = $fallbackReasons[$act->wellness_category] ?? [
                        'why' => "Recommended to support your overall wellness balance.",
                        'why_am' => "አጠቃላይ የአካልና አእምሮ ጤናዎን ለማገዝ ተመርጧል።"
                    ];
                }
            }
            $matched = $matched->merge($selectedFillers);
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

        $assessmentArray = $assessment->toArray();
        $assessmentArray['nickname'] = $user->nickname;
        $assessmentArray['recommended_resorts'] = $recommendations;

        return response()->json($assessmentArray, 217); // 217 created status
    }
}
