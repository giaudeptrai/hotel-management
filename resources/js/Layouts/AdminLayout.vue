<script setup>
import { ref } from 'vue';
import Sidebar from '@/Components/Admin/Sidebar.vue';
import Topbar from '@/Components/Admin/Topbar.vue';

// Biến trạng thái lưu trữ việc Sidebar đang đóng hay mở trên Mobile
const isSidebarOpen = ref(false);
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
        <div v-if="flashSuccess" class="fixed bottom-10 right-10 z-[100] max-w-sm w-full bg-white dark:bg-dark-card border-l-4 border-emerald-500 shadow-2xl rounded-2xl p-4 flex items-center gap-4 border border-slate-100 dark:border-dark-border">
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-500/10 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-black text-main-text dark:text-white uppercase tracking-wider">Thành công!</h3>
                <p class="text-xs text-muted-text font-medium">{{ flashSuccess }}</p>
            </div>
            <button @click="page.props.flash.success = null" class="text-slate-300 hover:text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </transition>   
    <div class="min-h-screen flex bg-surface dark:bg-dark-bg transition-colors duration-500 font-sans relative overflow-hidden lg:overflow-visible">
        
        <div v-if="isSidebarOpen" @click="isSidebarOpen = false" 
             class="fixed inset-0 bg-main-text/40 dark:bg-black/60 z-40 lg:hidden backdrop-blur-sm transition-opacity">
        </div>

        <Sidebar :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
                 class="lg:translate-x-0 transition-transform duration-300 ease-out z-50 shadow-2xl lg:shadow-none" />

        <div class="flex-1 lg:ml-64 flex flex-col min-h-screen w-full transition-all duration-300">
            <Topbar @toggle-sidebar="isSidebarOpen = !isSidebarOpen" />

            <main class="p-4 sm:p-8 lg:p-10">
                <Transition name="fade" mode="out-in">
                    <div :key="$page.url">
                        <slot />
                    </div>
                </Transition>
            </main>
        </div>
    </div>
</template>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>