<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AiCoachController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:2000',
            'lang' => 'nullable|string|in:en,am',
            'nickname' => 'nullable|string|max:191',
        ]);

        $question = $request->question;
        $nickname = trim($request->input('nickname', 'Guest'));
        $user = null;
        $latestAssessment = null;

        if (!empty($nickname)) {
            $user = \App\Models\User::firstOrCreate(['nickname' => $nickname]);
            $latestAssessment = \App\Models\WellnessAssessment::where('user_id', $user->id)
                ->latest()
                ->first();
        }

        $userContext = "";
        $promptContext = "";
        if ($latestAssessment) {
            $stressStr = ($latestAssessment->stress_level >= 7 ? 'High' : ($latestAssessment->stress_level <= 3 ? 'Low' : 'Medium'));
            $waterStr = ($latestAssessment->water_intake < 2.0 ? 'Low' : 'Good');

            $userContext = "Context about the user: Nickname is '{$nickname}'. They recently scored a {$latestAssessment->wellness_score}/100 on their wellness assessment. Their metrics are: Stress level: {$latestAssessment->stress_level}/10, Sleep duration: {$latestAssessment->sleep_hours} hours/night, Hydration: {$latestAssessment->water_intake} L/day, Physical Activity: {$latestAssessment->activity_level}, Mood indicator: {$latestAssessment->mood_level}. ";

            $promptContext .= "User Wellness Profile:\n" .
                              "- Stress Level: {$stressStr} ({$latestAssessment->stress_level}/10)\n" .
                              "- Sleep Hours: {$latestAssessment->sleep_hours} hours/night\n" .
                              "- Water Intake: {$waterStr} ({$latestAssessment->water_intake} L/day)\n" .
                              "- Activity Level: {$latestAssessment->activity_level}\n" .
                              "- Wellness Score: {$latestAssessment->wellness_score}/100\n\n";
        }

        $localContext = $this->getLocalFilesContext($question);
        if (!empty($localContext)) {
            $promptContext .= $localContext . "\n";
        }

        $promptContext .= "User Message:\n\"{$question}\"";

        $langContext = "";
        if ($request->lang === 'am') {
            $langContext = "IMPORTANT: The user has selected Amharic as their preferred language. You MUST write your response entirely in Amharic (using Ge'ez script). ";
        } elseif ($request->lang === 'en') {
            $langContext = "IMPORTANT: The user has selected English as their preferred language. You MUST write your response entirely in English. ";
        }

        $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
        $modelName = config('services.gemini.model', 'gemini-2.5-flash');
        $aiResponse = "";
        $apiError = "";

        if ($apiKey) {
            try {
                $systemPrompt = "You are the WellPilot AI Coach, an empathetic and highly knowledgeable digital wellness assistant. Always follow these rules strictly:\n" .
                                "{$langContext}" .
                                "1. Always reply in the same language as the user's message. If the user writes in Amharic, reply in Amharic. If the user writes in English, reply in English. If they mix, use their primary language.\n" .
                                "2. Keep responses short, practical, and focused. Maximum 2-4 sentences unless the user explicitly asks for more details. Do not give long explanations.\n" .
                                "3. Answer only what the user asks. Do not add unrelated information. Do not lecture or provide lengthy background information.\n" .
                                "4. You are a general AI assistant, meaning you can answer questions about any topic. However, maintain a supportive, professional, and friendly tone, and prioritize wellness contexts if relevant.\n" .
                                "5. If a User Wellness Profile is provided, you MUST customize and personalize your wellness recommendations based on those metrics, explicitly referencing values (like sleep hours or hydration) to explain how their habits relate to their concern. (For example, 'Based on your recent assessment, your low sleep and hydration levels may be contributing to your fatigue. Try...'). If no profile is provided, fall back to general guidance.\n" .
                                "6. If the user greets you, respond briefly and politely.\n" .
                                "7. Never generate harmful, unsafe, or illegal advice.\n" .
                                "8. Maintain a supportive, professional, and friendly tone.\n" .
                                "Response Rules:\n" .
                                "* Do not introduce yourself unless the user explicitly asks who you are.\n" .
                                "* Do not start responses with phrases such as \"Welcome to WellPilot AI Coach\", \"Hello, I am WellPilot AI Coach\", or \"Thank you for using WellPilot\".\n" .
                                "* Answer the user's question directly and focus only on the user's request.\n" .
                                "* Keep responses concise and practical.\n" .
                                "* Do not repeat greetings after the first interaction.\n" .
                                "* Do not include unnecessary introductions, conclusions, or promotional text.\n" .
                                "* Provide wellness-focused suggestions immediately.\n\n" .
                                "Amharic Communication Rules:\n" .
                                "* When responding in Amharic, use natural, fluent, and modern Ethiopian Amharic.\n" .
                                "* Respond as a native Amharic speaker would in everyday conversation.\n" .
                                "* Avoid literal word-for-word translations from English.\n" .
                                "* Use clear, simple, and culturally appropriate Amharic.\n" .
                                "* Prefer common Ethiopian expressions over formal or machine-translated language.\n" .
                                "* Keep responses concise, helpful, and easy to understand.\n" .
                                "* Maintain the same wellness-focused tone in both Amharic and English.\n" .
                                "* If the user speaks casually, respond casually and naturally.\n" .
                                "* If the user speaks formally, respond formally and respectfully.\n\n" .
                                "Examples:\n" .
                                "User: \"I am tired.\"\n" .
                                "Good Response: \"Try drinking water, taking a short break, and getting enough sleep tonight.\"\n" .
                                "Bad Response: \"Welcome to WellPilot AI Coach. I am here to help you. Since you are tired...\"\n\n" .
                                "User: \"ደክሞኛል\"\n" .
                                "Good Response: \"ትንሽ እረፍት ያድርጉ፣ ውሃ ይጠጡ እና በቂ እንቅልፍ ይውሰዱ።\"\n" .
                                "Bad Response: \"ወደ WellPilot AI Coach እንኳን ደህና መጡ...\"\n\n" .
                                "User: \"ፊቴ ጠቁሯል\"\n" .
                                "Good Response: \"በቂ ውሃ ይጠጡ፣ በቂ እንቅልፍ ይተኙ እና መደበኛ የቆዳ እንክብካቤ ያድርጉ። ቀላል የፊት እንፋሎት መውሰድም ሊረዳ ይችላል።\"\n" .
                                "Bad Response: \"WellPilot AI Coach ነኝ...\"";

                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}", [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => "System Instruction: {$systemPrompt}\n\n{$promptContext}"]
                                ]
                            ]
                        ],
                        'tools' => [
                            [
                                'googleSearch' => (object)[]
                            ]
                        ]
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $aiResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? "";
                } else {
                    $apiError = "Gemini API returned error code " . $response->status();
                    \Illuminate\Support\Facades\Log::warning("Gemini API returned error: " . $response->body());
                }
            } catch (\Exception $e) {
                $apiError = "Gemini API connection error: " . $e->getMessage();
                \Illuminate\Support\Facades\Log::error("Gemini API connection error: " . $e->getMessage());
            }
        }

        // Fallback or if Gemini failed/unconfigured
        if (empty($aiResponse)) {
            $q = mb_strtolower($question, 'UTF-8');
            $isAm = ($request->lang === 'am');

            if ($isAm) {
                if (str_contains($q, 'ሰላም') || str_contains($q, 'ሃሎ') || str_contains($q, 'ሄይ')) {
                    $aiResponse = "ሰላም! እንዴት ልረዳዎት እችላለሁ?";
                } elseif (str_contains($q, 'ማን ነህ') || str_contains($q, 'ማን ነሽ') || str_contains($q, 'ምን ነህ') || str_contains($q, 'ምን ነሽ') || str_contains($q, 'ስም')) {
                    $aiResponse = "እኔ የ WellPilot ኤአይ የጤና አማካሪ ነኝ። ስለ ጭንቀት ቅነሳ፣ እንቅልፍ፣ የውሃ አወሳሰድ እና የአካል ብቃት እንቅስቃሴ ልረዳዎት እችላለሁ።";
                } elseif (str_contains($q, 'ደህና ነህ') || str_contains($q, 'እንዴት ነህ') || str_contains($q, 'ደህና ነሽ') || str_contains($q, 'እንዴት ነሽ') || str_contains($q, 'ደህና ናችሁ') || str_contains($q, 'እንዴት ነው')) {
                    $aiResponse = "እግዚአብሔር ይመስገን፣ እኔ በጣም ደህና ነኝ! የእርስዎ ቀን እንዴት እየሄደ ነው?";
                } elseif (str_contains($q, 'ሀሳብ') || str_contains($q, 'አስባለሁ') || str_contains($q, 'አእምሮዬ')) {
                    $aiResponse = "ምን እያሰቡ ነው? እኔ ለማዳመጥ እና ለመርዳት እዚህ ነኝ።";
                } elseif (str_contains($q, 'ጭንቀት') || str_contains($q, 'ድካም') || str_contains($q, 'ሰለቸኝ') || str_contains($q, 'ውጥረት') || str_contains($q, 'ፍርሃት') || str_contains($q, 'ደከመኝ')) {
                    if ($latestAssessment) {
                        $stressStr = ($latestAssessment->stress_level >= 7 ? 'ከፍተኛ' : ($latestAssessment->stress_level <= 3 ? 'አነስተኛ' : 'መካከለኛ'));
                        $aiResponse = "በቅርብ ግምገማዎ መሠረት፣ የጭንቀትዎ መጠን {$stressStr} በመሆኑ ድካም እንዲሰማዎት አስተዋጽኦ አድርጎ ሊሆን ይችላል። እባክዎን ትንሽ እረፍት ያድርጉ፣ ስልክዎን ዘግተው ለ15 ደቂቃ በቀስታ ይራመዱ ወይም ዛሬ ማታ ሞቅ ባለ ውሃ ይታጠቡ።";
                    } else {
                        $aiResponse = "ትንሽ እረፍት ያድርጉ፣ ስልክዎን ዘግተው ለ15 ደቂቃ ያህል በዝግታ ይራመዱ፣ ወይም ዛሬ ማታ ሞቅ ባለ ውሃ መታጠቢያ በመውሰድ ሰውነትዎን ያረጋጉ።";
                    }
                } elseif (str_contains($q, 'እንቅልፍ') || str_contains($q, 'መኝታ') || str_contains($q, 'ህልም') || str_contains($q, 'ሌሊት') || str_contains($q, 'ማረፍ')) {
                    if ($latestAssessment) {
                        $aiResponse = "በቅርብ ግምገማዎ መሠረት፣ አማካይ የእንቅልፍ ሰዓትዎ {$latestAssessment->sleep_hours} ሰዓት በመሆኑ በቂ እንቅልፍ እንዳያገኙ አድርጎዎታል። ከመተኛትዎ 45 ደቂቃ በፊት ስልክዎን ያጥፉ እና ሙቅ ውሃ ይታጠቡ።";
                    } else {
                        $aiResponse = "እንቅልፍዎን ለማስተካከል ከመተኛትዎ 45 ደቂቃ በፊት ስልክዎን ያጥፉ። በተጨማሪም ከመተኛትዎ በፊት ለብ ባለ ውሃ መታጠብ ወይም ረጋ ያሉ ድምፆችን ማዳመጥ ጥሩ እንቅልፍ እንዲያገኙ ይረዳል።";
                    }
                } elseif (str_contains($q, 'ውሃ') || str_contains($q, 'ፈሳሽ') || str_contains($q, 'መጠጣት')) {
                    if ($latestAssessment) {
                        $aiResponse = "በቅርብ ግምገማዎ መሠረት፣ የውሃ አወሳሰድዎ {$latestAssessment->water_intake} ሊትር በመሆኑ ሰውነትዎ በቂ ፈሳሽ አላገኘም። በሚሰሩበት ቦታ የውሃ ጠርሙስ ያስቀምጡ እና ጠዋት እንደነቁ ውሃ ይጠጡ።";
                    } else {
                        $aiResponse = "በሚሰሩበት ቦታ የውሃ ጠርሙስ በማስቀመጥ ቀኑን ሙሉ ውሃ መጠጣትዎን ያረጋግጡ። በተጨማሪም ጠዋት እንደነቁ አንድ ብርጭቆ ለብ ያለ ውሃ መጠጣት ይመረጣል።";
                    }
                } elseif (str_contains($q, 'ስፖርት') || str_contains($q, 'ሩጫ') || str_contains($q, 'እንቅስቃሴ') || str_contains($q, 'መራመድ') || str_contains($q, 'ሰነፍ') || str_contains($q, 'ጂም')) {
                    if ($latestAssessment) {
                        $aiResponse = "በቅርብ ግምገማዎ መሠረት፣ የአካል እንቅስቃሴ ደረጃዎ {$latestAssessment->activity_level} በመሆኑ በየሰዓቱ ለ3 ደቂቃ ያህል በመቆም መለጠጥ ወይም በቀን ለ20 ደቂቃ በተፈጥሮ ውስጥ መራመድ ይመረጣል።";
                    } else {
                        $aiResponse = "አካላዊ እንቅስቃሴዎን ለማሻሻል በየሰዓቱ ለ3 ደቂቃ ያህል በመቆም ይለጠጡ። እንዲሁም የጠዋት ዮጋን መሞከር ወይም በቀን ለ20 ደቂቃ ያህል በተፈጥሮ ውስጥ መራመድ ሰውነትዎን ያነቃቃዋል።";
                    }
                } else {
                    $aiResponse = "ስለ ጭንቀት ቅነሳ፣ ጥልቅ እንቅልፍ፣ የአካል ብቃት እንቅስቃሴ እና አመጋገብ ልረዳዎ እችላለሁ። ዛሬ ምን ዓይነት የጤና ክፍል ማሻሻል እንደሚፈልጉ ይንገሩኝ።";
                }
            } else {
                if (preg_match('/\b(hi|hello|hey|greetings|howdy|yo)\b/i', $q)) {
                    $aiResponse = "Hi! How can I help you?";
                } elseif (preg_match('/\b(who are you|your name|what is your name)\b/i', $q)) {
                    $aiResponse = "I am the WellPilot AI Coach, your empathetic digital wellness assistant. I can guide you on stress reduction, sleep quality, hydration, and exercise.";
                } elseif (preg_match('/\b(are (you|u) fine|how are (you|u)|are (you|u) ok|how is it going|how are (you|u) doing)\b/i', $q)) {
                    $aiResponse = "I am doing great, thank you! How are you doing today?";
                } elseif (str_contains($q, 'on my mind') || str_contains($q, 'something on my mind') || str_contains($q, 'thinking about')) {
                    $aiResponse = "What's on your mind? I'm here to listen and help.";
                } elseif (str_contains($q, 'stress') || str_contains($q, 'anxious') || str_contains($q, 'burnout') || str_contains($q, 'tired') || str_contains($q, 'exhausted') || str_contains($q, 'pressure') || str_contains($q, 'overwhelm')) {
                    if ($latestAssessment) {
                        $stressStr = ($latestAssessment->stress_level >= 7 ? 'High' : ($latestAssessment->stress_level <= 3 ? 'Low' : 'Medium'));
                        $aiResponse = "Based on your recent assessment, your stress level is {$stressStr}. Try box breathing (inhale, hold, exhale, hold for 4 seconds each), take a 15-minute screen-free walk in nature, or schedule 30 minutes tonight for a hot mineral bath to calm your nervous system.";
                    } else {
                        $aiResponse = "It sounds like you are experiencing stress or exhaustion. Try box breathing (inhale, hold, exhale, hold for 4 seconds each), take a 15-minute screen-free walk in nature, or schedule 30 minutes tonight for a hot mineral bath to calm your nervous system.";
                    }
                } elseif (str_contains($q, 'sleep') || str_contains($q, 'insomnia') || str_contains($q, 'wake up') || str_contains($q, 'night') || str_contains($q, 'dream')) {
                    if ($latestAssessment) {
                        $aiResponse = "Based on your recent assessment showing {$latestAssessment->sleep_hours} hours of sleep, you may be experiencing sleep deficits. Try turning off all screens 45 minutes before bed and taking a warm shower to signal sleep onset.";
                    } else {
                        $aiResponse = "To improve your sleep quality, try turning off all screens 45 minutes before bed. You can also take a warm shower to signal sleep onset or listen to soft ambient sounds to help transition your brain wave state.";
                    }
                } elseif (str_contains($q, 'water') || str_contains($q, 'hydration') || str_contains($q, 'drink') || str_contains($q, 'dehydrated')) {
                    if ($latestAssessment) {
                        $aiResponse = "Based on your recent assessment, your daily water intake is low at {$latestAssessment->water_intake} L/day. Try keeping a large bottle on your desk, and drink a warm glass of water immediately upon waking to improve hydration.";
                    } else {
                        $aiResponse = "Ensure you are sipping water throughout the day by keeping a large bottle on your desk. You can add slices of lemon or mint for flavor, and drink a warm glass of water immediately upon waking.";
                    }
                } elseif (str_contains($q, 'exercise') || str_contains($q, 'workout') || str_contains($q, 'active') || str_contains($q, 'walk') || str_contains($q, 'lazy') || str_contains($q, 'fit')) {
                    if ($latestAssessment) {
                        $aiResponse = "Based on your recent assessment, your activity level is {$latestAssessment->activity_level}. Stand up and stretch for 3-5 minutes every hour if you sit at a desk, or enjoy a 20-minute daily walk.";
                    } else {
                        $aiResponse = "Keep your body active with low-impact habits rather than exhausting gym sessions. Stand up and stretch for 3-5 minutes every hour if you sit at a desk, or enjoy a 20-minute daily walk.";
                    }
                } else {
                    $aiResponse = "I can help you with stress reduction, deep sleep rituals, mindful movement, and cellular hydration. Let me know what specific area you would like to explore or improve today.";
                }
            }
        }

        // Save the conversation to the database
        \App\Models\AiConversation::create([
            'user_id' => $user ? $user->id : null,
            'question' => $question,
            'response' => $aiResponse,
        ]);

        return response()->json([
            'question' => $question,
            'response' => $aiResponse,
            'offline' => empty($apiKey) || !empty($apiError),
            'api_error' => $apiError,
        ]);
    }

    private function getLocalFilesContext($question)
    {
        $q = strtolower($question);
        $keywords = ['file', 'code', 'project', 'folder', 'controller', 'route', 'model', 'view', 'vue', 'php', 'style', 'doc.md', 'erteale', 'local', 'page', 'theme', 'dark', 'light', 'amharic', 'english', 'system'];

        $shouldFetch = false;
        foreach ($keywords as $keyword) {
            if (str_contains($q, $keyword)) {
                $shouldFetch = true;
                break;
            }
        }

        if (!$shouldFetch) {
            return "";
        }

        $basePath = base_path('..');
        $context = "Local Project Workspace Structure:\n";

        $files = [];
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($basePath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            $fileCount = 0;
            foreach ($iterator as $path => $dir) {
                $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $path);
                // Standard exclusions
                if (str_contains($relativePath, 'vendor') ||
                    str_contains($relativePath, 'node_modules') ||
                    str_contains($relativePath, '.git') ||
                    str_contains($relativePath, 'storage') ||
                    str_contains($relativePath, 'dist') ||
                    str_contains($relativePath, 'bootstrap')) {
                    continue;
                }

                if ($dir->isFile()) {
                    $files[] = $relativePath;
                    $fileCount++;
                    if ($fileCount > 50) break;
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to scan project structure: " . $e->getMessage());
        }

        $context .= implode("\n", $files) . "\n\n";

        // Find matching file
        $matchedFile = null;
        foreach ($files as $file) {
            $baseName = basename($file);
            if (str_contains($q, strtolower($baseName))) {
                $matchedFile = $file;
                break;
            }
        }

        if ($matchedFile) {
            $fullPath = $basePath . DIRECTORY_SEPARATOR . $matchedFile;
            if (file_exists($fullPath) && is_file($fullPath) && filesize($fullPath) < 50000) {
                $content = file_get_contents($fullPath);
                $context .= "Content of Matched Local File ({$matchedFile}):\n" .
                            "```\n" . substr($content, 0, 3000) . "\n```\n\n";
            }
        } else {
            $docPath = $basePath . DIRECTORY_SEPARATOR . 'doc.md';
            if (file_exists($docPath)) {
                $docContent = file_get_contents($docPath);
                $context .= "Content of doc.md:\n" .
                            "```\n" . substr($docContent, 0, 3000) . "\n```\n\n";
            }
        }

        return $context;
    }

    public function status()
    {
        $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
        return response()->json([
            'configured' => !empty($apiKey)
        ]);
    }

    public function speak(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:2000',
            'lang' => 'nullable|string|in:am,en',
        ]);

        $text = $request->text;
        $lang = $request->lang ?? 'am';

        // 1. Clean up developer offline warning notice if present
        $text = preg_replace('/\*\(Note:.*?\)\*/is', '', $text);
        $text = preg_replace('/\*\(ማሳሰቢያ[:፡].*?\)\*/is', '', $text);

        // 2. Remove emojis and miscellaneous symbols
        $text = preg_replace('/[\x{1F300}-\x{1F9FF}]/u', '', $text);
        $text = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $text);
        $text = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $text);
        $text = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $text);
        $text = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $text);

        // 3. Clean up text by removing asterisks (bold/italic markdown indicators), newlines, list dashes and extra spaces
        $text = str_replace(['**', '*', "\r", "\n"], [' ', ' ', ' ', ' '], $text);
        $text = preg_replace('/^\s*-\s+/m', ' ', $text); // remove bullet list hyphens
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim($text);

        if (empty($text)) {
            return response("Empty text", 400);
        }

        // Split text into chunks of maximum 150 characters to avoid hitting Google Translate TTS limits.
        // We split on word boundaries to preserve pronunciation.
        $chunks = [];
        $words = explode(' ', $text);
        $currentChunk = '';

        foreach ($words as $word) {
            if (mb_strlen($currentChunk . ' ' . $word) <= 150) {
                $currentChunk = empty($currentChunk) ? $word : $currentChunk . ' ' . $word;
            } else {
                if (!empty($currentChunk)) {
                    $chunks[] = $currentChunk;
                }
                $currentChunk = $word;
            }
        }
        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        // Fetch TTS audio chunks from Google Translate and concatenate the binary byte streams.
        // MP3 frames are independent, allowing seamless raw concatenation.
        $combinedAudio = "";

        try {
            foreach ($chunks as $chunk) {
                if (empty($chunk)) continue;

                $url = "https://translate.google.com/translate_tts?ie=UTF-8&tl=" . $lang . "&client=tw-ob&q=" . urlencode($chunk);

                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.0.0 Safari/537.36',
                    ])->get($url);

                if ($response->successful()) {
                    $combinedAudio .= $response->body();
                } else {
                    \Illuminate\Support\Facades\Log::warning("TTS chunk generation failed for: " . $chunk);
                }
            }

            if (!empty($combinedAudio)) {
                return response($combinedAudio, 200)
                    ->header('Content-Type', 'audio/mpeg')
                    ->header('Content-Disposition', 'inline; filename="tts.mp3"');
            } else {
                return response("TTS Generation Failed", 500);
            }
        } catch (\Exception $e) {
            return response("TTS Server Error: " . $e->getMessage(), 500);
        }
    }
}

