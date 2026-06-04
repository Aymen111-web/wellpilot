<script setup>
import { ref, onMounted } from 'vue';
import { useLanguage } from '../services/translations';
import api from '../services/api';

const { currentLang, t } = useLanguage();

// User states
const nickname = ref(localStorage.getItem('wellpilot_nickname') || 'Guest');

// Resort states
const activities = ref([]);
const personalizedRecommendations = ref([]);
const wellnessInsight = ref(null);
const activeCategoryFilter = ref('Personalized');
const hasHistory = ref(false);
const isLoading = ref(true);
const errorMsg = ref('');

// Booking modal simulation
const showBookingModal = ref(false);
const selectedActivity = ref(null);
const isBookingSuccess = ref(false);
const isBookingLoading = ref(false);

const fetchResortsData = async () => {
  isLoading.value = true;
  errorMsg.value = '';
  try {
    const response = await api.get(`/resorts?nickname=${encodeURIComponent(nickname.value)}`);
    activities.value = response.data.all_activities || [];
    personalizedRecommendations.value = response.data.recommendations || [];
    wellnessInsight.value = response.data.wellness_insight || null;
    hasHistory.value = response.data.has_history || false;

    // Default to 'All' if they don't have history to avoid empty state, but let them toggle back to Personalized
    if (!hasHistory.value) {
      activeCategoryFilter.value = 'All';
    }
  } catch (err) {
    console.error('Error fetching resort recommendations:', err);
    errorMsg.value = currentLang.value === 'en'
      ? 'Unable to retrieve resort recommendations. Is backend running?'
      : 'የሪዞርት መረጃዎችን ለማውረድ አልተቻለም። የጀርባ አገልግሎት (backend) እየሰራ መሆኑን ያረጋግጡ::';
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchResortsData();
});

const getCategoryTranslations = (category) => {
  const mapping = {
    'All': 'filterAll',
    'Personalized': 'filterPersonalized',
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

// Zone Style mapper
const getZoneStyles = (zone) => {
  if (zone?.includes('Thriving') || zone?.includes('በለጸገ')) {
    return {
      border: 'border-emerald-500/30',
      bg: 'from-emerald-500/5 to-teal-500/5',
      text: 'text-emerald-500',
      badge: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-600 dark:text-emerald-400'
    };
  } else if (zone?.includes('Balancing') || zone?.includes('የተመጣጠነ')) {
    return {
      border: 'border-teal-500/30',
      bg: 'from-teal-500/5 to-indigo-500/5',
      text: 'text-teal-500',
      badge: 'bg-teal-500/10 border-teal-500/20 text-teal-600 dark:text-teal-400'
    };
  } else {
    return {
      border: 'border-orange-500/30',
      bg: 'from-orange-500/5 to-red-500/5',
      text: 'text-orange-500',
      badge: 'bg-orange-500/10 border-orange-500/20 text-orange-600 dark:text-orange-400'
    };
  }
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
</script>

<template>
  <div class="space-y-10 animate-fade-in pb-16">
    
    <!-- Header Area -->
    <div class="text-center max-w-2xl mx-auto space-y-2">
      <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white">
        {{ t.resorts.title }}
      </h1>
      <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
        {{ t.resorts.subtitle }}
      </p>
      <div class="w-16 h-1 bg-gradient-to-r from-emerald-500 to-indigo-500 mx-auto rounded-full mt-3"></div>
    </div>

    <!-- API Loader -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20 space-y-4">
      <div class="w-12 h-12 rounded-full border-4 border-emerald-500/20 border-t-emerald-500 animate-spin"></div>
      <p class="text-sm font-bold text-zinc-400 animate-pulse">Loading experiences...</p>
    </div>

    <!-- Error View -->
    <div v-else-if="errorMsg" class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200/50 p-6 rounded-2xl text-center max-w-lg mx-auto space-y-4 shadow-xl">
      <span class="text-3xl">⚠️</span>
      <p class="text-sm font-semibold text-rose-600 dark:text-rose-400">{{ errorMsg }}</p>
      <button @click="fetchResortsData" class="px-4 py-2 bg-rose-500 text-white rounded-xl text-xs font-bold shadow hover:bg-rose-600 transition">
        Try Again
      </button>
    </div>

    <!-- Main Resorts Section -->
    <div v-else class="space-y-8">
      
      <!-- Premium Category Filters Nav -->
      <div class="flex items-center justify-center flex-wrap gap-2.5">
        <button 
          v-for="cat in ['Personalized', 'All', 'Stress', 'Sleep', 'Physical Activity', 'Hydration', 'Mood']" 
          :key="cat"
          @click="activeCategoryFilter = cat"
          class="px-4 py-2 rounded-full text-xs sm:text-sm font-bold border transition duration-300 cursor-pointer flex items-center space-x-1"
          :class="activeCategoryFilter === cat 
            ? 'bg-emerald-500 border-emerald-500 text-white shadow-md shadow-emerald-500/20 hover:bg-emerald-600' 
            : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800'"
        >
          <span>{{ cat === 'Personalized' ? '✨' : '' }}</span>
          <span>{{ getCategoryTranslations(cat) }}</span>
        </button>
      </div>

      <!-- No History Encouragement Notice (Only shown on Personalized tab if they haven't taken assessment) -->
      <div 
        v-if="activeCategoryFilter === 'Personalized' && !hasHistory"
        class="max-w-2xl mx-auto p-5 rounded-2xl bg-zinc-50 dark:bg-zinc-900/40 border border-zinc-200 dark:border-zinc-800 flex items-center justify-between shadow-sm"
      >
        <div class="space-y-1 pr-4 text-left">
          <h4 class="text-xs font-extrabold text-zinc-900 dark:text-white uppercase tracking-wider">
            {{ currentLang === 'am' ? 'የጤና ግምገማ አልተደረገም' : 'Personalize Your Recommendations' }}
          </h4>
          <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
            {{ currentLang === 'am' 
              ? 'እባክዎን መጀመሪያ የጤና ሁኔታ ግምገማውን ይውሰዱ። ለእርስዎ የተስማሙ ልዩ ምክሮችን እዚህ እናዘጋጃለን::' 
              : 'Complete your wellness profile assessment first to generate custom recommendation cards matching your exact health parameters.' }}
          </p>
        </div>
        <RouterLink 
          to="/assessment"
          class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold rounded-xl transition shadow flex-shrink-0 cursor-pointer text-center"
        >
          {{ t.nav.assessment }}
        </RouterLink>
      </div>

      <!-- 1. Wellness Insights Panel (Only visible on Personalized filter) -->
      <div 
        v-if="activeCategoryFilter === 'Personalized' && wellnessInsight"
        class="max-w-4xl mx-auto rounded-3xl border p-6 sm:p-8 bg-gradient-to-br shadow-md space-y-4"
        :class="getZoneStyles(currentLang === 'am' ? wellnessInsight.zone_name_am : wellnessInsight.zone_name).border + ' ' + getZoneStyles(currentLang === 'am' ? wellnessInsight.zone_name_am : wellnessInsight.zone_name).bg"
      >
        <div class="flex items-center justify-between border-b border-zinc-200/20 dark:border-zinc-800/20 pb-3">
          <span class="text-[10px] font-extrabold uppercase tracking-widest text-zinc-400">
            {{ t.resorts.assessmentOverview }}
          </span>
          <!-- Score Zone Badge -->
          <span 
            class="px-4 py-1 rounded-full border text-[10px] font-black uppercase tracking-wider"
            :class="getZoneStyles(currentLang === 'am' ? wellnessInsight.zone_name_am : wellnessInsight.zone_name).badge"
          >
            {{ currentLang === 'am' ? wellnessInsight.zone_name_am : wellnessInsight.zone_name }}
          </span>
        </div>

        <div class="space-y-4 text-left">
          <p class="text-sm sm:text-base text-zinc-800 dark:text-zinc-200 leading-relaxed font-semibold">
            {{ currentLang === 'am' ? wellnessInsight.description_am : wellnessInsight.description }}
          </p>
        </div>
      </div>

      <!-- Curated Recommendations Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <!-- Loop matching cards -->
        <div 
          v-for="act in (activeCategoryFilter === 'Personalized' ? personalizedRecommendations : activities.filter(a => activeCategoryFilter === 'All' || a.wellness_category === activeCategoryFilter))" 
          :key="act.id"
          class="group rounded-2xl bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 shadow-lg hover:shadow-2xl hover:scale-[1.01] transition-all duration-300 flex flex-col justify-between overflow-hidden relative"
        >
          
          <!-- Category Indicator Header Badge -->
          <div class="p-6 pb-2 flex items-center justify-between">
            <span class="px-3 py-1 rounded-full border border-emerald-500/20 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-extrabold uppercase tracking-wider flex items-center space-x-1">
              <span>{{ getCategoryIcon(act.wellness_category) }}</span>
              <span>{{ getCategoryTranslations(act.wellness_category) }}</span>
            </span>
          </div>

          <!-- Content Details -->
          <div class="p-6 pt-2 space-y-4 flex-grow text-left">
            <h3 class="text-lg font-bold text-zinc-900 dark:text-white group-hover:text-emerald-500 transition-colors">
              {{ currentLang === 'am' ? act.activity_name_am : act.activity_name }}
            </h3>
            <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed min-h-[60px]">
              {{ currentLang === 'am' ? act.description_am : act.description }}
            </p>

            <!-- Personalized Explanation Block (Only on Personalized tab) -->
            <div 
              v-if="activeCategoryFilter === 'Personalized'"
              class="p-3 bg-emerald-500/5 dark:bg-emerald-950/20 border border-emerald-500/10 rounded-xl text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed font-semibold"
            >
              <span class="text-[9px] uppercase tracking-wider font-extrabold text-emerald-600 dark:text-emerald-400 block mb-1">
                💡 {{ t.resorts.reasonLabel }}
              </span>
              &ldquo;{{ currentLang === 'am' ? act.why_recommended_am : act.why_recommended }}&rdquo;
            </div>
          </div>

          <!-- Card Booking Actions -->
          <div class="p-6 border-t border-zinc-200/30 dark:border-zinc-800/30 flex items-center justify-end bg-zinc-50/20 dark:bg-zinc-950/20">
            <button 
              @click="openBooking(act)"
              class="w-full py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs tracking-wider uppercase transition shadow hover:shadow-emerald-500/10 cursor-pointer"
            >
              {{ t.resorts.bookBtn }}
            </button>
          </div>

        </div>
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
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>

          <!-- Pending Modal State -->
          <div v-if="!isBookingSuccess" class="space-y-6">
            <div class="space-y-2 text-left">
              <span class="text-xs uppercase font-extrabold tracking-widest text-zinc-400">{{ currentLang === 'am' ? 'የቦታ ማስያዝ ዝርዝሮች' : 'Reservation Details' }}</span>
              <h3 class="text-xl font-bold text-zinc-900 dark:text-white pt-1">
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
                class="px-5 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-bold transition hover:bg-zinc-50 dark:hover:bg-zinc-800 cursor-pointer"
              >
                {{ currentLang === 'am' ? 'ሰርዝ' : 'Cancel' }}
              </button>
              <button 
                @click="confirmBooking"
                class="px-5 py-2.5 rounded-xl bg-emerald-500 text-white text-xs font-bold transition hover:bg-emerald-600 shadow shadow-emerald-500/10 cursor-pointer"
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
              class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-500 text-white text-xs font-bold tracking-wider hover:bg-emerald-600 transition cursor-pointer"
            >
              {{ t.resorts.closeBtn }}
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
  animation: zoomIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
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
