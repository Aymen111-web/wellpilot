<script setup>
import { ref, onMounted, computed } from 'vue';
import { RouterLink } from 'vue-router';
import { useLanguage } from '../services/translations';
import api from '../services/api';

const { currentLang, t } = useLanguage();

// Analytics states
const nickname = ref(localStorage.getItem('wellpilot_nickname') || 'Guest');
const assessments = ref([]);
const isLoading = ref(true);
const errorMsg = ref('');
const challengeStats = ref({
  total_completed: 0,
  wellness_points: 0,
  streak: 0,
  recent_reflections: []
});

const fetchDashboardData = async () => {
  isLoading.value = true;
  errorMsg.value = '';
  try {
    const [assessmentRes, challengeStatsRes] = await Promise.all([
      api.get('/assessments'),
      api.get(`/challenges/stats?nickname=${encodeURIComponent(nickname.value)}`)
    ]);
    assessments.value = assessmentRes.data;
    challengeStats.value = challengeStatsRes.data;
  } catch (err) {
    console.error('Error fetching dashboard data:', err);
    errorMsg.value = currentLang.value === 'en'
      ? 'Failed to retrieve dashboard trends. Is backend running?'
      : 'የክትትል መረጃዎችን ለማውረድ አልተቻለም። የጀርባ አገልግሎት (backend) እየሰራ መሆኑን ያረጋግጡ::';
  } finally {
    isLoading.value = false;
  }
};

const getCategoryIcon = (cat) => {
  const icons = {
    'Hydration': '🥤',
    'Sleep': '🛌',
    'Physical Activity': '🧘‍♂️',
    'Mental Wellness': '💆‍♀️',
    'Nutrition': '🥗',
    'Self-Care': '🌿'
  };
  return icons[cat] || '✨';
};

const formatReflectionDate = (dateString) => {
  const date = new Date(dateString);
  const today = new Date();
  const yesterday = new Date();
  yesterday.setDate(today.getDate() - 1);

  if (date.toDateString() === today.toDateString()) {
    return currentLang.value === 'en' ? 'Today' : 'ዛሬ';
  } else if (date.toDateString() === yesterday.toDateString()) {
    return currentLang.value === 'en' ? 'Yesterday' : 'ትላንትና';
  } else {
    if (currentLang.value === 'en') {
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    } else {
      return `${date.getMonth() + 1}/${date.getDate()}/${date.getFullYear()}`;
    }
  }
};

onMounted(() => {
  fetchDashboardData();
});

// Latest assessment computed
const latest = computed(() => {
  if (assessments.value.length === 0) return null;
  return assessments.value[assessments.value.length - 1];
});

// SVG Trend line points calculation
const chartPoints = computed(() => {
  if (assessments.value.length < 2) return '';
  const scores = assessments.value.map(a => a.wellness_score);
  
  // Chart dimensions: 500x120
  const width = 500;
  const height = 120;
  const paddingX = 30;
  const paddingY = 15;
  
  const stepX = (width - paddingX * 2) / (scores.length - 1);
  
  return scores.map((score, index) => {
    const x = paddingX + index * stepX;
    // Invert Y coordinate because SVG origin is top-left
    const y = height - paddingY - (score / 100) * (height - paddingY * 2);
    return `${x},${y}`;
  }).join(' ');
});

const getScoreColor = (score) => {
  if (score >= 80) return 'text-emerald-500';
  if (score >= 60) return 'text-teal-500';
  return 'text-rose-500';
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  if (currentLang.value === 'en') {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
  } else {
    // Amharic formatting simple representation
    return `${date.getMonth() + 1}/${date.getDate()} - ${date.getHours()}:${String(date.getMinutes()).padStart(2, '0')}`;
  }
};
</script>

<template>
  <div class="space-y-8 animate-fade-in">
    
    <!-- Title Section -->
    <div class="text-center sm:text-left max-w-4xl mx-auto space-y-2">
      <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white">
        {{ t.dashboard.title }}
      </h1>
      <p class="text-sm text-zinc-500 dark:text-zinc-400">
        {{ t.dashboard.subtitle }}
      </p>
      <div class="w-16 h-1 bg-gradient-to-r from-emerald-500 to-indigo-500 sm:mx-0 mx-auto rounded-full mt-3"></div>
    </div>

    <!-- API Loader -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 space-y-4">
      <div class="w-12 h-12 rounded-full border-4 border-emerald-500/20 border-t-emerald-500 animate-spin"></div>
      <p class="text-sm font-bold text-zinc-400 animate-pulse">Loading dashboard analytics...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMsg" class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200/50 p-6 rounded-2xl text-center max-w-lg mx-auto space-y-4 shadow-xl">
      <span class="text-3xl">⚠️</span>
      <p class="text-sm font-semibold text-rose-600 dark:text-rose-400">{{ errorMsg }}</p>
      <button @click="fetchDashboardData" class="px-4 py-2 bg-rose-500 text-white rounded-xl text-xs font-bold shadow hover:bg-rose-600 transition">
        Try Again
      </button>
    </div>

    <!-- Empty State -->
    <div v-else-if="assessments.length === 0" class="bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 shadow-2xl rounded-3xl p-8 sm:p-12 text-center max-w-xl mx-auto space-y-6 backdrop-blur-md">
      <div class="w-20 h-20 bg-emerald-500/10 rounded-full flex items-center justify-center mx-auto text-emerald-500 border border-emerald-500/20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
        </svg>
      </div>
      <h2 class="text-xl font-bold text-zinc-900 dark:text-white">
        {{ t.dashboard.noDataTitle }}
      </h2>
      <p class="text-sm text-zinc-500 dark:text-zinc-400 max-w-md mx-auto leading-relaxed">
        {{ t.dashboard.noDataDesc }}
      </p>
      <RouterLink to="/assessment" class="inline-block px-6 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-xl shadow-lg transition">
        {{ t.assessment.title }}
      </RouterLink>
    </div>

    <!-- Dashboard Content -->
    <div v-else class="space-y-8">
      
      <!-- Top Metrics Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        
        <!-- Score Card -->
        <div class="bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 p-5 rounded-2xl flex flex-col justify-between shadow-sm relative overflow-hidden">
          <span class="text-xs font-bold text-zinc-400 uppercase">{{ t.dashboard.scoreCard }}</span>
          <div class="mt-4 flex items-baseline space-x-1">
            <span class="text-3xl font-black tracking-tight" :class="getScoreColor(latest?.wellness_score)">
              {{ latest?.wellness_score }}
            </span>
            <span class="text-xs text-zinc-400 font-semibold">/100</span>
          </div>
          <div class="absolute -right-6 -bottom-6 w-16 h-16 bg-emerald-500/5 rounded-full filter blur-md"></div>
        </div>

        <!-- Stress Card -->
        <div class="bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 p-5 rounded-2xl flex flex-col justify-between shadow-sm">
          <span class="text-xs font-bold text-zinc-400 uppercase">{{ t.dashboard.stressCard }}</span>
          <div class="mt-4 flex items-baseline space-x-1">
            <span class="text-3xl font-black tracking-tight text-teal-500">{{ latest?.stress_level }}</span>
            <span class="text-xs text-zinc-400 font-semibold">/10</span>
          </div>
        </div>

        <!-- Sleep Card -->
        <div class="bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 p-5 rounded-2xl flex flex-col justify-between shadow-sm">
          <span class="text-xs font-bold text-zinc-400 uppercase">{{ t.dashboard.sleepCard }}</span>
          <div class="mt-4 flex items-baseline space-x-1">
            <span class="text-3xl font-black tracking-tight text-indigo-500">{{ latest?.sleep_hours }}</span>
            <span class="text-xs text-zinc-400 font-semibold">hrs</span>
          </div>
        </div>

        <!-- Water Card -->
        <div class="bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 p-5 rounded-2xl flex flex-col justify-between shadow-sm">
          <span class="text-xs font-bold text-zinc-400 uppercase">{{ t.dashboard.waterCard }}</span>
          <div class="mt-4 flex items-baseline space-x-1">
            <span class="text-3xl font-black tracking-tight text-blue-500">{{ latest?.water_intake }}</span>
            <span class="text-xs text-zinc-400 font-semibold">L</span>
          </div>
        </div>

        <!-- Activity Card -->
        <div class="bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 p-5 rounded-2xl flex flex-col justify-between shadow-sm">
          <span class="text-xs font-bold text-zinc-400 uppercase">{{ t.dashboard.activityCard }}</span>
          <span class="text-lg font-extrabold text-amber-500 mt-4 tracking-tight capitalize">
            {{ latest?.activity_level === 'low' ? t.dashboard.activityLow : (latest?.activity_level === 'medium' ? t.dashboard.activityMedium : t.dashboard.activityHigh) }}
          </span>
        </div>

        <!-- Mood Card -->
        <div class="bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 p-5 rounded-2xl flex flex-col justify-between shadow-sm">
          <span class="text-xs font-bold text-zinc-400 uppercase">{{ t.dashboard.moodCard }}</span>
          <div class="mt-4 flex items-center space-x-2">
            <span class="text-2xl">
              {{ latest?.mood_level === 'sad' ? '😢' : (latest?.mood_level === 'stressed' ? '😰' : (latest?.mood_level === 'neutral' ? '😐' : (latest?.mood_level === 'happy' ? '😊' : '🤩'))) }}
            </span>
            <span class="text-xs font-bold text-violet-500 capitalize">{{ latest?.mood_level }}</span>
          </div>
        </div>

      </div>

      <!-- Chart and Historical Logs Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- SVG Trajectory Chart -->
        <div class="lg:col-span-8 bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 p-6 rounded-3xl shadow-sm space-y-6 flex flex-col justify-between">
          <div class="flex items-center justify-between border-b border-zinc-200/30 dark:border-zinc-800/30 pb-3">
            <h3 class="font-extrabold text-zinc-800 dark:text-zinc-200 text-sm tracking-wide uppercase">
              {{ t.dashboard.chartHeader }}
            </h3>
            <span class="text-xs text-zinc-400 font-medium font-mono">{{ assessments.length }} logged intervals</span>
          </div>

          <!-- Trend line rendering -->
          <div class="relative w-full aspect-[5/2.2] flex items-center justify-center p-2 rounded-2xl bg-zinc-50/50 dark:bg-zinc-950/20 border border-zinc-200/20 dark:border-zinc-800/20">
            <!-- If we only have 1 data point, we can render a simple bar placeholder -->
            <div v-if="assessments.length < 2" class="text-center text-zinc-400 text-xs font-semibold py-8 space-y-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              <span>Add more assessments to visualize score trajectory graph!</span>
            </div>

            <!-- Beautiful inline SVG Line Graph -->
            <svg v-else viewBox="0 0 500 120" class="w-full h-full">
              <!-- Grid background guidelines -->
              <line x1="30" y1="15" x2="470" y2="15" class="stroke-zinc-200/50 dark:stroke-zinc-800/30" stroke-width="1" stroke-dasharray="4" />
              <line x1="30" y1="60" x2="470" y2="60" class="stroke-zinc-200/50 dark:stroke-zinc-800/30" stroke-width="1" stroke-dasharray="4" />
              <line x1="30" y1="105" x2="470" y2="105" class="stroke-zinc-200/50 dark:stroke-zinc-800/30" stroke-width="1" stroke-dasharray="4" />

              <!-- Polyline path -->
              <polyline :points="chartPoints" fill="none" class="stroke-emerald-500" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
              
              <!-- Dynamic dots for each coordinate -->
              <circle v-for="(pt, idx) in chartPoints.split(' ')" 
                      :key="idx"
                      :cx="pt.split(',')[0]" 
                      :cy="pt.split(',')[1]" 
                      r="4.5" 
                      class="fill-white stroke-emerald-500 dark:fill-zinc-950" 
                      stroke-width="2.5" />
            </svg>
          </div>

          <!-- Quick statistics review -->
          <div class="flex justify-between text-xs text-zinc-400 font-mono">
            <span>START: {{ formatDate(assessments[0].created_at) }}</span>
            <span>CURRENT: {{ formatDate(latest.created_at) }}</span>
          </div>
        </div>

        <!-- AI Guidance / Insight Panel -->
        <div class="lg:col-span-4 bg-gradient-to-br from-emerald-500/5 via-teal-500/5 to-indigo-500/5 border border-zinc-200/50 dark:border-zinc-800/50 p-6 rounded-3xl shadow-sm flex flex-col justify-between">
          <div class="space-y-4">
            <h3 class="font-extrabold text-zinc-800 dark:text-zinc-200 text-sm tracking-wide uppercase border-b border-zinc-200/30 dark:border-zinc-800/30 pb-3">
              {{ t.dashboard.insightsHeader }}
            </h3>
            <div class="text-sm text-zinc-600 dark:text-zinc-300 leading-relaxed whitespace-pre-line max-h-72 overflow-y-auto pr-1">
              {{ latest?.suggestions || t.dashboard.recommendationsPlh }}
            </div>
          </div>
          
          <RouterLink to="/assessment" class="mt-6 w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-center text-xs rounded-xl shadow transition duration-200">
            Retake Assessment
          </RouterLink>
        </div>

      </div>

      <!-- Wellness Challenges Progress Row -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Stats Summary Card (4 columns) -->
        <div class="lg:col-span-4 bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 p-6 rounded-3xl shadow-sm flex flex-col justify-between space-y-6">
          <h3 class="font-extrabold text-zinc-800 dark:text-zinc-200 text-sm tracking-wide uppercase border-b border-zinc-200/30 dark:border-zinc-800/30 pb-3">
            {{ currentLang === 'am' ? 'የተግዳሮቶች ሁኔታ' : 'Wellness Challenge Stats' }}
          </h3>
          
          <div class="space-y-4 flex-grow">
            <!-- Points Balance -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-500/5 border border-emerald-500/10">
              <div class="flex items-center space-x-3">
                <span class="text-2xl">💎</span>
                <div class="flex flex-col">
                  <span class="text-[10px] uppercase font-bold text-zinc-400 leading-none">Wellness Points</span>
                  <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Total Points Balance</span>
                </div>
              </div>
              <span class="text-2xl font-black text-emerald-500">{{ challengeStats.wellness_points }}</span>
            </div>

            <!-- Current Streak -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-orange-500/5 border border-orange-500/10">
              <div class="flex items-center space-x-3">
                <span class="text-2xl">🔥</span>
                <div class="flex flex-col">
                  <span class="text-[10px] uppercase font-bold text-zinc-400 leading-none">{{ t.challenges.currentStreak }}</span>
                  <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Consecutive Activity</span>
                </div>
              </div>
              <span class="text-2xl font-black text-orange-500">{{ challengeStats.streak }} {{ t.challenges.streakDays }}</span>
            </div>

            <!-- Total Completed -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-indigo-500/5 border border-indigo-500/10">
              <div class="flex items-center space-x-3">
                <span class="text-2xl">🏆</span>
                <div class="flex flex-col">
                  <span class="text-[10px] uppercase font-bold text-zinc-400 leading-none">{{ t.challenges.totalCompleted }}</span>
                  <span class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">Habits Logged</span>
                </div>
              </div>
              <span class="text-2xl font-black text-indigo-500">{{ challengeStats.total_completed }}</span>
            </div>
          </div>

          <RouterLink to="/challenges" class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-center text-xs rounded-xl shadow transition duration-200 cursor-pointer">
            {{ currentLang === 'am' ? 'ተግዳሮቶችን አሳይ' : 'Explore Wellness Challenges' }}
          </RouterLink>
        </div>

        <!-- Recent Reflections list (8 columns) -->
        <div class="lg:col-span-8 bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 p-6 rounded-3xl shadow-sm flex flex-col justify-between">
          <div class="space-y-4 w-full">
            <div class="flex items-center justify-between border-b border-zinc-200/30 dark:border-zinc-800/30 pb-3">
              <h3 class="font-extrabold text-zinc-800 dark:text-zinc-200 text-sm tracking-wide uppercase">
                {{ t.challenges.recentReflections }}
              </h3>
              <span class="text-[10px] font-extrabold uppercase tracking-widest text-zinc-400">Mindfulness Journal</span>
            </div>

            <!-- If no reflections -->
            <div v-if="!challengeStats.recent_reflections || challengeStats.recent_reflections.length === 0" class="flex flex-col items-center justify-center py-12 text-center text-zinc-400 space-y-2">
              <span class="text-3xl">📝</span>
              <p class="text-xs font-semibold max-w-xs">{{ t.challenges.noReflections }}</p>
              <RouterLink to="/challenges" class="text-xs font-extrabold text-emerald-500 hover:underline pt-2">{{ t.challenges.joinBtn }}</RouterLink>
            </div>

            <!-- Reflections Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[300px] overflow-y-auto pr-1">
              <div 
                v-for="(reflec, rIdx) in challengeStats.recent_reflections" 
                :key="rIdx"
                class="p-4 rounded-2xl bg-zinc-50/50 dark:bg-zinc-950/40 border border-zinc-200/40 dark:border-zinc-800/40 flex flex-col justify-between space-y-3"
              >
                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-[9px] uppercase font-black text-zinc-400 tracking-wider">
                      {{ getCategoryIcon(reflec.category) }} {{ reflec.category }}
                    </span>
                    <span class="text-[9px] font-bold text-zinc-400 bg-zinc-200/20 dark:bg-zinc-800/30 py-0.5 px-2 rounded-full">
                      {{ formatReflectionDate(reflec.completed_at) }}
                    </span>
                  </div>
                  <h4 class="text-xs font-extrabold text-zinc-800 dark:text-zinc-200 leading-tight">
                    {{ currentLang === 'am' ? reflec.challenge_name_am : reflec.challenge_name }}
                  </h4>
                  <p class="text-xs text-zinc-600 dark:text-zinc-300 italic leading-relaxed font-medium">
                    &ldquo;{{ reflec.reflection_text }}&rdquo;
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- History Table Log List -->
      <div class="bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 rounded-3xl p-6 shadow-sm">
        <h3 class="font-extrabold text-zinc-800 dark:text-zinc-200 text-sm tracking-wide uppercase border-b border-zinc-200/30 dark:border-zinc-800/30 pb-4 mb-4">
          All Historical Assessment Logs
        </h3>
        
        <div class="overflow-x-auto w-full">
          <table class="w-full text-left text-sm font-medium">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800 text-zinc-400 font-bold">
                <th class="py-3 px-4">Date</th>
                <th class="py-3 px-4">Nickname</th>
                <th class="py-3 px-4 text-center">Score</th>
                <th class="py-3 px-4 text-center">Stress</th>
                <th class="py-3 px-4 text-center">Sleep</th>
                <th class="py-3 px-4 text-center">Water</th>
                <th class="py-3 px-4 text-center">Activity</th>
                <th class="py-3 px-4 text-center">Mood</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200/50 dark:divide-zinc-800/50">
              <tr v-for="item in assessments.slice().reverse()" :key="item.id" class="text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50/50 dark:hover:bg-zinc-900/30 transition">
                <td class="py-3 px-4 font-mono text-xs">{{ formatDate(item.created_at) }}</td>
                <td class="py-3 px-4 font-bold">{{ item.nickname }}</td>
                <td class="py-3 px-4 text-center font-extrabold" :class="getScoreColor(item.wellness_score)">{{ item.wellness_score }}/100</td>
                <td class="py-3 px-4 text-center">{{ item.stress_level }}/10</td>
                <td class="py-3 px-4 text-center">{{ item.sleep_hours }} hrs</td>
                <td class="py-3 px-4 text-center">{{ item.water_intake }} L</td>
                <td class="py-3 px-4 text-center capitalize">{{ item.activity_level }}</td>
                <td class="py-3 px-4 text-center capitalize">{{ item.mood_level }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

  </div>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
