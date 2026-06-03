<script setup>
import { ref, onMounted, watch } from 'vue';
import { RouterLink, RouterView, useRouter } from 'vue-router';
import { useLanguage } from './services/translations';

const { currentLang, toggleLanguage, t } = useLanguage();
const router = useRouter();

// Dark mode state
const isDark = ref(localStorage.getItem('wellpilot_theme') !== 'light');

// Mobile menu toggle
const isMobileMenuOpen = ref(false);

const toggleTheme = () => {
  isDark.value = !isDark.value;
  localStorage.setItem('wellpilot_theme', isDark.value ? 'dark' : 'light');
  updateThemeClass();
};

const updateThemeClass = () => {
  if (isDark.value) {
    document.documentElement.classList.add('dark');
    document.documentElement.classList.remove('light');
  } else {
    document.documentElement.classList.remove('dark');
    document.documentElement.classList.add('light');
  }
};

onMounted(() => {
  updateThemeClass();
});

// Close mobile menu on route change
watch(() => router.currentRoute.value.path, () => {
  isMobileMenuOpen.value = false;
});
</script>

<template>
  <div class="min-h-screen bg-slate-50 dark:bg-zinc-950 text-zinc-700 dark:text-zinc-300 transition-colors duration-300 font-sans">
    
    <!-- Top Glassmorphic Navbar -->
    <header class="sticky top-0 z-50 w-full backdrop-blur-md bg-white/70 dark:bg-zinc-950/70 border-b border-zinc-200/50 dark:border-zinc-800/50 px-4 sm:px-6 lg:px-8 py-4 transition-all duration-300">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        
        <!-- Brand Logo -->
        <RouterLink to="/" class="flex items-center space-x-2 text-2xl font-bold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-emerald-500 via-teal-400 to-indigo-500 hover:opacity-90 transition-opacity">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-500 inline-block animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          <span class="font-extrabold tracking-wider">WellPilot</span>
        </RouterLink>

        <!-- Desktop Navigation Items -->
        <nav class="hidden md:flex space-x-1 lg:space-x-2 items-center bg-zinc-100/50 dark:bg-zinc-900/50 p-1 rounded-full border border-zinc-200/30 dark:border-zinc-800/30">
          <RouterLink to="/" class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300" active-class="bg-emerald-500 text-white shadow-md shadow-emerald-500/20">
            {{ t.nav.home }}
          </RouterLink>
          <RouterLink to="/assessment" class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300" active-class="bg-emerald-500 text-white shadow-md shadow-emerald-500/20">
            {{ t.nav.assessment }}
          </RouterLink>
          <RouterLink to="/dashboard" class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300" active-class="bg-emerald-500 text-white shadow-md shadow-emerald-500/20">
            {{ t.nav.dashboard }}
          </RouterLink>
          <RouterLink to="/ai-coach" class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300" active-class="bg-emerald-500 text-white shadow-md shadow-emerald-500/20">
            {{ t.nav.aiCoach }}
          </RouterLink>
          <RouterLink to="/resorts" class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300" active-class="bg-emerald-500 text-white shadow-md shadow-emerald-500/20">
            {{ t.nav.resorts }}
          </RouterLink>
          <RouterLink to="/challenges" class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300" active-class="bg-emerald-500 text-white shadow-md shadow-emerald-500/20">
            {{ t.nav.challenges }}
          </RouterLink>
        </nav>

        <!-- Right Side Toggles -->
        <div class="hidden md:flex items-center space-x-3">
          
          <!-- Language Toggle Button -->
          <button @click="toggleLanguage" class="flex items-center space-x-1 px-3 py-1.5 rounded-full border border-zinc-200 dark:border-zinc-800 text-xs font-semibold hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-all duration-300 text-teal-600 dark:text-teal-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 2.16-.57 4.21-1.571 6m-.724-4.135a1.86 1.86 0 01-1.002-1.2l-.012-.047" />
            </svg>
            <span>{{ t.nav.toggleLang }}</span>
          </button>

          <!-- Theme Switcher Button -->
          <button @click="toggleTheme" class="p-2 rounded-full border border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 transition-all duration-300">
            <!-- Sun Icon (Light Mode) -->
            <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
            </svg>
            <!-- Moon Icon (Dark Mode) -->
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>
        </div>

        <!-- Mobile Toggles & Menu Button -->
        <div class="flex md:hidden items-center space-x-2">
          
          <!-- Quick Language Switcher for Mobile -->
          <button @click="toggleLanguage" class="flex items-center space-x-1 px-2.5 py-1 rounded-full border border-zinc-200 dark:border-zinc-800 text-xs font-semibold text-teal-600 dark:text-teal-400">
            <span>{{ t.nav.toggleLang }}</span>
          </button>

          <!-- Quick Theme Switcher for Mobile -->
          <button @click="toggleTheme" class="p-1.5 rounded-full border border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400">
            <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>

          <!-- Hamburger Button -->
          <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="p-2 rounded-md border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 focus:outline-none transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path v-if="isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>

      </div>
    </header>

    <!-- Mobile Drawer Menu -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform -translate-y-4 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform -translate-y-4 opacity-0"
    >
      <div v-if="isMobileMenuOpen" class="md:hidden sticky top-[73px] z-40 w-full bg-white dark:bg-zinc-900 border-b border-zinc-200/50 dark:border-zinc-800/50 px-4 py-4 space-y-2 shadow-xl">
        <RouterLink to="/" class="block px-4 py-3 rounded-lg text-base font-semibold transition-all duration-200" active-class="bg-emerald-500 text-white shadow-md">
          {{ t.nav.home }}
        </RouterLink>
        <RouterLink to="/assessment" class="block px-4 py-3 rounded-lg text-base font-semibold transition-all duration-200" active-class="bg-emerald-500 text-white shadow-md">
          {{ t.nav.assessment }}
        </RouterLink>
        <RouterLink to="/dashboard" class="block px-4 py-3 rounded-lg text-base font-semibold transition-all duration-200" active-class="bg-emerald-500 text-white shadow-md">
          {{ t.nav.dashboard }}
        </RouterLink>
        <RouterLink to="/ai-coach" class="block px-4 py-3 rounded-lg text-base font-semibold transition-all duration-200" active-class="bg-emerald-500 text-white shadow-md">
          {{ t.nav.aiCoach }}
        </RouterLink>
        <RouterLink to="/resorts" class="block px-4 py-3 rounded-lg text-base font-semibold transition-all duration-200" active-class="bg-emerald-500 text-white shadow-md">
          {{ t.nav.resorts }}
        </RouterLink>
        <RouterLink to="/challenges" class="block px-4 py-3 rounded-lg text-base font-semibold transition-all duration-200" active-class="bg-emerald-500 text-white shadow-md">
          {{ t.nav.challenges }}
        </RouterLink>
      </div>
    </transition>

    <!-- Main Dynamic Content Container -->
    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 md:py-12">
      <RouterView v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </RouterView>
    </main>

    <!-- Glassmorphic Footer -->
    <footer class="mt-auto w-full border-t border-zinc-200/50 dark:border-zinc-800/50 bg-white/40 dark:bg-zinc-950/40 backdrop-blur-sm py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between text-sm text-zinc-500 dark:text-zinc-400 space-y-4 md:space-y-0">
        <div class="flex items-center space-x-2">
          <span class="font-bold text-zinc-800 dark:text-zinc-200">WellPilot</span>
          <span>© 2026. All rights reserved.</span>
        </div>
        <div class="flex space-x-6">
          <span>Heal. Build. Thrive.</span>
          <span class="text-emerald-500 font-semibold">Wellness Hackathon 2026</span>
        </div>
      </div>
    </footer>

  </div>
</template>

<style>
/* Smooth View Transition Animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Custom Scrollbar for premium feel */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(16, 185, 129, 0.2);
  border-radius: 9999px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(16, 185, 129, 0.4);
}
</style>
