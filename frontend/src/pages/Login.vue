<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useLanguage } from '../services/translations';
import api from '../services/api';

const { currentLang, t } = useLanguage();
const router = useRouter();

const nicknameInput = ref('');
const isSubmitting = ref(false);
const errorMsg = ref('');

const handleLogin = async () => {
  const nickname = nicknameInput.value.trim();
  if (!nickname) {
    errorMsg.value = t.value.login.errorEmpty;
    return;
  }

  isSubmitting.value = true;
  errorMsg.value = '';

  try {
    const response = await api.post('/login', {
      nickname: nickname
    });

    const { exists, user } = response.data;

    // Save nickname in localStorage
    localStorage.setItem('wellpilot_nickname', user.nickname);

    if (exists) {
      // Returning user: redirect to dashboard
      router.push('/dashboard');
    } else {
      // New user: redirect to assessment
      router.push('/assessment');
    }
  } catch (err) {
    console.error('Login error:', err);
    errorMsg.value = t.value.login.errorConnection;
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
  <div class="min-h-[70vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    
    <!-- Immersive background glow elements -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full filter blur-3xl animate-pulse"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-indigo-500/10 dark:bg-indigo-500/5 rounded-full filter blur-3xl animate-pulse"></div>

    <div class="max-w-md w-full space-y-8 bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 p-8 sm:p-10 rounded-3xl shadow-2xl backdrop-blur-md relative z-10 animate-fade-in">
      
      <!-- Brand Header -->
      <div class="text-center space-y-3">
        <div class="inline-flex items-center justify-center p-3 rounded-2xl bg-emerald-500/10 text-emerald-500 mb-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
        <h2 class="text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight">
          {{ t.login.title }}
        </h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed max-w-xs mx-auto">
          {{ t.login.subtitle }}
        </p>
      </div>

      <!-- Form -->
      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        
        <div class="space-y-2 text-left">
          <label for="nickname" class="text-xs font-extrabold uppercase tracking-widest text-zinc-400">
            {{ t.login.nicknameLabel }}
          </label>
          <input 
            id="nickname" 
            name="nickname" 
            type="text" 
            required 
            v-model="nicknameInput"
            class="w-full px-5 py-4 rounded-2xl bg-zinc-50 dark:bg-zinc-950/80 border border-zinc-200 dark:border-zinc-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 transition font-medium text-zinc-900 dark:text-white"
            :placeholder="t.login.nicknamePlh"
            :disabled="isSubmitting"
          />
        </div>

        <!-- Error Alert -->
        <transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="transform -translate-y-2 opacity-0"
          enter-to-class="transform translate-y-0 opacity-100"
        >
          <div v-if="errorMsg" class="p-4 rounded-xl border border-rose-200/50 bg-rose-50/50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 text-sm font-semibold flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span class="text-left">{{ errorMsg }}</span>
          </div>
        </transition>

        <div>
          <button 
            type="submit" 
            :disabled="isSubmitting || !nicknameInput.trim()"
            class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold tracking-wide shadow-lg hover:shadow-emerald-500/20 hover:scale-[1.01] active:scale-95 transition-all duration-200 disabled:opacity-50 flex items-center justify-center space-x-2 cursor-pointer"
          >
            <span v-if="isSubmitting">{{ t.login.loading }}</span>
            <span v-else>{{ t.login.enterBtn }}</span>
            <svg v-if="!isSubmitting" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </button>
        </div>

      </form>

    </div>
  </div>
</template>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.6s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
