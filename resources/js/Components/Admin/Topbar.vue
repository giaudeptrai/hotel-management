<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3'; // Quan trọng: Import Link để làm nút Đăng xuất
import ThemeToggle from '@/Components/Admin/ThemeToggle.vue';

defineEmits(['toggle-sidebar']);

// Trạng thái đóng/mở của Menu Tài khoản
const isProfileOpen = ref(false);
</script>

<template>
    <header class="h-[70px] flex items-center justify-between px-4 sm:px-6 bg-white dark:bg-dark-card border-b border-slate-100 dark:border-dark-border transition-colors duration-500 sticky top-0 z-30">
        
        <div class="flex items-center gap-2 sm:gap-4">
            <button @click="$emit('toggle-sidebar')" 
                    class="lg:hidden text-main-text dark:text-white hover:text-primary-500 hover:bg-slate-50 dark:hover:bg-slate-800 p-2 -ml-2 rounded-lg transition-colors flex items-center justify-center cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            
            <div class="hidden md:flex items-center bg-surface dark:bg-dark-bg rounded-full px-4 py-2 focus-within:ring-2 focus-within:ring-primary-500/20 transition-all">
                <i class="fas fa-search text-muted-text text-sm"></i>
                <input type="text" placeholder="Tìm kiếm..." class="bg-transparent border-none focus:ring-0 text-sm w-48 text-main-text dark:text-white ml-2 placeholder:text-muted-text p-0">
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-4 relative">
            <ThemeToggle />
            
            <button class="relative p-2 text-muted-text hover:text-primary-500 rounded-full hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="w-[22px] h-[22px]">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span class="absolute top-[6px] right-[6px] w-[9px] h-[9px] bg-red-500 rounded-full border-2 border-white dark:border-dark-card"></span>
            </button>

            <div class="hidden sm:block w-px h-6 bg-slate-200 dark:bg-dark-border mx-1"></div>

            <div class="relative">
                <div @click="isProfileOpen = !isProfileOpen" 
                     class="flex items-center gap-3 cursor-pointer hover:bg-surface dark:hover:bg-dark-bg p-1.5 sm:pr-3 rounded-full transition-all border border-transparent dark:hover:border-dark-border/50"
                     :class="{'bg-surface dark:bg-dark-bg': isProfileOpen}">
                    
                    <img :src="'https://ui-avatars.com/api/?name=' + $page.props.auth.user.name + '&background=3e63e2&color=fff'" alt="Avatar" class="w-8 h-8 rounded-full shadow-sm">
                    
                    <div class="hidden sm:block text-left">
                        <p class="text-sm font-bold text-main-text dark:text-white leading-tight">{{ $page.props.auth.user.name }}</p>
                        <p class="text-[10px] text-muted-text font-medium">Quản trị viên</p>
                    </div>
                    
                    <i class="fas fa-chevron-down text-[10px] text-muted-text hidden sm:block ml-1 transition-transform duration-300" 
                       :class="{'rotate-180': isProfileOpen}"></i>
                </div>

                <Transition 
                    enter-active-class="transition ease-out duration-200" 
                    enter-from-class="transform opacity-0 scale-95 translate-y-2" 
                    enter-to-class="transform opacity-100 scale-100 translate-y-0" 
                    leave-active-class="transition ease-in duration-150" 
                    leave-from-class="transform opacity-100 scale-100 translate-y-0" 
                    leave-to-class="transform opacity-0 scale-95 translate-y-2">
                    
                    <div v-if="isProfileOpen" class="absolute right-0 mt-3 w-56 bg-white dark:bg-dark-card border border-slate-100 dark:border-dark-border rounded-xl shadow-app dark:shadow-app-dark py-2 z-50">
                        
                        <div class="px-4 py-2 border-b border-slate-50 dark:border-dark-border/50 mb-1">
                            <p class="text-[10px] font-bold text-muted-text uppercase tracking-widest">Tài khoản</p>
                        </div>

                        <Link :href="route('profile.edit')" class="w-full text-left px-4 py-2.5 text-sm font-medium text-main-text dark:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center gap-3 group">
                            <i class="fas fa-user text-muted-text group-hover:text-primary-500 transition-colors w-4 text-center"></i> 
                            Hồ sơ cá nhân
                        </Link>
                        
                        <div class="h-px bg-slate-100 dark:bg-dark-border my-1"></div>
                        
                        <Link :href="route('logout')" method="post" as="button" class="w-full text-left px-4 py-2.5 text-sm font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors flex items-center gap-3">
                            <i class="fas fa-sign-out-alt w-4 text-center"></i> 
                            Đăng xuất
                        </Link>
                    </div>
                </Transition>

                <div v-if="isProfileOpen" @click="isProfileOpen = false" class="fixed inset-0 z-40"></div>
            </div>
        </div>
    </header>
</template>