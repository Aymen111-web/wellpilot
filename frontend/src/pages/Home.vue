<script setup>
import { RouterLink } from 'vue-router';
import { useLanguage } from '../services/translations';

const { currentLang, t } = useLanguage();
</script>

<template>
  <div class="space-y-16 animate-fade-in">
    
    <!-- Hero Banner Section -->
    <section class="relative rounded-3xl overflow-hidden py-20 px-6 sm:px-12 text-center bg-gradient-to-br from-teal-900/10 via-emerald-950/5 to-indigo-950/15 border border-zinc-200/40 dark:border-zinc-800/40 shadow-2xl backdrop-blur-md">
      
      <!-- Backdrop glow effects -->
      <div class="absolute -top-12 -left-12 w-64 h-64 bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full filter blur-3xl animate-pulse"></div>
      <div class="absolute -bottom-16 -right-16 w-80 h-80 bg-indigo-500/10 dark:bg-indigo-500/5 rounded-full filter blur-3xl animate-pulse"></div>

      <div class="relative max-w-4xl mx-auto space-y-8">
        
        <!-- Premium Tag -->
        <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-widest border border-emerald-500/20">
          Wellness Tourism 2.0
        </span>

        <!-- Hero Title -->
        <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-zinc-900 dark:text-white leading-none">
          <span class="block mb-2 text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 via-teal-500 to-indigo-600 dark:from-emerald-400 dark:via-teal-300 dark:to-indigo-400">
            {{ t.home.heroTitle }}
          </span>
        </h1>

        <!-- Hero Subtitle -->
        <p class="text-lg sm:text-xl text-zinc-600 dark:text-zinc-300 max-w-2xl mx-auto leading-relaxed">
          {{ t.home.heroSubtitle }}
        </p>

        <!-- Dynamic Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
          <RouterLink to="/assessment" class="group relative px-8 py-4 w-full sm:w-auto rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-bold text-center shadow-lg hover:shadow-emerald-500/30 hover:scale-105 active:scale-95 transition-all duration-300 overflow-hidden cursor-pointer">
            <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-teal-500 to-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></span>
            <span class="relative flex items-center justify-center gap-2">
              {{ t.home.startAssessment }}
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:translate-x-1.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </span>
          </RouterLink>

          <RouterLink to="/dashboard" class="px-8 py-4 w-full sm:w-auto rounded-full bg-white dark:bg-zinc-900/80 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-800 font-bold text-center shadow-md hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:scale-105 active:scale-95 transition-all duration-300 cursor-pointer">
            {{ t.home.exploreDashboard }}
          </RouterLink>
        </div>

      </div>
    </section>

    <!-- Core Features Grid -->
    <section class="space-y-8">
      <div class="text-center max-w-2xl mx-auto space-y-2">
        <h2 class="text-3xl font-extrabold text-zinc-900 dark:text-white">
          {{ t.home.coreModules }}
        </h2>
        <div class="w-16 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 mx-auto rounded-full"></div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <!-- Feature 1: Assessment -->
        <div class="group relative rounded-2xl p-6 bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 shadow-lg hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 flex flex-col justify-between">
          <div class="space-y-4">
            <div class="h-12 w-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white group-hover:text-emerald-500 transition-colors">
              {{ t.nav.assessment }}
            </h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
              {{ t.home.assessmentDesc }}
            </p>
          </div>
          <RouterLink to="/assessment" class="inline-flex items-center text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-6 group-hover:underline">
            {{ t.home.startAssessment }} →
          </RouterLink>
        </div>

        <!-- Feature 2: AI Coach -->
        <div class="group relative rounded-2xl p-6 bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 shadow-lg hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 flex flex-col justify-between">
          <div class="space-y-4">
            <div class="h-12 w-12 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white group-hover:text-teal-500 transition-colors">
              {{ t.nav.aiCoach }}
            </h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
              {{ t.home.aiCoachDesc }}
            </p>
          </div>
          <RouterLink to="/ai-coach" class="inline-flex items-center text-xs font-bold text-teal-600 dark:text-teal-400 mt-6 group-hover:underline">
            {{ t.nav.aiCoach }} →
          </RouterLink>
        </div>

        <!-- Feature 3: Dashboard -->
        <div class="group relative rounded-2xl p-6 bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 shadow-lg hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 flex flex-col justify-between">
          <div class="space-y-4">
            <div class="h-12 w-12 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white group-hover:text-indigo-500 transition-colors">
              {{ t.nav.dashboard }}
            </h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
              {{ t.home.dashboardDesc }}
            </p>
          </div>
          <RouterLink to="/dashboard" class="inline-flex items-center text-xs font-bold text-indigo-600 dark:text-indigo-400 mt-6 group-hover:underline">
            {{ t.nav.dashboard }} →
          </RouterLink>
        </div>

        <!-- Feature 4: Resorts -->
        <div class="group relative rounded-2xl p-6 bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 shadow-lg hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 flex flex-col justify-between">
          <div class="space-y-4">
            <div class="h-12 w-12 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white group-hover:text-amber-500 transition-colors">
              {{ t.nav.resorts }}
            </h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
              {{ t.home.resortsDesc }}
            </p>
          </div>
          <RouterLink to="/resorts" class="inline-flex items-center text-xs font-bold text-amber-600 dark:text-amber-400 mt-6 group-hover:underline">
            {{ t.nav.resorts }} →
          </RouterLink>
        </div>

        <!-- Feature 5: Challenges -->
        <div class="group relative rounded-2xl p-6 bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 shadow-lg hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 flex flex-col justify-between">
          <div class="space-y-4">
            <div class="h-12 w-12 rounded-xl bg-violet-500/10 text-violet-600 dark:text-violet-400 flex items-center justify-center font-bold">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 0h4m-4 0H8m12 3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white group-hover:text-violet-500 transition-colors">
              {{ t.nav.challenges }}
            </h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
              {{ t.home.challengesDesc }}
            </p>
          </div>
          <RouterLink to="/challenges" class="inline-flex items-center text-xs font-bold text-violet-600 dark:text-violet-400 mt-6 group-hover:underline">
            {{ t.nav.challenges }} →
          </RouterLink>
        </div>

      </div>
    </section>

  </div>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.8s ease-out forwards;
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
