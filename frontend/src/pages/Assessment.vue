<script setup>
import { ref } from 'vue';
import { useLanguage } from '../services/translations';
import api from '../services/api';

const { currentLang, t } = useLanguage();

// Form input models
const nickname = ref('');
const stressLevel = ref(5);
const sleepHours = ref(7);
const waterIntake = ref(2);
const activityLevel = ref('medium');
const moodLevel = ref('neutral');

// App state
const isSubmitting = ref(false);
const showResults = ref(false);
const errorMsg = ref('');
const resultData = ref(null);

// Booking modal simulation
const showBookingModal = ref(false);
const selectedActivity = ref(null);
const isBookingSuccess = ref(false);
const isBookingLoading = ref(false);

const getCategoryTranslations = (category) => {
  const mapping = {
    'All': 'filterAll',
    'Stress': 'filterStress',
    'Sleep': 'filterSleep',
    'Physical Activity': 'filterActivity',
    'Hydration': 'filterHydration',
    'Mood': 'filterMood'
  };
  return t.value.resorts[mapping[category] || 'filterAll'];
};

const getCategoryIcon = (category) => {
  const icons = {
    'Stress': '💆‍♀️',
    'Sleep': '🛌',
    'Physical Activity': '🧘‍♂️',
    'Hydration': '🥤',
    'Mood': '🎨'
  };
  return icons[category] || '🌿';
};

const openBooking = (activity) => {
  selectedActivity.value = activity;
  isBookingSuccess.value = false;
  showBookingModal.value = true;
};

const confirmBooking = () => {
  isBookingLoading.value = true;
  setTimeout(() => {
    isBookingLoading.value = false;
    isBookingSuccess.value = true;
  }, 1000);
};

const resetForm = () => {
  nickname.value = '';
  stressLevel.value = 5;
  sleepHours.value = 7;
  waterIntake.value = 2;
  activityLevel.value = 'medium';
  moodLevel.value = 'neutral';
  showResults.value = false;
  resultData.value = null;
  errorMsg.value = '';
};

const submitAssessment = async () => {
  // Input validations
  if (!nickname.value.trim() || sleepHours.value < 0 || sleepHours.value > 24 || waterIntake.value < 0 || waterIntake.value > 20) {
    errorMsg.value = currentLang.value === 'en' 
      ? 'Please fill in all the required fields correctly.' 
      : 'እባክዎን ሁሉንም አስፈላጊ ቦታዎች በትክክል ይሙሉ::';
    return;
  }

  isSubmitting.value = true;
  errorMsg.value = '';

  try {
    const response = await api.post('/assessments', {
      nickname: nickname.value.trim(),
      stress_level: parseInt(stressLevel.value),
      sleep_hours: parseFloat(sleepHours.value),
      water_intake: parseFloat(waterIntake.value),
      activity_level: activityLevel.value,
      mood_level: moodLevel.value,
      lang: currentLang.value,
    });

    resultData.value = response.data;
    showResults.value = true;
    
    // Save nickname for quick usage across other parts like AI Coach
    localStorage.setItem('wellpilot_nickname', nickname.value.trim());
  } catch (err) {
    console.error('Assessment submission error:', err);
    errorMsg.value = currentLang.value === 'en' 
      ? 'Connection failed. Please check if backend is running.' 
      : 'ግንኙነት አልተሳካም። እባክዎን የጀርባ አገልግሎት (backend) መስራቱን ያረጋግጡ።';
  } finally {
    isSubmitting.value = false;
  }
};

const getZoneColor = (score) => {
  if (score >= 80) return 'text-emerald-500 border-emerald-500/30 bg-emerald-500/10';
  if (score >= 60) return 'text-teal-500 border-teal-500/30 bg-teal-500/10';
  return 'text-rose-500 border-rose-500/30 bg-rose-500/10';
};
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-8 animate-fade-in">
    
    <!-- Header Block -->
    <div class="text-center max-w-2xl mx-auto space-y-2">
      <h1 class="text-3xl sm:text-4xl font-extrabold text-zinc-900 dark:text-white">
        {{ t.assessment.title }}
      </h1>
      <p class="text-sm sm:text-base text-zinc-500 dark:text-zinc-400">
        {{ t.assessment.subtitle }}
      </p>
      <div class="w-20 h-1 bg-gradient-to-r from-emerald-500 to-indigo-500 mx-auto rounded-full mt-4"></div>
    </div>

    <!-- Questionnaire Panel -->
    <div v-if="!showResults" class="bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 shadow-2xl rounded-3xl p-6 sm:p-10 relative overflow-hidden backdrop-blur-md">
      
      <!-- Submission Loader overlay -->
      <div v-if="isSubmitting" class="absolute inset-0 bg-white/80 dark:bg-zinc-950/80 z-20 flex flex-col items-center justify-center space-y-4">
        <div class="relative w-16 h-16">
          <div class="absolute inset-0 rounded-full border-4 border-emerald-500/20"></div>
          <div class="absolute inset-0 rounded-full border-4 border-emerald-500 border-t-transparent animate-spin"></div>
        </div>
        <p class="font-bold text-emerald-600 dark:text-emerald-400 animate-pulse">
          {{ t.assessment.submitting }}
        </p>
      </div>

      <form @submit.prevent="submitAssessment" class="space-y-8">
        
        <!-- Nickname -->
        <div class="space-y-2">
          <label class="block text-sm font-bold text-zinc-800 dark:text-zinc-200">
            {{ t.assessment.nicknameLabel }} <span class="text-emerald-500">*</span>
          </label>
          <input 
            type="text" 
            v-model="nickname" 
            required 
            class="w-full px-4 py-3 rounded-xl bg-zinc-50 dark:bg-zinc-950/80 border border-zinc-200 dark:border-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition" 
            :placeholder="t.assessment.nicknamePlh" 
          />
        </div>

        <!-- Stress Level Slider -->
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <label class="block text-sm font-bold text-zinc-800 dark:text-zinc-200">
              {{ t.assessment.stressLabel }}
            </label>
            <span class="text-xl font-black text-emerald-500 bg-emerald-500/10 px-3 py-1 rounded-lg">
              {{ stressLevel }} / 10
            </span>
          </div>
          <input 
            type="range" 
            v-model="stressLevel" 
            min="1" 
            max="10" 
            class="w-full accent-emerald-500 h-2 bg-zinc-200 dark:bg-zinc-800 rounded-lg cursor-pointer"
          />
          <div class="flex justify-between text-xs text-zinc-400">
            <span>1 (Relaxed)</span>
            <span>5 (Moderate)</span>
            <span>10 (Burnout)</span>
          </div>
        </div>

        <!-- Sleep Hours and Water Intake Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <!-- Sleep Hours -->
          <div class="space-y-2">
            <label class="block text-sm font-bold text-zinc-800 dark:text-zinc-200">
              {{ t.assessment.sleepLabel }} <span class="text-emerald-500">*</span>
            </label>
            <input 
              type="number" 
              v-model="sleepHours" 
              min="0" 
              max="24" 
              step="0.5" 
              required 
              class="w-full px-4 py-3 rounded-xl bg-zinc-50 dark:bg-zinc-950/80 border border-zinc-200 dark:border-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition" 
            />
          </div>

          <!-- Water Intake -->
          <div class="space-y-2">
            <label class="block text-sm font-bold text-zinc-800 dark:text-zinc-200">
              {{ t.assessment.waterLabel }} <span class="text-emerald-500">*</span>
            </label>
            <input 
              type="number" 
              v-model="waterIntake" 
              min="0" 
              max="20" 
              step="0.1" 
              required 
              class="w-full px-4 py-3 rounded-xl bg-zinc-50 dark:bg-zinc-950/80 border border-zinc-200 dark:border-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition" 
            />
          </div>
        </div>

        <!-- Physical Activity Radio Selector -->
        <div class="space-y-3">
          <label class="block text-sm font-bold text-zinc-800 dark:text-zinc-200">
            {{ t.assessment.activityLabel }}
          </label>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            
            <label class="flex flex-col items-center justify-center p-4 rounded-xl border cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition" 
                   :class="activityLevel === 'low' ? 'border-emerald-500 ring-2 ring-emerald-500/30 bg-emerald-500/5' : 'border-zinc-200 dark:border-zinc-800'">
              <input type="radio" v-model="activityLevel" value="low" class="sr-only" />
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-zinc-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="text-xs font-semibold text-center text-zinc-800 dark:text-zinc-200">
                {{ t.assessment.activityLow }}
              </span>
            </label>

            <label class="flex flex-col items-center justify-center p-4 rounded-xl border cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition"
                   :class="activityLevel === 'medium' ? 'border-emerald-500 ring-2 ring-emerald-500/30 bg-emerald-500/5' : 'border-zinc-200 dark:border-zinc-800'">
              <input type="radio" v-model="activityLevel" value="medium" class="sr-only" />
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-zinc-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
              <span class="text-xs font-semibold text-center text-zinc-800 dark:text-zinc-200">
                {{ t.assessment.activityMedium }}
              </span>
            </label>

            <label class="flex flex-col items-center justify-center p-4 rounded-xl border cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition"
                   :class="activityLevel === 'high' ? 'border-emerald-500 ring-2 ring-emerald-500/30 bg-emerald-500/5' : 'border-zinc-200 dark:border-zinc-800'">
              <input type="radio" v-model="activityLevel" value="high" class="sr-only" />
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-zinc-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
              </svg>
              <span class="text-xs font-semibold text-center text-zinc-800 dark:text-zinc-200">
                {{ t.assessment.activityHigh }}
              </span>
            </label>

          </div>
        </div>

        <!-- Mood Radio Selector -->
        <div class="space-y-3">
          <label class="block text-sm font-bold text-zinc-800 dark:text-zinc-200">
            {{ t.assessment.moodLabel }}
          </label>
          <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
            
            <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition"
                   :class="moodLevel === 'sad' ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500' : 'border-zinc-200 dark:border-zinc-800'">
              <input type="radio" v-model="moodLevel" value="sad" class="sr-only" />
              <span class="text-2xl mb-1">😢</span>
              <span class="text-[10px] sm:text-xs font-semibold text-zinc-800 dark:text-zinc-200 text-center">{{ t.assessment.moodSad }}</span>
            </label>

            <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition"
                   :class="moodLevel === 'stressed' ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500' : 'border-zinc-200 dark:border-zinc-800'">
              <input type="radio" v-model="moodLevel" value="stressed" class="sr-only" />
              <span class="text-2xl mb-1">😰</span>
              <span class="text-[10px] sm:text-xs font-semibold text-zinc-800 dark:text-zinc-200 text-center">{{ t.assessment.moodStressed }}</span>
            </label>

            <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition"
                   :class="moodLevel === 'neutral' ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500' : 'border-zinc-200 dark:border-zinc-800'">
              <input type="radio" v-model="moodLevel" value="neutral" class="sr-only" />
              <span class="text-2xl mb-1">😐</span>
              <span class="text-[10px] sm:text-xs font-semibold text-zinc-800 dark:text-zinc-200 text-center">{{ t.assessment.moodNeutral }}</span>
            </label>

            <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition"
                   :class="moodLevel === 'happy' ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500' : 'border-zinc-200 dark:border-zinc-800'">
              <input type="radio" v-model="moodLevel" value="happy" class="sr-only" />
              <span class="text-2xl mb-1">😊</span>
              <span class="text-[10px] sm:text-xs font-semibold text-zinc-800 dark:text-zinc-200 text-center">{{ t.assessment.moodHappy }}</span>
            </label>

            <label class="flex flex-col items-center justify-center p-3 rounded-xl border cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition"
                   :class="moodLevel === 'excited' ? 'border-emerald-500 bg-emerald-500/5 ring-1 ring-emerald-500' : 'border-zinc-200 dark:border-zinc-800'">
              <input type="radio" v-model="moodLevel" value="excited" class="sr-only" />
              <span class="text-2xl mb-1">🤩</span>
              <span class="text-[10px] sm:text-xs font-semibold text-zinc-800 dark:text-zinc-200 text-center">{{ t.assessment.moodExcited }}</span>
            </label>

          </div>
        </div>

        <!-- Error Notification Banner -->
        <transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="transform -translate-y-2 opacity-0"
          enter-to-class="transform translate-y-0 opacity-100"
        >
          <div v-if="errorMsg" class="p-4 rounded-xl border border-rose-200/50 bg-rose-50/50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 text-sm font-semibold flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span>{{ errorMsg }}</span>
          </div>
        </transition>

        <!-- Submit Button -->
        <button 
          type="submit" 
          class="w-full py-4 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold tracking-wide shadow-lg hover:shadow-emerald-500/20 hover:scale-[1.01] active:scale-95 transition-all duration-200 cursor-pointer"
        >
          {{ t.assessment.submitBtn }}
        </button>

      </form>

    </div>

    <!-- Results Display Panel -->
    <div v-else class="bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 shadow-2xl rounded-3xl p-6 sm:p-10 space-y-8 backdrop-blur-md animate-fade-in">
      
      <div class="text-center">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-white">
          {{ t.assessment.resultTitle }}
        </h2>
        <p class="text-xs text-zinc-400 font-medium">Logged for {{ resultData?.nickname }}</p>
      </div>

      <!-- Results Grid (Score Radial Gauge + Details) -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
        
        <!-- Score Radial Gauge -->
        <div class="md:col-span-4 flex flex-col items-center justify-center space-y-3">
          <div class="relative w-44 h-44 flex items-center justify-center">
            
            <!-- Glow background -->
            <div class="absolute inset-2 bg-emerald-500/5 dark:bg-emerald-500/10 rounded-full filter blur-xl animate-pulse"></div>

            <!-- SVG circle gauge -->
            <svg class="w-full h-full transform -rotate-90">
              <!-- Outer Track circle -->
              <circle cx="88" cy="88" r="70" class="stroke-zinc-100 dark:stroke-zinc-800 fill-transparent" stroke-width="12" />
              <!-- Highlight colored circle fill based on score -->
              <circle cx="88" cy="88" r="70" 
                      class="stroke-emerald-500 fill-transparent transition-all duration-1000 ease-out" 
                      stroke-width="12"
                      stroke-linecap="round"
                      :stroke-dasharray="2 * Math.PI * 70"
                      :stroke-dashoffset="2 * Math.PI * 70 * (1 - (resultData?.wellness_score || 0)/100)" />
            </svg>

            <!-- Center Score Label -->
            <div class="absolute text-center">
              <span class="block text-4xl sm:text-5xl font-black text-zinc-900 dark:text-white tracking-tight">
                {{ resultData?.wellness_score }}
              </span>
              <span class="block text-xs uppercase font-extrabold tracking-widest text-zinc-400">
                / 100
              </span>
            </div>

          </div>
          <span class="text-sm font-bold text-zinc-500">{{ t.assessment.scoreLabel }}</span>
        </div>

        <!-- Feedback Zone Box -->
        <div class="md:col-span-8 space-y-4">
          <div class="flex items-center space-x-3">
            <span class="text-sm font-extrabold text-zinc-400 uppercase tracking-wider">
              {{ t.assessment.scoreZone }}:
            </span>
            <span class="px-4 py-1.5 rounded-full border text-sm font-bold uppercase tracking-wider" 
                  :class="getZoneColor(resultData?.wellness_score)">
              {{ resultData?.wellness_score >= 80 ? (currentLang === 'en' ? 'Thriving Zone' : 'የበለጸገ ደረጃ') : (resultData?.wellness_score >= 60 ? (currentLang === 'en' ? 'Balancing Zone' : 'የተመጣጠነ ደረጃ') : (currentLang === 'en' ? 'Healing Zone' : 'የማገገሚያ ደረጃ')) }}
            </span>
          </div>

          <!-- Dynamic tailored suggestions parsed from Markdown lists -->
          <div class="bg-zinc-50 dark:bg-zinc-950/50 border border-zinc-200/50 dark:border-zinc-800/50 p-6 rounded-2xl space-y-4">
            <h3 class="font-bold text-zinc-800 dark:text-zinc-200 border-b border-zinc-200 dark:border-zinc-800 pb-2">
              {{ t.assessment.recommendationsHeader }}
            </h3>
            
            <div class="text-sm text-zinc-600 dark:text-zinc-300 leading-relaxed whitespace-pre-line">
              {{ resultData?.suggestions }}
            </div>
          </div>
        </div>

      </div>

      <!-- Recommended Resort Experiences section -->
      <div v-if="resultData?.recommended_resorts && resultData.recommended_resorts.length > 0" class="space-y-6 pt-6 border-t border-zinc-200/40 dark:border-zinc-800/40 animate-fade-in">
        <div class="text-center md:text-left space-y-1">
          <span class="text-xs uppercase font-extrabold tracking-widest text-emerald-600 dark:text-emerald-400">
            {{ currentLang === 'am' ? 'የተመረጡ የሪዞርት ተሞክሮዎች' : 'Recommended Resort Experiences' }}
          </span>
          <h3 class="text-xl font-bold text-zinc-900 dark:text-white">
            {{ currentLang === 'am' ? 'ለእርስዎ የተመረጡ ልዩ ተግባራት' : 'Tailored Activities from Our Catalogue' }}
          </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div 
            v-for="act in resultData.recommended_resorts" 
            :key="act.id"
            class="group rounded-2xl bg-zinc-50 dark:bg-zinc-950/40 border border-zinc-200/40 dark:border-zinc-800/40 p-5 flex flex-col justify-between shadow-sm relative overflow-hidden"
          >
            <div class="space-y-3">
              <span class="px-2.5 py-0.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[9px] font-extrabold uppercase tracking-wider inline-flex items-center space-x-1">
                <span>{{ getCategoryIcon(act.wellness_category) }}</span>
                <span>{{ getCategoryTranslations(act.wellness_category) }}</span>
              </span>
              <h4 class="text-sm font-bold text-zinc-900 dark:text-white group-hover:text-emerald-500 transition-colors">
                {{ currentLang === 'am' ? act.activity_name_am : act.activity_name }}
              </h4>
              <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
                {{ currentLang === 'am' ? act.description_am : act.description }}
              </p>
            </div>
            
            <div class="mt-4 pt-3 border-t border-zinc-200/30 dark:border-zinc-800/30 flex justify-end">
              <button 
                @click="openBooking(act)"
                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-[10px] tracking-wider uppercase rounded-lg transition shadow-md cursor-pointer"
              >
                {{ t.resorts.bookBtn }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Result CTA Actions -->
      <div class="flex flex-col sm:flex-row items-center justify-end gap-4 pt-4 border-t border-zinc-200/40 dark:border-zinc-800/40">
        <button 
          @click="resetForm" 
          class="w-full sm:w-auto px-6 py-3 rounded-xl border border-zinc-200 dark:border-zinc-800 font-bold hover:bg-zinc-50 dark:hover:bg-zinc-800 text-center transition duration-200 cursor-pointer"
        >
          {{ t.assessment.retakeBtn }}
        </button>

        <RouterLink 
          to="/dashboard" 
          class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-500 text-white font-bold text-center hover:bg-emerald-600 shadow-md shadow-emerald-500/10 transition duration-200 cursor-pointer"
        >
          {{ t.assessment.dashboardBtn }}
        </RouterLink>
      </div>

    </div>

    <!-- Booking Simulator Modal Overlay -->
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showBookingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/80 backdrop-blur-sm">
        
        <!-- Glassmorphic Modal Body -->
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200/50 dark:border-zinc-800/50 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative overflow-hidden animate-zoom-in">
          
          <div class="absolute top-4 right-4">
            <button @click="showBookingModal = false" class="p-2 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>

          <!-- Pending Modal State -->
          <div v-if="!isBookingSuccess" class="space-y-6">
            <div class="space-y-2">
              <span class="text-xs uppercase font-extrabold tracking-widest text-zinc-400">{{ currentLang === 'am' ? 'የቦታ ማስያዝ ዝርዝሮች' : 'Reservation Details' }}</span>
              <h3 class="text-xl font-bold text-zinc-900 dark:text-white">
                {{ currentLang === 'am' ? selectedActivity?.activity_name_am : selectedActivity?.activity_name }}
              </h3>
              <p class="text-xs text-zinc-500 leading-relaxed">{{ currentLang === 'am' ? selectedActivity?.description_am : selectedActivity?.description }}</p>
            </div>

            <!-- Loader indicator -->
            <div v-if="isBookingLoading" class="flex flex-col items-center justify-center py-6 space-y-2">
              <div class="w-8 h-8 rounded-full border-2 border-emerald-500/20 border-t-emerald-500 animate-spin"></div>
              <p class="text-[10px] text-zinc-400">{{ currentLang === 'am' ? 'ዝርዝሮችን ለሪዞርት አስተናጋጅ በመላክ ላይ...' : 'Submitting details to concierge...' }}</p>
            </div>

            <!-- Action buttons -->
            <div v-else class="flex gap-3 justify-end pt-4 border-t border-zinc-200/40 dark:border-zinc-800/40">
              <button 
                @click="showBookingModal = false"
                class="px-5 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-bold transition hover:bg-zinc-50 dark:hover:bg-zinc-800"
              >
                {{ currentLang === 'am' ? 'ሰርዝ' : 'Cancel' }}
              </button>
              <button 
                @click="confirmBooking"
                class="px-5 py-2.5 rounded-xl bg-emerald-500 text-white text-xs font-bold transition hover:bg-emerald-600 shadow shadow-emerald-500/10"
              >
                {{ currentLang === 'am' ? 'ቦታ ማስያዙን አረጋግጥ' : 'Confirm Booking' }}
              </button>
            </div>
          </div>

          <!-- Success Modal State -->
          <div v-else class="text-center py-6 space-y-6">
            <div class="w-16 h-16 bg-emerald-500/10 border border-emerald-500/20 rounded-full flex items-center justify-center mx-auto text-emerald-500 animate-bounce">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            
            <div class="space-y-2">
              <h3 class="text-xl font-bold text-zinc-900 dark:text-white">
                {{ t.resorts.bookingSuccess }}
              </h3>
              <p class="text-sm text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto leading-relaxed">
                {{ t.resorts.bookingSuccessDesc }}
              </p>
            </div>

            <button 
              @click="showBookingModal = false"
              class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-500 text-white text-xs font-bold tracking-wider hover:bg-emerald-600 transition"
            >
              {{ t.resorts.closeBtn }}
            </button>
          </div>

        </div>
      </div>
    </transition>

  </div>
</template>
