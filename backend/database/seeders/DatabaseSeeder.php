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
            'description' => 'Drink at least 3 liters of pure water every day to boost energy levels, clear skin, and improve digestion. Log your daily consumption to track progress.',
            'duration_days' => 7,
            'reward_points' => 150,
        ]);

        \App\Models\WellnessChallenge::create([
            'challenge_name' => 'Better Sleep Challenge',
            'description' => 'Aim for 8 hours of uninterrupted sleep each night. Establish a calming, screen-free pre-sleep wind-down routine 30 minutes before bed.',
            'duration_days' => 7,
            'reward_points' => 200,
        ]);

        \App\Models\WellnessChallenge::create([
            'challenge_name' => 'Daily Walking Challenge',
            'description' => 'Walk 10,000 steps daily. Enjoy the fresh air, boost your metabolic rate, and improve cardiovascular health through a simple, effective walking habit.',
            'duration_days' => 5,
            'reward_points' => 100,
        ]);

        \App\Models\WellnessChallenge::create([
            'challenge_name' => 'Stress-Free Week Challenge',
            'description' => 'Dedicate 10 minutes every day to mindful meditation or deep breathing exercises. Perfect for lowering cortisol levels and centering your mind.',
            'duration_days' => 7,
            'reward_points' => 250,
        ]);

        // Seed Resort Recommendations
        // Stress Category
        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Stress',
            'activity_name' => 'Deep Tissue Spa & Massage Therapy',
            'description' => 'A luxurious 90-minute treatment at our Serene Oasis Spa, utilizing aromatic essential oils and deep pressure techniques to melt away muscular tension and lower cortisol levels.',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Stress',
            'activity_name' => 'Guided Forest Mindful Meditation',
            'description' => 'A peaceful, meditative walk through our resort\'s private pine forest sanctuary led by a resident mindfulness expert. Reconnect with nature and quiet your busy mind.',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Stress',
            'activity_name' => 'Thermal Mineral Hot Springs Soak',
            'description' => 'Immerse yourself in our geothermal pools enriched with magnesium and sulfur, known to naturally relieve anxiety, calm the nervous system, and soothe tired muscles.',
        ]);

        // Physical Activity Category
        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Physical Activity',
            'activity_name' => 'Sunrise Vinyasa Beach Yoga',
            'description' => 'An energizing, fluid yoga flow on our quiet sandy beach deck as the sun rises. Perfect for improving flexibility, balance, and core strength while listening to the soothing ocean waves.',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Physical Activity',
            'activity_name' => 'Sunset Lagoon Kayaking Tour',
            'description' => 'A guided scenic paddling session through the tranquil resort lagoon. A fantastic upper-body workout that lets you experience local wildlife and a breathtaking sunset.',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Physical Activity',
            'activity_name' => 'Aqua Aerobics & Core Program',
            'description' => 'A high-energy, low-impact full-body workout in our heated infinity pool. Utilizes water resistance to challenge your muscles while remaining gentle on your joints.',
        ]);

        // Sleep Category
        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Sleep',
            'activity_name' => 'Acoustic Sound Bath & Sound Therapy',
            'description' => 'Relax on plush custom mats while therapeutic sound waves from quartz crystal bowls and gongs wash over you. Designed to guide your brainwaves into a deep, restorative delta state.',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Sleep',
            'activity_name' => 'Aromatherapy Deep Sleep Ritual',
            'description' => 'An evening in-room wellness treatment featuring a warm chamomile-infused foot soak, followed by a light scalp and shoulder massage using lavender and sandalwood essential oils.',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Sleep',
            'activity_name' => 'Starry Night Herbal Wind-Down',
            'description' => 'Enjoy customized herbal tea blends under the night sky at our celestial observatory deck, accompanied by soft instrumental acoustic music to prime your body for sleep.',
        ]);

        // Mood Category
        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Mood',
            'activity_name' => 'Forest Bathing (Shinrin-Yoku)',
            'description' => 'A Japanese wellness practice involving slow, sensory immersion in the woods. Scientifically proven to elevate mood, boost natural killer cell activity, and reduce blood pressure.',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Mood',
            'activity_name' => 'Expressive Art & Clay Therapy',
            'description' => 'Unleash your creativity in our open-air garden studio. Guided by a certified art therapist, this hands-on workshop helps you process emotions and release stress through raw clay.',
        ]);

        // Hydration / Nutrition Category
        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Hydration',
            'activity_name' => 'Detox Cold-Pressed Juicing Class',
            'description' => 'Learn the science behind cold-pressed juicing from our clinical nutritionists. Craft and enjoy customized nutrient-dense juices designed to support cellular hydration and detoxification.',
        ]);

        \App\Models\ResortRecommendation::create([
            'wellness_category' => 'Hydration',
            'activity_name' => 'Infused Electrolyte Wellness Lounge',
            'description' => 'Relax in our luxurious wellness lounge offering unlimited access to premium mineral and fruit-infused hydration elixirs, curated specifically to restore cellular balance.',
        ]);
    }
}
