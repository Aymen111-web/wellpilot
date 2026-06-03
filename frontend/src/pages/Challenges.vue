<script setup>
import { ref, onMounted } from 'vue';
import { useLanguage } from '../services/translations';
import api from '../services/api';

const { currentLang, t } = useLanguage();

// Challenges states
const challenges = ref([]);
const isLoading = ref(true);
const errorMsg = ref('');

// Gamified Local States
const totalPoints = ref(parseInt(localStorage.getItem('wellpilot_points') || '0'));
const joinedChallenges = ref(JSON.parse(localStorage.getItem('wellpilot_joined_challenges') || '{}'));

const fetchChallengesData = async () => {
  isLoading.value = true;
  errorMsg.value = '';
  try {
    const response = await api.get('/challenges');
    challenges.value = response.data;
  } catch (err) {
    console.error('Error fetching challenges:', err);
    errorMsg.value = currentLang.value === 'en'
      ? 'Unable to retrieve wellness challenges. Is backend running?'
      : 'የጤና ተግዳሮቶችን ለማውረድ አልተቻለም። የጀርባ አገልግሎት (backend) እየሰራ መሆኑን ያረጋግጡ::';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchChallengesData();
});

const joinChallenge = (challengeId) => {
  if (joinedChallenges.value[challengeId]) return;

  // Initialize progress trackers for each day: e.g. for 7 days, make an array [false, false...]
  const challenge = challenges.value.find(c => c.id === challengeId);
  const duration = challenge ? challenge.duration_days : 5;
  
  joinedChallenges.value[challengeId] = {
    joined: true,
    progress: Array(duration).fill(false),
    completed: false
  };

  saveJoinedState();
};

const toggleDayProgress = (challengeId, dayIndex) => {
  const chState = joinedChallenges.value[challengeId];
  if (!chState || chState.completed) return;

  chState.progress[dayIndex] = !chState.progress[dayIndex];
  
  // Check if all days are completed
  const allDone = chState.progress.every(day => day === true);
  if (allDone) {
    chState.completed = true;
    
    // Add reward points
    const challenge = challenges.value.find(c => c.id === challengeId);
    const reward = challenge ? challenge.reward_points : 100;
    totalPoints.value += reward;
    localStorage.setItem('wellpilot_points', String(totalPoints.value));
  }

  saveJoinedState();
};

const saveJoinedState = () => {
  localStorage.setItem('wellpilot_joined_challenges', JSON.stringify(joinedChallenges.value));
};
</script>

<template>
  <div class="space-y-10 animate-fade-in">
    
    <!-- Header with Reward Balance Card -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center border-b border-zinc-200/50 dark:border-zinc-800/50 pb-8">
      
      <div class="md:col-span-8 space-y-2 text-center md:text-left">
        <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white">
          {{ t.challenges.title }}
        </h1>
        <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
          {{ t.challenges.subtitle }}
        </p>
        <div class="w-16 h-1 bg-gradient-to-r from-emerald-500 to-indigo-500 md:mx-0 mx-auto rounded-full mt-3"></div>
      </div>

      <!-- Gamified points counter -->
      <div class="md:col-span-4 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 border border-emerald-500/30 p-5 rounded-2xl flex items-center justify-between shadow-sm relative overflow-hidden max-w-sm mx-auto w-full">
        <div class="space-y-1">
          <span class="text-xs uppercase font-extrabold tracking-widest text-emerald-600 dark:text-emerald-400">
            {{ t.challenges.pointsHeader }}
          </span>
          <p class="text-[10px] text-zinc-400 font-medium leading-normal max-w-[200px]">
            {{ t.challenges.pointsDesc }}
          </p>
        </div>
        <div class="text-right">
          <span class="block text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-500 animate-pulse">
            {{ totalPoints }}
          </span>
          <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">{{ t.challenges.pointsText }}</span>
        </div>
      </div>

    </div>

    <!-- API Loader -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 space-y-4">
      <div class="w-12 h-12 rounded-full border-4 border-emerald-500/20 border-t-emerald-500 animate-spin"></div>
      <p class="text-sm font-bold text-zinc-400 animate-pulse">Loading challenges...</p>
    </div>

    <!-- Error View -->
    <div v-else-if="errorMsg" class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200/50 p-6 rounded-2xl text-center max-w-lg mx-auto space-y-4 shadow-xl">
      <span class="text-3xl">⚠️</span>
      <p class="text-sm font-semibold text-rose-600 dark:text-rose-400">{{ errorMsg }}</p>
      <button @click="fetchChallengesData" class="px-4 py-2 bg-rose-500 text-white rounded-xl text-xs font-bold shadow hover:bg-rose-600 transition">
        Try Again
      </button>
    </div>

    <!-- Main Challenges Section -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
      
      <!-- Loop challenges cards -->
      <div 
        v-for="ch in challenges" 
        :key="ch.id"
        class="bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 rounded-2xl p-6 shadow-md flex flex-col justify-between relative overflow-hidden"
      >
        
        <!-- Reward indicator overlay tag -->
        <div class="absolute top-4 right-4 flex items-center space-x-1.5 px-3 py-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold text-[10px] uppercase tracking-wider">
          <span>💎</span>
          <span>+{{ ch.reward_points }} pts</span>
        </div>

        <div class="space-y-4">
          
          <div class="space-y-1">
            <span class="text-xs uppercase font-extrabold tracking-widest text-zinc-400">
              {{ ch.duration_days }} {{ t.challenges.daysText }} Challenge
            </span>
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white">
              {{ ch.challenge_name }}
            </h3>
          </div>

          <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
            {{ ch.description }}
          </p>

          <!-- Joined interactive state -->
          <div v-if="joinedChallenges[ch.id]" class="border-t border-zinc-200/30 dark:border-zinc-800/30 pt-4 space-y-4">
            
            <div class="flex items-center justify-between text-xs font-bold uppercase tracking-wider text-zinc-400">
              <span>{{ t.challenges.logDayBtn }}</span>
              <span :class="joinedChallenges[ch.id].completed ? 'text-emerald-500' : 'text-amber-500'">
                {{ joinedChallenges[ch.id].completed ? t.challenges.completedStatus : t.challenges.activeStatus }}
              </span>
            </div>

            <!-- Gamified dynamic check list checkboxes -->
            <div class="flex items-center flex-wrap gap-2.5">
              <label 
                v-for="(day, dIdx) in joinedChallenges[ch.id].progress" 
                :key="dIdx"
                class="w-10 h-10 rounded-xl border flex items-center justify-center font-bold text-xs cursor-pointer select-none transition"
                :class="day 
                  ? 'bg-emerald-500 border-emerald-500 text-white shadow shadow-emerald-500/10' 
                  : (joinedChallenges[ch.id].completed ? 'bg-zinc-100 dark:bg-zinc-850 border-zinc-200 dark:border-zinc-800 text-zinc-400' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800 text-zinc-500')"
              >
                <input 
                  type="checkbox" 
                  class="sr-only" 
                  :disabled="joinedChallenges[ch.id].completed" 
                  @change="toggleDayProgress(ch.id, dIdx)"
                />
                D{{ dIdx + 1 }}
              </label>
            </div>

          </div>

        </div>

        <!-- Join Button -->
        <div v-if="!joinedChallenges[ch.id]" class="mt-6 border-t border-zinc-200/30 dark:border-zinc-800/30 pt-4">
          <button 
            @click="joinChallenge(ch.id)"
            class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-md transition duration-200 cursor-pointer"
          >
            {{ t.challenges.joinBtn }}
          </button>
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
