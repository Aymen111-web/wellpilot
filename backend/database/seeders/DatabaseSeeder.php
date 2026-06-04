<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Wellness Challenges
        \App\Models\WellnessChallenge::create([
            'challenge_name' => '7-Day Hydration Challenge',
            'challenge_name_am' => 'የ7-ቀን የውሃ መጠጣት ተግዳሮት',
            'description' => 'Drink at least 3 liters of pure water every day to boost energy levels, clear skin, and improve digestion. Log your daily consumption to track progress.',
            'description_am' => 'ጉልበትን ለመጨመር፣ ቆዳን ለማፅዳትና የምግብ መፈጨትን ለማሻሻል በየቀኑ ቢያንስ 3 ሊትር ንጹህ ውሃ ይጠጡ። እድገትዎን ለመከታተል ዕለታዊ ፍጆታዎን ይመዝግቡ።',
            'duration_days' => 7,
            'reward_points' => 150,
            'category' => 'Hydration',
        ]);

        \App\Models\WellnessChallenge::create([
            'challenge_name' => 'Better Sleep Challenge',
            'challenge_name_am' => 'የተሻለ እንቅልፍ ተግዳሮት',
            'description' => 'Aim for 8 hours of uninterrupted sleep each night. Establish a calming, screen-free pre-sleep wind-down routine 30 minutes before bed.',
            'description_am' => 'በየሌሊቱ ለ8 ሰዓታት ያህል ያለማቋረጥ ለመተኛት ይሞክሩ። ከመተኛትዎ 30 ደቂቃ በፊት ስልክና ሌሎች ስክሪኖችን በማጥፋት አእምሮን የሚያረጋጋ ልማድ ይፍጠሩ።',
            'duration_days' => 7,
            'reward_points' => 200,
            'category' => 'Sleep',
        ]);

        \App\Models\WellnessChallenge::create([
            'challenge_name' => 'Daily Walking Challenge',
            'challenge_name_am' => 'የዕለታዊ የእግር ጉዞ ተግዳሮት',
            'description' => 'Walk 10,000 steps daily. Enjoy the fresh air, boost your metabolic rate, and improve cardiovascular health through a simple, effective walking habit.',
            'description_am' => 'በየቀኑ 10,000 እርምጃዎችን ይራመዱ። ንጹህ አየር በመተንፈስ፣ የሰውነትዎን ሜታቦሊዝም መጠን በመጨመር እና የልብና የደም ዝውውር ጤናዎን ያሻሽሉ።',
            'duration_days' => 5,
            'reward_points' => 100,
            'category' => 'Physical Activity',
        ]);

        \App\Models\WellnessChallenge::create([
            'challenge_name' => 'Stress-Free Week Challenge',
            'challenge_name_am' => 'ከጭንቀት ነፃ የሳምንት ተግዳሮት',
            'description' => 'Dedicate 10 minutes every day to mindful meditation or deep breathing exercises. Perfect for lowering cortisol levels and centering your mind.',
            'description_am' => 'በየቀኑ ለ10 ደቂቃ ያህል ትኩረት ሰጥተው አእምሮዎን የሚያረጋጉ ወይም በጥልቀት የመተንፈስ ልምምዶችን ያድርጉ። የጭንቀት ሆርሞንን ለመቀነስ ፍጹም መንገድ ነው።',
            'duration_days' => 7,
            'reward_points' => 250,
            'category' => 'Mental Wellness',
        ]);

        \App\Models\WellnessChallenge::create([
            'challenge_name' => 'Balanced Nutrition Challenge',
            'challenge_name_am' => 'የተመጣጠነ ምግብ ተግዳሮት',
            'description' => 'Ensure your meals include a healthy balance of fresh greens, lean protein, and complex carbohydrates to nourish your body.',
            'description_am' => 'ሰውነትዎን ለመመገብ ምግብዎ ትኩስ አትክልቶች፣ ፕሮቲን እና ካርቦሃይድሬት የተመጣጠነ ሚዛን እንዲኖረው ያድርጉ::',
            'duration_days' => 7,
            'reward_points' => 150,
            'category' => 'Nutrition',
        ]);

        \App\Models\WellnessChallenge::create([
            'challenge_name' => 'Mindful Digital Detox',
            'challenge_name_am' => 'የአእምሮ እረፍት እና ዲጂታል ዲቶክስ',
            'description' => 'Turn off all digital screens at least 1 hour before sleeping to calm your mind and improve sleep quality.',
            'description_am' => 'አእምሮዎን ለማረጋጋት እና የእንቅልፍ ጥራትን ለማሻሻል ከመተኛትዎ ቢያንስ 1 ሰዓት በፊት ሁሉንም ዲጂታል ስክሪኖች ያጥፉ::',
            'duration_days' => 5,
            'reward_points' => 120,
            'category' => 'Self-Care',
        ]);

        // Seed Resort Recommendations
        // Stress Category
        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Stress',
            'activity_name' => 'Deep Tissue Spa & Massage Therapy',
            'activity_name_am' => 'ጥልቅ የአካል ማሸት እና ስፓ ቴራፒ',
            'description' => 'A luxurious 90-minute treatment at our Serene Oasis Spa, utilizing aromatic essential oils and deep pressure techniques to melt away muscular tension and lower cortisol levels.',
            'description_am' => 'በእኛ ሰሪን ኦአሲስ ስፓ (Serene Oasis Spa) ውስጥ የሚሰጥ የ90 ደቂቃ የቅንጦት ህክምና። ጥሩ መዓዛ ያላቸው ዘይቶችንና ጥልቅ የመጫን ዘዴዎችን በመጠቀም የአጥንትና የጡንቻ ውጥረትን ያስወግዳል፣ የጭንቀት መጠንንም ይቀንሳል።',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Stress',
            'activity_name' => 'Guided Forest Mindful Meditation',
            'activity_name_am' => 'በጫካ ውስጥ የሚመራ የአእምሮ ማረጋጊያ አስተነፍሶ',
            'description' => 'A peaceful, meditative walk through our resort\'s private pine forest sanctuary led by a resident mindfulness expert. Reconnect with nature and quiet your busy mind.',
            'description_am' => 'በሪዞርቱ የግል ጥድ ጫካ ውስጥ በባለሙያ የሚመራ ሰላማዊ የእግር ጉዞ እና ማሰላሰል። ከተፈጥሮ ጋር እንደገና ይገናኙ እና የተጨናነቀ አእምሮዎን ያረጋጉ።',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Stress',
            'activity_name' => 'Thermal Mineral Hot Springs Soak',
            'activity_name_am' => 'በማዕድን የበለፀገ ፍልውሃ ውስጥ መዘፈቅ',
            'description' => 'Immerse yourself in our geothermal pools enriched with magnesium and sulfur, known to naturally relieve anxiety, calm the nervous system, and soothe tired muscles.',
            'description_am' => 'በማግኒዚየም እና በሰልፈር የበለፀጉ የፍልውሃ ገንዳዎቻችን ውስጥ ይዘፈቁ። ጭንቀትን በራስ-ሰር ለማቃለል፣ የነርቭ ስርዓትን ለማረጋጋት እና የደከሙ ጡንቻዎችን ለማስታገስ እንደሚረዳ የታወቀ ነው።',
        ]);

        // Physical Activity Category
        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Physical Activity',
            'activity_name' => 'Sunrise Vinyasa Beach Yoga',
            'activity_name_am' => 'የማለዳ ቪንያሳ የባህር ዳርቻ ዮጋ',
            'description' => 'An energizing, fluid yoga flow on our quiet sandy beach deck as the sun rises. Perfect for improving flexibility, balance, and core strength while listening to the soothing ocean waves.',
            'description_am' => 'ፀሐይ ስትወጣ በባህር ዳርቻው ሰሌዳ ላይ የሚደረግ የሰውነት መለማመጃ ዮጋ። የሰውነት የመተጣጠፍ ችሎታን፣ ሚዛንን እና ጥንካሬን ለማሻሻል እንዲሁም የባህር ሞገድ ድምፅን ለማዳመጥ የሚረዳ።',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Physical Activity',
            'activity_name' => 'Sunset Lagoon Kayaking Tour',
            'activity_name_am' => 'የፀሐይ መጥለቂያ ካያኪንግ ጉብኝት',
            'description' => 'A guided scenic paddling session through the tranquil resort lagoon. A fantastic upper-body workout that lets you experience local wildlife and a breathtaking sunset.',
            'description_am' => 'በሪዞርቱ ፀጥተኛ የባህር ወሽመጥ (lagoon) ውስጥ የሚደረግ የሚመራ የካያክ ቀዘፋ። የላይኛውን የሰውነት ክፍል ለማጠናከር እና አስደናቂውን የፀሐይ መጥለቅ እያዩ አካባቢውን ለመጎብኘት የሚረዳ።',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Physical Activity',
            'activity_name' => 'Aqua Aerobics & Core Program',
            'activity_name_am' => 'የውሃ ኤሮቢክስ እና የኮር ፕሮግራም',
            'description' => 'A high-energy, low-impact full-body workout in our heated infinity pool. Utilizes water resistance to challenge your muscles while remaining gentle on your joints.',
            'description_am' => 'በሞቀ የውሃ ገንዳችን ውስጥ የሚደረግ ከፍተኛ ጉልበት የሚጠይቅ የአካል ብቃት እንቅስቃሴ። ለመገጣጠሚያዎችዎ ምቹ በሆነ ሁኔታ የውሃ መቋቋምን በመጠቀም ጡንቻዎችዎን ያጠናክራል።',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Physical Activity',
            'activity_name' => 'State-of-the-Art Gym & Strength Session',
            'activity_name_am' => 'ዘመናዊ ጂም እና የጥንካሬ ስልጠና',
            'description' => 'Access our high-end fitness center equipped with advanced cardio machines, free weights, and personal coaching to design your custom workout.',
            'description_am' => 'የካርዲዮ ማሽኖች፣ የክብደት ማንሻዎች እና የግል አሰልጣኝ የተሟላለትን ዘመናዊ የጂም ማዕከላችንን በመጠቀም የራስዎን የስፖርት እንቅስቃሴ ያድርጉ።',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Physical Activity',
            'activity_name' => 'High-Energy Cardio Aerobics Class',
            'activity_name_am' => 'ከፍተኛ ጉልበት የሚጠይቅ የካርዲዮ ኤሮቢክስ',
            'description' => 'Join an upbeat group aerobics class designed to elevate your heart rate, boost cardiovascular health, and burn calories through rhythmic movements.',
            'description_am' => 'የልብ ምትን የሚጨምር፣ የካርዲዮቫስኩላር ጤናን የሚያሻሽል እና የሰውነት ስብን ለመቀነስ የሚረዳ በቡድን የሚሰራ የካርዲዮ ኤሮቢክስ ስልጠና።',
        ]);

        // Sleep Category
        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Sleep',
            'activity_name' => 'Acoustic Sound Bath & Sound Therapy',
            'activity_name_am' => 'የድምፅ መታጠቢያ እና የድምፅ ቴራፒ',
            'description' => 'Relax on plush custom mats while therapeutic sound waves from quartz crystal bowls and gongs wash over you. Designed to guide your brainwaves into a deep, restorative delta state.',
            'description_am' => 'ከክሪስታል ጎድጓዳ ሳህኖች እና ከጎንግ የሚወጡ የድምፅ ሞገዶች በእርስዎ ላይ ሲያልፉ ምቹ በሆኑ ምንጣፎች ላይ ዘና ይበሉ። አእምሮዎን ወደ ጥልቅ የእንቅልፍ ደረጃ ለመምራት የተነደፈ ነው።',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Sleep',
            'activity_name' => 'Aromatherapy Deep Sleep Ritual',
            'activity_name_am' => 'የጥልቅ እንቅልፍ የአሮማቴራፒ ልማድ',
            'description' => 'An evening in-room wellness treatment featuring a warm chamomile-infused foot soak, followed by a light scalp and shoulder massage using lavender and sandalwood essential oils.',
            'description_am' => 'የሞቀ የሻሞሜል እግር መታጠቢያን የሚያካትት የምሽት ህክምና። በመቀጠልም የራስ ቆዳ እና የትከሻ ማሳጅ ከላቬንደር እና ከሰንደልዉድ ዘይቶች ጋር ይደረጋል።',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Sleep',
            'activity_name' => 'Starry Night Herbal Wind-Down',
            'activity_name_am' => 'የኮከብ ምሽት ከዕፅዋት የተቀመመ ሻይ መዝናኛ',
            'description' => 'Enjoy customized herbal tea blends under the night sky at our celestial observatory deck, accompanied by soft instrumental acoustic music to prime your body for sleep.',
            'description_am' => 'በእፅዋት የተቀመሙ የሻይ ውህዶችን ከሰማይ ኮከቦች በታች ባለው የመመልከቻ ሰሌዳ ላይ ይደሰቱ። ሰውነትዎን ለእረፍት ዝግጁ የሚያደርግ ጸጥተኛ የአኮስቲክ ሙዚቃ ታጅቧል።',
        ]);

        // Mood Category
        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Mood',
            'activity_name' => 'Forest Bathing (Shinrin-Yoku)',
            'activity_name_am' => 'የጫካ መታጠቢያ (ሺንሪን-ዮኩ)',
            'description' => 'A Japanese wellness practice involving slow, sensory immersion in the woods. Scientifically proven to elevate mood, boost natural killer cell activity, and reduce blood pressure.',
            'description_am' => 'በጫካ ውስጥ ዘገምተኛ የዳሰሳ ስሜትን የሚያካትት የጃፓን የጤና ልምምድ። ስሜትን ለማሻሻል፣ የደም ግፊትን ለመቀነስ እና አእምሮን ለማረጋጋት በሳይንስ የተረጋገጠ።',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Mood',
            'activity_name' => 'Expressive Art & Clay Therapy',
            'activity_name_am' => 'የስሜት መግለጫ የስዕል እና የሸክላ ቴራፒ',
            'description' => 'Unleash your creativity in our open-air garden studio. Guided by a certified art therapist, this hands-on workshop helps you process emotions and release stress through raw clay.',
            'description_am' => 'በተከፈተ የአትክልት ስፍራ ስቱዲዮ ውስጥ ፈጠራዎን ያውጡ። በስነ-ጥበብ ቴራፒስት የሚመራው ይህ ተግባራዊ ስልጠና ጥሬ ሸክላዎችን በመጠቀም ስሜትን ለመግለጽና ጭንቀትን ለማስወገድ ይረዳል።',
        ]);

        // Hydration / Nutrition Category
        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Hydration',
            'activity_name' => 'Detox Cold-Pressed Juicing Class',
            'activity_name_am' => 'የፍራሽ ጭማቂ አዘገጃጀት ስልጠና',
            'description' => 'Learn the science behind cold-pressed juicing from our clinical nutritionists. Craft and enjoy customized nutrient-dense juices designed to support cellular hydration and detoxification.',
            'description_am' => 'ከስነ-ምግብ ባለሙያዎቻችን የተፈጥሮ ፍራሽ ጭማቂዎችን የመጭመቅ ሳይንስን ይማሩ። የሰውነትዎን ፈሳሽ መጠን የሚጨምሩ እና መርዛማ ነገሮችን የሚያስወግዱ የራስዎን ጭማቂዎች ያዘጋጁ።',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Hydration',
            'activity_name' => 'Infused Electrolyte Wellness Lounge',
            'activity_name_am' => 'በኤሌክትሮላይት የበለፀገ የጤና እረፍት ክፍል',
            'description' => 'Relax in our luxurious wellness lounge offering unlimited access to premium mineral and fruit-infused hydration elixirs, curated specifically to restore cellular balance.',
            'description_am' => 'የሰውነትዎን የፈሳሽ መጠን እና የተመጣጠነ ምግብ ሚዛን ለመመለስ በተለየ ሁኔታ በተዘጋጁ የማዕድን እና የፍራፍሬ ውህዶች በሚቀርቡ መጠጦች በሚዝናኑበት የቅንጦት ማረፊያ ክፍል ይደሰቱ።',
        ]);
    }
}
