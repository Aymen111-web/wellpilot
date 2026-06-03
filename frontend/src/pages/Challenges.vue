<script setup>
import { ref, onMounted, computed } from 'vue';
import { useLanguage } from '../services/translations';
import api from '../services/api';

const { currentLang, t } = useLanguage();

// User identity states
const nickname = ref(localStorage.getItem('wellpilot_nickname') || 'Guest');
const isEditingNickname = ref(false);
const editNicknameVal = ref('');

// Challenges states
const challenges = ref([]);
const isLoading = ref(true);
const errorMsg = ref('');

// Challenge completions & statistics states
const stats = ref({
  total_completed: 0,
  wellness_points: 0,
  streak: 0,
  completed_categories_today: []
});

// Modal state managers
const showReflectionModal = ref(false);
const showSuccessModal = ref(false);
const activeChallenge = ref(null);
const reflectionText = ref('');
const submittingCompletion = ref(false);
const completionErrorMsg = ref('');
const completedPoints = ref(0);

const fetchChallenges = async () => {
  try {
    const response = await api.get('/challenges');
    challenges.value = response.data;
  } catch (err) {
    console.error('Error fetching challenges:', err);
    throw err;
  }
};

const fetchStats = async () => {
  try {
    const response = await api.get(`/challenges/stats?nickname=${encodeURIComponent(nickname.value)}`);
    stats.value = response.data;
    // Sync points to local storage for any legacy parts that look there
    localStorage.setItem('wellpilot_points', String(stats.value.wellness_points));
  } catch (err) {
    console.error('Error fetching stats:', err);
  }
};

const fetchAllData = async () => {
  isLoading.value = true;
  errorMsg.value = '';
  try {
    await Promise.all([fetchChallenges(), fetchStats()]);
  } catch (err) {
    errorMsg.value = currentLang.value === 'en'
      ? 'Unable to retrieve wellness challenges. Is the backend running?'
      : 'የጤና ተግዳሮቶችን ለማውረድ አልተቻለም። የጀርባ አገልግሎት (backend) እየሰራ መሆኑን ያረጋግጡ::';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchAllData();
});

// Nickname editing logic
const startEditNickname = () => {
  editNicknameVal.value = nickname.value;
  isEditingNickname.value = true;
};

const saveNickname = async () => {
  if (editNicknameVal.value.trim()) {
    nickname.value = editNicknameVal.value.trim();
    localStorage.setItem('wellpilot_nickname', nickname.value);
    isEditingNickname.value = false;
    isLoading.value = true;
    await fetchStats();
    isLoading.value = false;
  } else {
    isEditingNickname.value = false;
  }
};

// Check if a challenge category is completed today
const isCategoryCompletedToday = (category) => {
  return stats.value.completed_categories_today?.includes(category);
};

// Start completion flow
const triggerMarkComplete = (challenge) => {
  if (isCategoryCompletedToday(challenge.category)) {
    return;
  }
  activeChallenge.value = challenge;
  reflectionText.value = '';
  completionErrorMsg.value = '';
  showReflectionModal.value = true;
};

// Submit completion to backend
const submitCompletion = async (skipReflection = false) => {
  if (!activeChallenge.value) return;

  submittingCompletion.value = true;
  completionErrorMsg.value = '';

  try {
    const response = await api.post(`/challenges/${activeChallenge.value.id}/complete`, {
      nickname: nickname.value,
      reflection_text: skipReflection ? '' : reflectionText.value.trim(),
    });

    completedPoints.value = response.data.points_earned;
    showReflectionModal.value = false;
    showSuccessModal.value = true;
    
    // Refresh stats to lock category and update balance
    await fetchStats();
  } catch (err) {
    console.error('Error completing challenge:', err);
    if (err.response && err.response.data) {
      completionErrorMsg.value = currentLang.value === 'am'
        ? (err.response.data.error_am || err.response.data.error)
        : (err.response.data.error || 'Failed to complete challenge.');
    } else {
      completionErrorMsg.value = currentLang.value === 'en'
        ? 'Failed to submit completion. Please check connection.'
        : 'ማጠናቀቁን ለመመዝገብ አልተቻለም። እባክዎን ግንኙነቱን ያረጋግጡ።';
    }
  } finally {
    submittingCompletion.value = false;
  }
};

// Icon and category translations helper
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

const getCategoryName = (cat) => {
  if (currentLang.value === 'am') {
    const names = {
      'Hydration': 'የውሃ አወሳሰድ',
      'Sleep': 'እንቅልፍ',
      'Physical Activity': 'አካላዊ ብቃት',
      'Mental Wellness': 'አእምሯዊ ደህንነት',
      'Nutrition': 'ስነ-ምግብ',
      'Self-Care': 'ራስን መንከባከብ'
    };
    return names[cat] || cat;
  }
  return cat;
};
</script>

<template>
  <div class="space-y-10 animate-fade-in pb-16">
    
    <!-- Header & Stats Dashboard Row -->
    <div class="border-b border-zinc-200/50 dark:border-zinc-800/50 pb-8 space-y-6">
      
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="space-y-2 text-center md:text-left">
          <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white">
            {{ t.challenges.title }}
          </h1>
          <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 max-w-xl">
            {{ t.challenges.subtitle }}
          </p>
          <div class="w-16 h-1 bg-gradient-to-r from-emerald-500 to-indigo-500 md:mx-0 mx-auto rounded-full mt-3"></div>
        </div>

        <!-- Nickname Pill Editor -->
        <div class="flex justify-center md:justify-end">
          <div class="flex items-center space-x-2 text-xs font-bold bg-zinc-100 dark:bg-zinc-800/60 hover:bg-zinc-200/50 dark:hover:bg-zinc-700/50 py-2 px-4 rounded-full border border-zinc-200/30 dark:border-zinc-700/30 shadow-sm transition">
            <span class="text-zinc-400 uppercase tracking-wider">{{ t.challenges.nicknameLabel }}:</span>
            <span v-if="!isEditingNickname" class="text-emerald-500 dark:text-emerald-400">{{ nickname }}</span>
            <input 
              v-else 
              v-model="editNicknameVal" 
              @blur="saveNickname" 
              @keyup.enter="saveNickname" 
              ref="nicknameInput"
              class="bg-transparent border-b border-emerald-500 focus:outline-none text-emerald-500 dark:text-emerald-400 font-extrabold w-24 text-center" 
              placeholder="Nickname"
            />
            <button @click="isEditingNickname ? saveNickname() : startEditNickname()" class="hover:scale-110 active:scale-95 text-zinc-400 hover:text-emerald-500 transition duration-200 cursor-pointer">
              {{ isEditingNickname ? '✓' : '✏️' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Statistics Dashboard Summary Panels -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-4">
        
        <!-- Score Card -->
        <div class="bg-gradient-to-br from-emerald-500/5 to-teal-500/5 border border-emerald-500/20 p-5 rounded-2xl flex items-center justify-between shadow-sm relative overflow-hidden">
          <div class="space-y-1">
            <span class="text-xs uppercase font-extrabold tracking-widest text-emerald-600 dark:text-emerald-400">
              {{ t.challenges.pointsText }}
            </span>
            <p class="text-[10px] text-zinc-400 leading-normal max-w-[150px]">
              {{ t.challenges.pointsDesc }}
            </p>
          </div>
          <div class="text-right">
            <span class="block text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 to-teal-500">
              {{ stats.wellness_points }}
            </span>
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">💎 Points</span>
          </div>
        </div>

        <!-- Streak Card -->
        <div class="bg-gradient-to-br from-orange-500/5 to-red-500/5 border border-orange-500/20 p-5 rounded-2xl flex items-center justify-between shadow-sm relative overflow-hidden">
          <div class="space-y-1">
            <span class="text-xs uppercase font-extrabold tracking-widest text-orange-600 dark:text-orange-400">
              {{ t.challenges.currentStreak }}
            </span>
            <p class="text-[10px] text-zinc-400 leading-normal max-w-[150px]">
              Keep completing challenges to build your streak.
            </p>
          </div>
          <div class="text-right">
            <span class="block text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-500">
              {{ stats.streak }}
            </span>
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">🔥 {{ t.challenges.streakDays }}</span>
          </div>
        </div>

        <!-- Completed Challenges Card -->
        <div class="bg-gradient-to-br from-indigo-500/5 to-blue-500/5 border border-indigo-500/20 p-5 rounded-2xl flex items-center justify-between shadow-sm relative overflow-hidden">
          <div class="space-y-1">
            <span class="text-xs uppercase font-extrabold tracking-widest text-indigo-600 dark:text-indigo-400">
              {{ t.challenges.totalCompleted }}
            </span>
            <p class="text-[10px] text-zinc-400 leading-normal max-w-[150px]">
              Total habits logged in WellPilot.
            </p>
          </div>
          <div class="text-right">
            <span class="block text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-500 to-blue-500">
              {{ stats.total_completed }}
            </span>
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">🏆 Completed</span>
          </div>
        </div>

      </div>

    </div>

    <!-- API Loader -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 space-y-4">
      <div class="w-12 h-12 rounded-full border-4 border-emerald-500/20 border-t-emerald-500 animate-spin"></div>
      <p class="text-sm font-bold text-zinc-400 animate-pulse">Loading wellness challenges...</p>
    </div>

    <!-- Error View -->
    <div v-else-if="errorMsg" class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200/50 p-6 rounded-2xl text-center max-w-lg mx-auto space-y-4 shadow-xl">
      <span class="text-3xl">⚠️</span>
      <p class="text-sm font-semibold text-rose-600 dark:text-rose-400">{{ errorMsg }}</p>
      <button @click="fetchAllData" class="px-4 py-2 bg-rose-500 text-white rounded-xl text-xs font-bold shadow hover:bg-rose-600 transition">
        Try Again
      </button>
    </div>

    <!-- Main Challenges Section -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
      
      <!-- Loop challenges cards -->
      <div 
        v-for="ch in challenges" 
        :key="ch.id"
        class="bg-white dark:bg-zinc-900/60 border rounded-2xl p-6 shadow-md flex flex-col justify-between relative overflow-hidden transition duration-300 hover:shadow-lg"
        :class="isCategoryCompletedToday(ch.category)
          ? 'border-zinc-200/30 dark:border-zinc-800/30 opacity-75'
          : 'border-zinc-200/50 dark:border-zinc-800/50'"
      >
        
        <!-- Reward Points Tag -->
        <div class="absolute top-4 right-4 flex items-center space-x-1.5 px-3 py-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold text-[10px] uppercase tracking-wider">
          <span>💎</span>
          <span>+{{ ch.reward_points }} {{ currentLang === 'am' ? 'ነጥብ' : 'pts' }}</span>
        </div>

        <div class="space-y-4">
          
          <div class="space-y-1">
            <!-- Category Badge -->
            <span class="inline-flex items-center space-x-1 text-[10px] font-extrabold uppercase tracking-widest text-zinc-400 bg-zinc-100 dark:bg-zinc-800 py-1 px-2.5 rounded-full border border-zinc-200/20 dark:border-zinc-700/20">
              <span>{{ getCategoryIcon(ch.category) }}</span>
              <span>{{ getCategoryName(ch.category) }}</span>
            </span>
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white pt-2">
              {{ currentLang === 'am' ? ch.challenge_name_am : ch.challenge_name }}
            </h3>
          </div>

          <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed min-h-[50px]">
            {{ currentLang === 'am' ? ch.description_am : ch.description }}
          </p>

        </div>

        <!-- Completion Actions -->
        <div class="mt-6 border-t border-zinc-200/30 dark:border-zinc-800/30 pt-4">
          <!-- Category lock message -->
          <div v-if="isCategoryCompletedToday(ch.category)" class="bg-rose-50/50 dark:bg-rose-950/10 border border-rose-200/20 text-rose-600 dark:text-rose-400 p-3 rounded-xl text-xs font-semibold text-center mb-3">
            🔒 {{ t.challenges.alreadyCompleted }}
          </div>

          <!-- Mark Complete Button -->
          <button 
            @click="triggerMarkComplete(ch)"
            :disabled="isCategoryCompletedToday(ch.category)"
            class="w-full py-3 font-bold text-xs rounded-xl shadow-md transition duration-200 flex items-center justify-center space-x-1 cursor-pointer"
            :class="isCategoryCompletedToday(ch.category)
              ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 cursor-not-allowed shadow-none'
              : 'bg-emerald-500 hover:bg-emerald-600 text-white hover:shadow-emerald-500/20 hover:scale-[1.01]'"
          >
            <span>✓</span>
            <span>{{ t.challenges.markComplete }}</span>
          </button>
        </div>

      </div>

    </div>

    <!-- 1. Reflection Modal -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showReflectionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/80 backdrop-blur-sm">
        
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200/50 dark:border-zinc-800/50 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative overflow-hidden animate-zoom-in">
          
          <div class="space-y-6">
            <div class="space-y-2">
              <span class="text-xs uppercase font-extrabold tracking-widest text-emerald-500">
                {{ getCategoryIcon(activeChallenge?.category) }} {{ getCategoryName(activeChallenge?.category) }}
              </span>
              <h3 class="text-xl font-bold text-zinc-900 dark:text-white">
                {{ t.challenges.reflectionTitle }}
              </h3>
              <p class="text-xs text-zinc-400">
                {{ currentLang === 'am' ? 'ተግዳሮት፦ ' + activeChallenge?.challenge_name_am : 'Challenge: ' + activeChallenge?.challenge_name }}
              </p>
            </div>

            <!-- Text area -->
            <textarea 
              v-model="reflectionText"
              rows="4"
              class="w-full px-4 py-3 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 text-sm transition"
              :placeholder="t.challenges.reflectionPlh"
            ></textarea>

            <!-- Loading Spinner in Modal -->
            <div v-if="submittingCompletion" class="flex flex-col items-center justify-center py-4 space-y-2">
              <div class="w-8 h-8 rounded-full border-2 border-emerald-500/20 border-t-emerald-500 animate-spin"></div>
            </div>

            <!-- Error Banner -->
            <div v-if="completionErrorMsg" class="p-3.5 rounded-xl border border-rose-200/50 bg-rose-50/50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 text-xs font-semibold">
              {{ completionErrorMsg }}
            </div>

            <!-- Buttons -->
            <div v-if="!submittingCompletion" class="flex flex-col sm:flex-row gap-3 justify-end pt-4 border-t border-zinc-200/40 dark:border-zinc-800/40">
              <button 
                @click="submitCompletion(true)"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-850 hover:bg-zinc-50 dark:hover:bg-zinc-800 text-xs font-bold text-zinc-500 dark:text-zinc-300 transition duration-200 cursor-pointer"
              >
                {{ t.challenges.skip }}
              </button>
              <button 
                @click="submitCompletion(false)"
                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold transition duration-200 shadow-md shadow-emerald-500/10 cursor-pointer"
              >
                {{ t.challenges.saveReflection }}
              </button>
            </div>
          </div>

        </div>
      </div>
    </transition>

    <!-- 2. Success Modal -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/80 backdrop-blur-sm">
        
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200/50 dark:border-zinc-800/50 rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl text-center relative overflow-hidden animate-zoom-in">
          
          <div class="space-y-6 py-4">
            <div class="w-20 h-20 bg-emerald-500/10 border border-emerald-500/20 rounded-full flex items-center justify-center mx-auto text-emerald-500 animate-bounce">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>

            <div class="space-y-2">
              <h3 class="text-2xl font-extrabold text-zinc-900 dark:text-white">
                {{ t.challenges.successTitle }}
              </h3>
              <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                +{{ completedPoints }} {{ t.challenges.pointsText }} earned!
              </p>
              <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed px-4 pt-2">
                {{ t.challenges.successBody }}
              </p>
            </div>

            <button 
              @click="showSuccessModal = false"
              class="w-full px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs tracking-wider transition cursor-pointer"
            >
              Okay, Great!
            </button>
          </div>

        </div>
      </div>
    </transition>

  </div>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}

.animate-zoom-in {
  animation: zoomIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
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

@keyframes zoomIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}
</style>
