<script setup>
defineProps({
    message: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <transition
        enter-active-class="transform transition duration-300 ease-out"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="message"
            class="fixed bottom-10 right-10 z-[100] min-w-[320px] rounded-2xl border border-slate-100 border-l-4 border-emerald-500 bg-white p-4 shadow-2xl dark:border-dark-border dark:bg-dark-card"
            role="status"
            aria-live="polite"
        >
            <div class="flex items-center gap-4">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-500 dark:bg-emerald-500/10">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black uppercase tracking-widest text-emerald-500">Thành công</p>
                    <p class="text-xs font-bold text-main-text dark:text-white">{{ message }}</p>
                </div>
            </div>
        </div>
    </transition>
</template>
