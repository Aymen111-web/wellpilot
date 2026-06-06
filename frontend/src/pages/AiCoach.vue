<script setup>
import { ref, onMounted, nextTick, watch, onUnmounted } from 'vue';
import { useLanguage, translations } from '../services/translations';
import api, { backendUrl } from '../services/api';

const { currentLang, t } = useLanguage();

// Chat states
const messages = ref([]);
const questionInput = ref('');
const storedNickname = ref(localStorage.getItem('wellpilot_nickname') || '');
const isSending = ref(false);
const chatScrollContainer = ref(null);
const isConfigured = ref(true); // default to true, then check API config state

// Audio Synthesis states
const isSpeaking = ref(false);
const speakingIndex = ref(null);
const hasSpeechSynthesis = ref(false);
let synth = null;
let currentUtterance = null;
let activeTtsAudio = null;
let ttsAudio = null;

const checkConfigStatus = async () => {
  try {
    const response = await api.get('/ai-coach/status');
    isConfigured.value = response.data.configured;
  } catch (err) {
    console.error('Error checking API key config:', err);
  }
};

// Speech Recognition (Voice Input) states & handlers
const isRecording = ref(false);
let recognition = null;

const initializeSpeechRecognition = () => {
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (!SpeechRecognition) return;

  recognition = new SpeechRecognition();
  recognition.continuous = false;
  recognition.interimResults = false;

  if (currentLang.value === 'am') {
    recognition.lang = 'am-ET'; // Amharic dictation
  } else {
    recognition.lang = 'en-US'; // English dictation
  }

  recognition.onstart = () => {
    isRecording.value = true;
  };

  recognition.onend = () => {
    isRecording.value = false;
  };

  recognition.onerror = (event) => {
    console.error('Speech recognition error:', event.error);
    isRecording.value = false;
  };

  recognition.onresult = (event) => {
    const transcript = event.results[0][0].transcript;
    questionInput.value = transcript;
    
    // Auto-send the transcribed query after a small delay!
    setTimeout(() => {
      sendChatMessage();
    }, 500);
  };
};

const toggleVoiceInput = () => {
  if (!recognition) {
    initializeSpeechRecognition();
  }

  if (!recognition) {
    alert(currentLang.value === 'en' 
      ? 'Speech recognition is not supported in this browser. Please try Chrome, Edge, or Safari.' 
      : 'ይህ ብሮውዘር በድምፅ መናገርን አይደግፍም። እባክዎን በChrome፣ Edge ወይም Safari ይሞክሩ።');
    return;
  }

  if (isRecording.value) {
    recognition.stop();
  } else {
    recognition.lang = currentLang.value === 'am' ? 'am-ET' : 'en-US';
    recognition.start();
  }
};

onMounted(() => {
  checkConfigStatus();
  // Initialize speech synthesis
  if (typeof window !== 'undefined' && 'speechSynthesis' in window) {
    synth = window.speechSynthesis;
    hasSpeechSynthesis.value = true;
  }

  // Set up default greeting message from AI Coach
  messages.value.push({
    sender: 'ai',
    text: currentLang.value === 'en'
      ? `Hello ${storedNickname.value ? storedNickname.value : 'friend'}! ` + translations.en.aiCoach.welcomeMsg
      : `ሰላም ${storedNickname.value ? storedNickname.value : 'ወዳጄ'}! ` + translations.am.aiCoach.welcomeMsg,
    timestamp: new Date()
  });
});

const scrollToBottom = async () => {
  await nextTick();
  if (chatScrollContainer.value) {
    chatScrollContainer.value.scrollTop = chatScrollContainer.value.scrollHeight;
  }
};

const formatMessageText = (text) => {
  if (!text) return '';
  // Basic markdown parser for bold styling (**text**)
  let html = text.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-zinc-950 dark:text-white">$1</strong>');
  // Formats bullet points
  html = html.replace(/^\s*-\s*(.*?)$/gm, '<li class="ml-4 list-disc">$1</li>');
  html = html.replace(/^\s*\d+\.\s*(.*?)$/gm, '<li class="ml-4 list-decimal">$1</li>');
  // Formats new lines
  html = html.replace(/\n/g, '<br/>');
  return html;
};

const sendChatMessage = async () => {
  const query = questionInput.value.trim();
  if (!query || isSending.value) return;

  // Add User Message
  messages.value.push({
    sender: 'user',
    text: query,
    timestamp: new Date()
  });

  questionInput.value = '';
  isSending.value = true;
  await scrollToBottom();

  try {
    const response = await api.post('/ai-coach', {
      question: query,
      lang: currentLang.value,
      nickname: storedNickname.value
    });

    if (response.data.offline !== undefined) {
      isConfigured.value = !response.data.offline;
    }

    // Add AI Message
    messages.value.push({
      sender: 'ai',
      text: response.data.response,
      timestamp: new Date()
    });

  } catch (err) {
    console.error('AI Coach communication error:', err);
    messages.value.push({
      sender: 'ai',
      text: currentLang.value === 'en'
        ? 'My apologies. I encountered a connection issue. Please verify that the Laravel backend is serving correctly.'
        : 'ይቅርታ እንጠይቃለን። የጀርባ አገልግሎት (backend) ግንኙነት ችግር አጋጥሞኛል። እባክዎን የLaravel አገልግሎት በትክክል መስራቱን ያረጋግጡ።',
      timestamp: new Date()
    });
  } finally {
    isSending.value = false;
    await scrollToBottom();
  }
};

const cleanTextForTts = (text) => {
  if (!text) return '';
  let clean = text;
  
  // Remove developer notices (both English and Amharic)
  clean = clean.replace(/\*\(Note:.*?\)\*/gi, '');
  clean = clean.replace(/\*\(ማሳሰቢያ[:፡].*?\)\*/gi, '');
  
  // Remove markdown symbols and bullet points
  clean = clean.replace(/\*\*/g, '');
  clean = clean.replace(/\*/g, '');
  clean = clean.replace(/-\s+/g, '');
  clean = clean.replace(/^\s*\d+\.\s+/gm, '');
  
  // Remove emojis
  clean = clean.replace(/[\u{1F300}-\u{1F9FF}]/gu, '');
  clean = clean.replace(/[\u{2700}-\u{27BF}]/gu, '');
  clean = clean.replace(/[\u{2600}-\u{26FF}]/gu, '');
  clean = clean.replace(/[\u{1F600}-\u{1F64F}]/gu, '');
  clean = clean.replace(/[\u{1F680}-\u{1F6FF}]/gu, '');
  
  // Normalize whitespace
  clean = clean.replace(/\s+/g, ' ').trim();
  return clean;
};

const speakAILoud = (text, index) => {
  if (isSpeaking.value) {
    stopSpeaking();
    if (speakingIndex.value === index) return;
  }

  const cleanText = cleanTextForTts(text);
  const encodedText = encodeURIComponent(cleanText);
  const ttsUrl = `${backendUrl}/api/ai-coach/tts?text=${encodedText}&lang=${currentLang.value}`;
  
  activeTtsAudio = new Audio(ttsUrl);
  isSpeaking.value = true;
  speakingIndex.value = index;
  
  activeTtsAudio.onended = () => {
    isSpeaking.value = false;
    speakingIndex.value = null;
    activeTtsAudio = null;
  };
  
  activeTtsAudio.onerror = () => {
    isSpeaking.value = false;
    speakingIndex.value = null;
    activeTtsAudio = null;
  };
  
  activeTtsAudio.play().catch(err => {
    console.error('Failed to play audio:', err);
    isSpeaking.value = false;
    speakingIndex.value = null;
    activeTtsAudio = null;
  });
};

const stopSpeaking = () => {
  if (activeTtsAudio) {
    activeTtsAudio.pause();
    activeTtsAudio = null;
  }
  isSpeaking.value = false;
  speakingIndex.value = null;
};

// Continuous Voice Session States
const isVoiceModeActive = ref(false);
const voiceSessionStatus = ref('idle'); // 'idle', 'listening', 'thinking', 'speaking', 'error'
const voiceTranscript = ref('');
const voiceAiResponse = ref('');
const voiceMuted = ref(false);
let continuousRecognition = null;

const initContinuousRecognition = () => {
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (!SpeechRecognition) return null;

  const rec = new SpeechRecognition();
  rec.continuous = false;
  rec.interimResults = false;
  rec.lang = currentLang.value === 'am' ? 'am-ET' : 'en-US';

  rec.onstart = () => {
    voiceSessionStatus.value = 'listening';
    voiceTranscript.value = '';
  };

  rec.onend = () => {
    if (isVoiceModeActive.value && voiceSessionStatus.value === 'listening' && !voiceMuted.value) {
      try {
        rec.start();
      } catch (err) {
        console.error('Failed to restart recognition:', err);
      }
    }
  };

  rec.onerror = (event) => {
    console.error('Continuous speech recognition error:', event.error);
    if (event.error === 'no-speech') {
      if (isVoiceModeActive.value && !voiceMuted.value) {
        setTimeout(() => {
          if (isVoiceModeActive.value && voiceSessionStatus.value === 'listening' && !voiceMuted.value) {
            try { rec.start(); } catch (e) {}
          }
        }, 100);
      }
      return;
    }
    voiceSessionStatus.value = 'idle';
  };

  rec.onresult = async (event) => {
    const transcript = event.results[0][0].transcript;
    voiceTranscript.value = transcript;
    voiceSessionStatus.value = 'thinking';
    
    try {
      rec.stop();
    } catch (e) {}

    messages.value.push({
      sender: 'user',
      text: transcript,
      timestamp: new Date()
    });
    await scrollToBottom();

    try {
      const response = await api.post('/ai-coach', {
        question: transcript,
        lang: currentLang.value,
        nickname: storedNickname.value
      });

      if (response.data.offline !== undefined) {
        isConfigured.value = !response.data.offline;
      }

      const aiReply = response.data.response;
      voiceAiResponse.value = aiReply;

      messages.value.push({
        sender: 'ai',
        text: aiReply,
        timestamp: new Date()
      });
      await scrollToBottom();

      if (voiceMuted.value) {
        voiceSessionStatus.value = 'idle';
      } else {
        speakVoiceResponse(aiReply);
      }
    } catch (err) {
      console.error('Voice mode API error:', err);
      voiceSessionStatus.value = 'error';
      speakVoiceResponse(currentLang.value === 'en' ? 'Sorry, I had an issue connecting.' : 'ይቅርታ፣ የግንኙነት ስህተት አጋጥሞኛል።');
    }
  };

  return rec;
};

const speakVoiceResponse = (text) => {
  if (ttsAudio) {
    ttsAudio.pause();
    ttsAudio = null;
  }

  const cleanText = cleanTextForTts(text);
  const encodedText = encodeURIComponent(cleanText);
  const ttsUrl = `${backendUrl}/api/ai-coach/tts?text=${encodedText}&lang=${currentLang.value}`;
  
  voiceSessionStatus.value = 'speaking';
  
  ttsAudio = new Audio(ttsUrl);
  ttsAudio.onended = () => {
    if (isVoiceModeActive.value && !voiceMuted.value) {
      voiceSessionStatus.value = 'listening';
      startListeningLoop();
    } else {
      voiceSessionStatus.value = 'idle';
    }
  };
  
  ttsAudio.onerror = (e) => {
    console.error('TTS playback error:', e);
    if (isVoiceModeActive.value && !voiceMuted.value) {
      voiceSessionStatus.value = 'listening';
      startListeningLoop();
    } else {
      voiceSessionStatus.value = 'idle';
    }
  };
  
  ttsAudio.play().catch(err => {
    console.error('Failed to play audio:', err);
    if (isVoiceModeActive.value && !voiceMuted.value) {
      voiceSessionStatus.value = 'listening';
      startListeningLoop();
    } else {
      voiceSessionStatus.value = 'idle';
    }
  });
};

const startVoiceSession = () => {
  isVoiceModeActive.value = true;
  voiceSessionStatus.value = 'listening';
  
  if (isRecording.value && recognition) {
    recognition.stop();
  }

  if (ttsAudio) {
    ttsAudio.pause();
    ttsAudio = null;
  }

  if (!continuousRecognition) {
    continuousRecognition = initContinuousRecognition();
  }

  if (!continuousRecognition) {
    alert(currentLang.value === 'en' 
      ? 'Voice recognition is not supported in this browser. Please try Chrome, Edge, or Safari.' 
      : 'የድምፅ ግብዓት በዚህ ብሮውዘር አይደገፍም። እባክዎን በChrome፣ Edge ወይም Safari ይሞክሩ።');
    isVoiceModeActive.value = false;
    return;
  }

  continuousRecognition.lang = currentLang.value === 'am' ? 'am-ET' : 'en-US';
  startListeningLoop();
};

const startListeningLoop = () => {
  if (!continuousRecognition) return;
  try {
    continuousRecognition.start();
  } catch (err) {
    console.warn('Recognition start attempted:', err);
  }
};

const stopListeningLoop = () => {
  if (continuousRecognition) {
    try {
      continuousRecognition.stop();
    } catch (e) {}
  }
};

const stopVoiceSession = () => {
  isVoiceModeActive.value = false;
  voiceSessionStatus.value = 'idle';
  stopListeningLoop();
  if (ttsAudio) {
    ttsAudio.pause();
    ttsAudio = null;
  }
};

const toggleVoiceMute = () => {
  voiceMuted.value = !voiceMuted.value;
  if (voiceMuted.value) {
    stopListeningLoop();
    if (ttsAudio) {
      ttsAudio.pause();
      ttsAudio = null;
    }
    voiceSessionStatus.value = 'idle';
  } else {
    voiceSessionStatus.value = 'listening';
    startListeningLoop();
  }
};

watch(currentLang, (newLang) => {
  if (continuousRecognition) {
    continuousRecognition.lang = newLang === 'am' ? 'am-ET' : 'en-US';
  }
});

onUnmounted(() => {
  stopListeningLoop();
  if (activeTtsAudio) {
    activeTtsAudio.pause();
    activeTtsAudio = null;
  }
  if (ttsAudio) {
    ttsAudio.pause();
    ttsAudio = null;
  }
});

</script>

<template>
  <div class="max-w-4xl mx-auto space-y-6 flex flex-col h-[80vh] animate-fade-in">
    
    <!-- Title Section -->
    <div class="text-center max-w-xl mx-auto space-y-1 flex-shrink-0">
      <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-white">
        {{ t.aiCoach.title }}
      </h1>
      <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
        {{ t.aiCoach.subtitle }}
      </p>
      <div class="w-16 h-1 bg-gradient-to-r from-emerald-500 to-indigo-500 mx-auto rounded-full mt-2"></div>
    </div>

    <!-- Chat Container -->
    <div class="flex-grow bg-white dark:bg-zinc-900/60 border border-zinc-200/50 dark:border-zinc-800/50 rounded-3xl p-4 sm:p-6 shadow-2xl flex flex-col justify-between overflow-hidden relative backdrop-blur-md">
      
      <!-- Backdrop glow background -->
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-emerald-500/5 rounded-full filter blur-3xl pointer-events-none"></div>

      <!-- Chat Header (Always Visible) -->
      <div class="flex items-center justify-between border-b border-zinc-200/30 dark:border-zinc-800/30 pb-3 mb-4 flex-shrink-0 z-10">
        <div class="flex items-center space-x-2">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="text-[10px] font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
            {{ currentLang === 'en' ? 'AI Wellness Coach' : 'የኤአይ ጤና አማካሪ' }}
          </span>
        </div>
        
        <!-- Premium Sliding Pill Toggle Switcher -->
        <div class="bg-zinc-100 dark:bg-zinc-950 p-0.5 rounded-full flex border border-zinc-200/50 dark:border-zinc-800/50 shadow-sm relative overflow-hidden h-8 items-center">
          <!-- Active Indicator Pill sliding background -->
          <div 
            class="absolute top-0.5 bottom-0.5 left-0.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 shadow transition-all duration-300"
            :style="{
              width: 'calc(50% - 0.25rem)',
              transform: isVoiceModeActive ? 'translateX(100%)' : 'translateX(0)'
            }"
          ></div>
          
          <button 
            type="button"
            @click="stopVoiceSession"
            class="relative z-10 px-3.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider transition-colors duration-300 cursor-pointer h-full flex items-center justify-center"
            :class="!isVoiceModeActive ? 'text-white' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300'"
          >
            {{ currentLang === 'en' ? 'Text Chat' : 'ጽሑፍ ውይይት' }}
          </button>
          <button 
            type="button"
            @click="startVoiceSession"
            class="relative z-10 px-3.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider transition-colors duration-300 cursor-pointer h-full flex items-center justify-center"
            :class="isVoiceModeActive ? 'text-white' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300'"
          >
            {{ currentLang === 'en' ? 'Live Session' : 'የቀጥታ ድምፅ' }}
          </button>
        </div>
      </div>

      <!-- Immersive Live Voice Mode Interface -->
      <div v-if="isVoiceModeActive" class="flex-grow flex flex-col justify-between items-center py-4 relative z-10 overflow-hidden min-h-[50vh]">
        
        <!-- Top Status Indicator -->
        <div class="text-center space-y-1">
          <span 
            class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-widest shadow-sm transition-all duration-500"
            :class="{
              'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20': voiceSessionStatus === 'listening',
              'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20': voiceSessionStatus === 'thinking',
              'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20': voiceSessionStatus === 'speaking',
              'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border border-zinc-500/20': voiceSessionStatus === 'idle' || voiceMuted,
              'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20': voiceSessionStatus === 'error'
            }"
          >
            <span v-if="voiceSessionStatus === 'listening'">{{ t.aiCoach.voiceListening }}</span>
            <span v-else-if="voiceSessionStatus === 'thinking'">{{ t.aiCoach.voiceThinking }}</span>
            <span v-else-if="voiceSessionStatus === 'speaking'">{{ t.aiCoach.voiceSpeaking }}</span>
            <span v-else-if="voiceSessionStatus === 'idle'">{{ t.aiCoach.voiceIdle }}</span>
            <span v-else-if="voiceSessionStatus === 'error'">{{ t.aiCoach.voiceError }}</span>
          </span>
        </div>

        <!-- Middle Pulse Orb Section -->
        <div class="my-6 relative flex items-center justify-center cursor-pointer" @click="toggleVoiceMute">
          
          <!-- Outer dynamic radiating ripple rings -->
          <div 
            class="absolute rounded-full filter blur-md opacity-20 transition-all duration-1000"
            :class="{
              'w-64 h-64 bg-emerald-500 animate-ping': voiceSessionStatus === 'listening' && !voiceMuted,
              'w-64 h-64 bg-indigo-500 animate-pulse': voiceSessionStatus === 'thinking',
              'w-72 h-72 bg-amber-500 animate-ping': voiceSessionStatus === 'speaking',
              'w-48 h-48 bg-zinc-500': voiceSessionStatus === 'idle' || voiceMuted,
              'w-64 h-64 bg-rose-500 animate-bounce': voiceSessionStatus === 'error'
            }"
          ></div>
          
          <div 
            class="absolute rounded-full opacity-10 transition-all duration-700"
            :class="{
              'w-48 h-48 bg-emerald-400 animate-pulse': voiceSessionStatus === 'listening' && !voiceMuted,
              'w-48 h-48 bg-indigo-400 animate-spin': voiceSessionStatus === 'thinking',
              'w-56 h-56 bg-amber-400 animate-pulse': voiceSessionStatus === 'speaking',
              'w-40 h-40 bg-zinc-400': voiceSessionStatus === 'idle' || voiceMuted
            }"
            style="animation-duration: 4s"
          ></div>

          <!-- Main Core Orb -->
          <div 
            class="w-36 h-36 rounded-full flex flex-col items-center justify-center shadow-2xl transition-all duration-500 relative border border-white/20 dark:border-zinc-800/50"
            :class="{
              'bg-gradient-to-tr from-emerald-600 to-teal-400 hover:scale-105': voiceSessionStatus === 'listening' && !voiceMuted,
              'bg-gradient-to-tr from-indigo-600 to-purple-400 scale-95': voiceSessionStatus === 'thinking',
              'bg-gradient-to-tr from-amber-600 to-orange-400 scale-110': voiceSessionStatus === 'speaking',
              'bg-gradient-to-tr from-zinc-600 to-slate-400 hover:scale-105': voiceSessionStatus === 'idle' || voiceMuted,
              'bg-gradient-to-tr from-rose-600 to-pink-500 scale-100': voiceSessionStatus === 'error'
            }"
          >
            <!-- Icons inside Core -->
            <svg v-if="voiceSessionStatus === 'listening' && !voiceMuted" xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
            <svg v-else-if="voiceSessionStatus === 'thinking'" xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5" />
            </svg>
            <svg v-else-if="voiceSessionStatus === 'speaking'" xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
            </svg>
            <svg v-else-if="voiceMuted" xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            
            <span class="text-[9px] text-white/80 font-bold uppercase tracking-wider mt-1">
              {{ voiceMuted ? t.aiCoach.voiceMuted : (voiceSessionStatus === 'listening' ? 'Listening' : (voiceSessionStatus === 'speaking' ? 'Speaking' : 'Mute')) }}
            </span>
          </div>

        </div>

        <!-- Bottom Transcript / Live subtitle view section -->
        <div class="w-full max-w-lg bg-zinc-50 dark:bg-zinc-950/40 rounded-2xl p-4 sm:p-5 border border-zinc-200/50 dark:border-zinc-800/40 backdrop-blur text-center space-y-3 min-h-[140px] flex flex-col justify-center animate-fade-in">
          
          <div v-if="voiceTranscript" class="space-y-1">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
              {{ currentLang === 'en' ? 'You said' : 'እርስዎ ያሉትን' }}
            </span>
            <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 italic">
              "{{ voiceTranscript }}"
            </p>
          </div>

          <div v-if="voiceAiResponse" class="space-y-1 transition-all duration-500">
            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-500 dark:text-amber-400">
              {{ currentLang === 'en' ? 'Coach reply' : 'የአማካሪው ምላሽ' }}
            </span>
            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 max-h-[80px] overflow-y-auto line-clamp-3">
              {{ voiceAiResponse }}
            </p>
          </div>

          <div v-if="!voiceTranscript && !voiceAiResponse" class="text-zinc-400 text-xs italic animate-pulse">
            {{ currentLang === 'en' ? 'Start speaking to begin your live wellness guide...' : 'የቀጥታ የጤና አማካሪዎን ለማግኘት መናገር ይጀምሩ...' }}
          </div>
          
        </div>

        <!-- Controls Footer -->
        <div class="flex items-center justify-center space-x-3 mt-4">
          <button 
            @click="toggleVoiceMute"
            class="px-4 py-2.5 rounded-full border text-xs font-bold flex items-center space-x-2 transition-all duration-300 cursor-pointer"
            :class="voiceMuted 
              ? 'bg-rose-500 border-rose-500 text-white shadow-lg' 
              : 'bg-zinc-100 dark:bg-zinc-800/50 border-zinc-200 dark:border-zinc-700/50 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-800'"
          >
            <svg v-if="voiceMuted" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
            </svg>
            <span>{{ voiceMuted ? t.aiCoach.voiceUnmuted : t.aiCoach.voiceMuted }}</span>
          </button>

          <button 
            @click="stopVoiceSession"
            class="px-5 py-2.5 rounded-full bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs shadow-md hover:shadow-rose-500/20 hover:scale-105 active:scale-95 transition-all cursor-pointer"
          >
            {{ t.aiCoach.exitVoiceModeBtn }}
          </button>
        </div>

      </div>

      <!-- Messages View List -->
      <div v-if="!isVoiceModeActive" ref="chatScrollContainer" class="flex-grow overflow-y-auto space-y-6 pr-2 mb-4 scroll-smooth">
        <div v-for="(msg, index) in messages" :key="index" class="flex flex-col" :class="msg.sender === 'user' ? 'items-end' : 'items-start'">
          
          <!-- Avatar Icon and Meta -->
          <div class="flex items-center space-x-2 text-xs text-zinc-400 font-semibold mb-1" :class="msg.sender === 'user' ? 'flex-row-reverse space-x-reverse' : ''">
            <span class="capitalize">{{ msg.sender === 'user' ? 'You' : 'AI Wellness Coach' }}</span>
            <span>•</span>
            <span>{{ msg.timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
          </div>

          <!-- Message bubble -->
          <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl px-5 py-3.5 shadow-sm text-sm leading-relaxed whitespace-pre-line relative"
               :class="msg.sender === 'user' 
                 ? 'bg-emerald-500 text-white rounded-tr-none' 
                 : 'bg-zinc-100 dark:bg-zinc-950/70 border border-zinc-200/30 dark:border-zinc-800/30 text-zinc-800 dark:text-zinc-200 rounded-tl-none'">
            
            <div v-html="formatMessageText(msg.text)" class="space-y-1"></div>

            <!-- Speak Voice Switcher (Only on AI messages - unified high quality TTS) -->
            <div v-if="msg.sender === 'ai'" class="mt-3.5 border-t border-zinc-200/50 dark:border-zinc-800/50 pt-2 flex items-center justify-between">
              <!-- Audio Waveform Visualization while speaking -->
              <div v-if="isSpeaking && speakingIndex === index" class="flex items-center space-x-1 text-emerald-500">
                <span class="w-1 h-3.5 bg-emerald-500 rounded animate-bounce" style="animation-delay: 0.1s"></span>
                <span class="w-1 h-5 bg-emerald-500 rounded animate-bounce" style="animation-delay: 0.3s"></span>
                <span class="w-1 h-2.5 bg-emerald-500 rounded animate-bounce" style="animation-delay: 0.5s"></span>
                <span class="text-[10px] font-bold uppercase ml-1 animate-pulse">{{ t.aiCoach.voiceStopBtn }}</span>
              </div>
              <div v-else></div>

              <button 
                @click="speakAILoud(msg.text, index)"
                class="px-3 py-1 rounded-full bg-zinc-200/70 dark:bg-zinc-800/70 text-[10px] font-bold text-zinc-600 dark:text-zinc-400 hover:text-emerald-500 dark:hover:text-emerald-400 hover:bg-zinc-200 dark:hover:bg-zinc-800 transition"
              >
                {{ isSpeaking && speakingIndex === index ? t.aiCoach.voiceStopBtn : t.aiCoach.speakBtn }}
              </button>
            </div>

          </div>
        </div>

        <!-- Typing state indicators -->
        <div v-if="isSending" class="flex flex-col items-start">
          <div class="flex items-center space-x-1.5 p-3 rounded-2xl bg-zinc-100 dark:bg-zinc-950/70 border border-zinc-200/30 dark:border-zinc-800/30 text-zinc-400 rounded-tl-none">
            <span class="w-2 h-2 bg-zinc-400 dark:bg-zinc-600 rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
            <span class="w-2 h-2 bg-zinc-400 dark:bg-zinc-600 rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
            <span class="w-2 h-2 bg-zinc-400 dark:bg-zinc-600 rounded-full animate-bounce" style="animation-delay: 0.5s"></span>
          </div>
        </div>

      </div>

      <!-- Chat Form Input Block -->
      <div v-if="!isVoiceModeActive" class="border-t border-zinc-200/40 dark:border-zinc-800/40 pt-4 flex-shrink-0">
        
        <!-- Offline fallback message warning -->
        <div v-if="!isConfigured" class="mb-3 text-[10px] text-center text-zinc-400 font-medium">
          {{ t.aiCoach.offlineNotice }}
        </div>

        <form @submit.prevent="sendChatMessage" class="flex items-center space-x-2">
          
          <!-- Microphone Voice Button -->
          <button 
            type="button"
            @click="toggleVoiceInput"
            class="p-3.5 rounded-full border transition-all duration-300 flex items-center justify-center cursor-pointer flex-shrink-0"
            :class="isRecording 
              ? 'bg-rose-500 border-rose-500 text-white animate-pulse shadow-lg shadow-rose-500/20' 
              : 'bg-zinc-100 dark:bg-zinc-950/80 border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 hover:text-emerald-500 dark:hover:text-emerald-400 hover:border-emerald-500/50'"
            title="Voice Input"
          >
            <!-- Microphone Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
          </button>

          <input 
            type="text" 
            v-model="questionInput" 
            :disabled="isSending"
            class="flex-grow px-5 py-3.5 rounded-full bg-zinc-100 dark:bg-zinc-950/80 border border-zinc-200 dark:border-zinc-800 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/50 disabled:opacity-50 transition" 
            :placeholder="t.aiCoach.placeholder" 
          />
          <button 
            type="submit" 
            :disabled="!questionInput.trim() || isSending"
            class="px-6 py-3.5 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-sm shadow-md hover:shadow-emerald-500/20 disabled:opacity-50 hover:scale-105 active:scale-95 transition-all cursor-pointer"
          >
            {{ t.aiCoach.sendBtn }}
          </button>
        </form>

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
