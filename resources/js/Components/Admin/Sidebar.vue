<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <aside class="w-64 fixed h-full bg-white dark:bg-dark-card border-r border-slate-100 dark:border-dark-border z-50 flex flex-col transition-colors duration-500">
        <div class="h-[70px] flex items-center justify-center border-b border-slate-50 dark:border-dark-border/30">
            <span class="text-2xl font-black text-primary-500 tracking-tighter italic">DASHER<span class="text-main-text dark:text-white">PMS</span></span>
        </div>

        <div class="flex-1 overflow-y-auto py-6 px-4 space-y-7 scrollbar-hide">
            
            <div class="space-y-2">
                <p class="px-4 text-[10px] font-bold text-muted-text uppercase tracking-[0.2em] mb-4 opacity-70">Lưu trú & Phòng</p>
                <nav class="space-y-1">
                    <Link :href="route('dashboard')" 
                          :class="route().current('dashboard') ? 'bg-primary-500 text-white shadow-app-dark translate-x-1' : 'text-muted-text hover:text-main-text dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800'"
                          class="flex items-center gap-3 px-4 py-3 rounded-layout transition-all duration-300 font-bold text-sm group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        <span>Tổng quan</span>
                    </Link>

                    <Link :href="route('admin.rooms.index')" 
                          :class="route().current('admin.rooms.*') ? 'bg-primary-500 text-white shadow-app-dark translate-x-1' : 'text-muted-text hover:text-main-text dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800'"
                          class="flex items-center gap-3 px-4 py-3 rounded-layout transition-all duration-300 font-bold text-sm group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5-1.5l-3-1m-3.145-5.303a48.56 48.56 0 00-11.43 3.973" />
                        </svg>
                        <span>Quản lý Phòng</span>
                    </Link>

                    <Link :href="route('admin.room-categories.index')" 
                          :class="route().current('admin.room-categories.*') ? 'bg-primary-500 text-white shadow-app-dark translate-x-1' : 'text-muted-text hover:text-main-text dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800'"
                          class="flex items-center gap-3 px-4 py-3 rounded-layout transition-all duration-300 font-bold text-sm group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25z" />
                        </svg>
                        <span>Hạng phòng</span>
                    </Link>

                    <Link :href="route('admin.room-types.index')" 
                          :class="route().current('admin.room-types.*') ? 'bg-primary-500 text-white shadow-app-dark translate-x-1' : 'text-muted-text hover:text-main-text dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800'"
                          class="flex items-center gap-3 px-4 py-3 rounded-layout transition-all duration-300 font-bold text-sm group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V9.75M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5-1.5l-3-1m-3.145-5.303a48.56 48.56 0 00-11.43 3.973" />
                        </svg>
                        <span>Loại phòng</span>
                    </Link>

                    <Link :href="route('admin.room-definitions.index')" 
                          :class="route().current('admin.room-definitions.*') ? 'bg-primary-500 text-white shadow-app-dark translate-x-1' : 'text-muted-text hover:text-main-text dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800'"
                          class="flex items-center gap-3 px-4 py-3 rounded-layout transition-all duration-300 font-bold text-sm group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V9.75M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5-1.5l-3-1m-3.145-5.303a48.56 48.56 0 00-11.43 3.973" />
                        </svg>
                        <span>Định nghĩa phòng</span>
                    </Link>

                </nav>
            </div>

            <div class="space-y-2">
                <p class="px-4 text-[10px] font-bold text-muted-text uppercase tracking-[0.2em] mb-4 opacity-70">Dịch vụ & Tiện ích</p>
                <nav class="space-y-1">
                    <Link href="#" class="flex items-center gap-3 px-4 py-3 text-muted-text hover:text-main-text dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 rounded-layout transition-all font-bold text-sm group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:text-primary-500 transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        <span>Quản lý Dịch vụ</span>
                    </Link>
                    <Link :href="route('admin.amenities.index')" class="flex items-center gap-3 px-4 py-3 text-muted-text hover:text-main-text dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 rounded-layout transition-all font-bold text-sm group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:text-primary-500 transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09l2.846.813-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                        </svg>
                        <span>Quản lý Tiện ích</span>
                    </Link>
                </nav>
            </div>

            <div class="space-y-2">
                <p class="px-4 text-[10px] font-bold text-muted-text uppercase tracking-[0.2em] mb-4 opacity-70">Kinh doanh</p>
                <nav class="space-y-1">
                    <Link href="#" class="flex items-center gap-3 px-4 py-3 text-muted-text hover:text-main-text dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 rounded-layout transition-all font-bold text-sm group">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:text-primary-500 transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z" />
                        </svg>
                        <span>Khách hàng</span>
                    </Link>
                </nav>
            </div>
            
        </div>

        <div class="p-4 border-t border-slate-50 dark:border-dark-border/30">
            <div class="bg-primary-500/5 dark:bg-primary-500/10 rounded-card p-4">
                <h4 class="text-xs font-black text-primary-500 uppercase mb-1">Hệ thống v1.0</h4>
                <p class="text-[10px] text-muted-text font-medium leading-relaxed">
                    Chào {{ $page.props.auth.user?.name || 'Admin' }}!
                </p>
            </div>
        </div>
    </aside>
</template>

<style scoped>
/* Ẩn thanh cuộn nhưng vẫn có thể cuộn */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>