<script setup>
import { ref } from 'vue';
import Sidebar from '@/Components/Admin/SidebarSimple.vue';
import Topbar from '@/Components/Admin/Topbar.vue';

// Biến trạng thái lưu trữ việc Sidebar đang đóng hay mở trên Mobile
const isSidebarOpen = ref(false);
</script>

<template>
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
