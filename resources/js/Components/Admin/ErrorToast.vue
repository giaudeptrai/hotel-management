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
            class="fixed bottom-10 right-10 z-[101] min-w-[320px] rounded-2xl border border-rose-200 border-l-4 border-rose-500 bg-white p-4 shadow-2xl dark:border-rose-500/30 dark:bg-dark-card"
            role="alert"
            aria-live="assertive"
        >
            <div class="flex items-center gap-4">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-500 dark:bg-rose-500/10">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v3m0 3h.01m-7.5 3h15a1.5 1.5 0 001.299-2.25l-7.5-13a1.5 1.5 0 00-2.598 0l-7.5 13A1.5 1.5 0 004.5 18z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black uppercase tracking-widest text-rose-500">Có lỗi</p>
                    <p class="text-xs font-bold text-main-text dark:text-white">{{ message }}</p>
                </div>
            </div>
        </div>
    </transition>
</template>
