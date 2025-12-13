<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

// Configuration (in milliseconds)
const INACTIVITY_TIMEOUT = 10 * 1000; // 10 seconds for testing
const WARNING_DURATION = 60; // 60 seconds countdown

// State
const showWarning = ref(false);
const countdown = ref(WARNING_DURATION);
const timeRemaining = ref(INACTIVITY_TIMEOUT / 1000); // Time until warning in seconds
let inactivityTimer = null;
let countdownInterval = null;
let displayInterval = null;

// Reset the inactivity timer
function resetTimer() {
    console.log('[SessionTimeout] Timer reset');
    
    // Clear existing timers
    if (inactivityTimer) clearTimeout(inactivityTimer);
    if (countdownInterval) clearInterval(countdownInterval);
    if (displayInterval) clearInterval(displayInterval);
    
    // Hide warning if showing
    showWarning.value = false;
    countdown.value = WARNING_DURATION;
    timeRemaining.value = INACTIVITY_TIMEOUT / 1000;
    
    // Start new inactivity timer
    const startTime = Date.now();
    inactivityTimer = setTimeout(() => {
        console.log('[SessionTimeout] Inactivity timeout reached, showing warning');
        showWarningModal();
    }, INACTIVITY_TIMEOUT);
    
    // Update display every second
    displayInterval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        const remaining = Math.max(0, Math.ceil((INACTIVITY_TIMEOUT - elapsed) / 1000));
        timeRemaining.value = remaining;
    }, 1000);
    
    console.log('[SessionTimeout] Next warning in', INACTIVITY_TIMEOUT / 1000, 'seconds');
}

// Show warning modal and start countdown
function showWarningModal() {
    showWarning.value = true;
    countdown.value = WARNING_DURATION;
    
    // Start countdown
    countdownInterval = setInterval(() => {
        countdown.value--;
        
        if (countdown.value <= 0) {
            logout();
        }
    }, 1000);
}

// Extend session
function extendSession() {
    resetTimer();
}

// Logout user
function logout() {
    if (inactivityTimer) clearTimeout(inactivityTimer);
    if (countdownInterval) clearInterval(countdownInterval);
    
    router.post(route('logout'));
}

// Format countdown time
const formattedTime = computed(() => {
    const minutes = Math.floor(countdown.value / 60);
    const seconds = countdown.value % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
});

// Activity events to monitor
const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

// Setup activity listeners
onMounted(() => {
    console.log('[SessionTimeout] Component mounted, setting up listeners');
    
    // Add event listeners for user activity
    activityEvents.forEach(event => {
        document.addEventListener(event, resetTimer, true);
    });
    
    // Start initial timer
    resetTimer();
    
    console.log('[SessionTimeout] Monitoring events:', activityEvents);
});

// Cleanup on unmount
onUnmounted(() => {
    console.log('[SessionTimeout] Component unmounting, cleaning up');
    
    // Remove event listeners
    activityEvents.forEach(event => {
        document.removeEventListener(event, resetTimer, true);
    });
    
    // Clear timers
    if (inactivityTimer) clearTimeout(inactivityTimer);
    if (countdownInterval) clearInterval(countdownInterval);
    if (displayInterval) clearInterval(displayInterval);
});
</script>

<template>
    <!-- Session Timer Display (always visible) -->
    <div class="fixed bottom-4 right-4 z-40">
        <div class="rounded-lg border border-gray-200 bg-white px-4 py-2 shadow-lg dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Sesión: 
                    <span :class="timeRemaining <= 3 ? 'text-red-600 dark:text-red-400 font-bold' : 'text-gray-900 dark:text-gray-100'">
                        {{ timeRemaining }}s
                    </span>
                </span>
            </div>
        </div>
    </div>

    <!-- Warning Modal -->
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="showWarning" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl border border-amber-200 bg-white p-6 shadow-xl dark:border-amber-900/50 dark:bg-gray-800">
                <div class="flex items-start gap-4">
                    <!-- Warning Icon -->
                    <div class="flex-shrink-0 rounded-full bg-amber-100 p-3 dark:bg-amber-900/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Sesión por expirar
                        </h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Su sesión se cerrará automáticamente por inactividad.
                        </p>
                        
                        <!-- Countdown Timer -->
                        <div class="mt-4 flex items-center justify-center">
                            <div class="relative">
                                <!-- Circular Progress -->
                                <svg class="h-24 w-24 -rotate-90 transform">
                                    <circle
                                        cx="48"
                                        cy="48"
                                        r="40"
                                        stroke="currentColor"
                                        stroke-width="8"
                                        fill="none"
                                        class="text-gray-200 dark:text-gray-700"
                                    />
                                    <circle
                                        cx="48"
                                        cy="48"
                                        r="40"
                                        stroke="currentColor"
                                        stroke-width="8"
                                        fill="none"
                                        :stroke-dasharray="251.2"
                                        :stroke-dashoffset="251.2 * (1 - countdown / WARNING_DURATION)"
                                        class="text-amber-500 transition-all duration-1000 dark:text-amber-400"
                                        stroke-linecap="round"
                                    />
                                </svg>
                                
                                <!-- Timer Text -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                                        {{ formattedTime }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="mt-6 flex gap-3">
                            <button
                                @click="logout"
                                type="button"
                                class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                            >
                                Cerrar sesión
                            </button>
                            <button
                                @click="extendSession"
                                type="button"
                                class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
                            >
                                Continuar sesión
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>
