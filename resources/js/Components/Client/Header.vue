<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/Admin/ThemeToggle.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const currentComponent = computed(() => page.component || '');

const navItems = [
    {
        label: 'Khám Phá',
        href: route('home'),
        active: ['Welcome'],
    },
    {
        label: 'Loại Phòng',
        href: route('client.rooms.index'),
        active: ['Client/Rooms'],
    },
    // {
    //     label: 'Dịch Vụ',
    //     href: route('client.services.index'),
    //     active: ['Client/Services'],
    // },
    {
        label: 'Liên Hệ',
        href: route('client.contact.index'),
        active: ['Client/Contact'],
    },
];

const isMobileMenuOpen = ref(false);
const isProfileOpen = ref(false);

const isActive = (patterns) => patterns.length > 0 && patterns.some((pattern) => currentComponent.value.startsWith(pattern));

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
    if (isMobileMenuOpen.value) {
        isProfileOpen.value = false;
    }
};

const toggleProfileMenu = () => {
    isProfileOpen.value = !isProfileOpen.value;
    if (isProfileOpen.value) {
        isMobileMenuOpen.value = false;
    }
};

const closeMobileMenu = () => {
    isMobileMenuOpen.value = false;
};

const closeProfileMenu = () => {
    isProfileOpen.value = false;
};
</script>

<template>
    <header class="fixed top-0 z-50 w-full border-b border-slate-200/80 bg-white/80 shadow-sm backdrop-blur-xl transition-colors duration-500 dark:border-slate-700/70 dark:bg-slate-950/80">
        <div class="bg-slate-900 px-4 py-2 text-white dark:bg-primary-900">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 text-[10px] font-black uppercase tracking-[0.2em] sm:px-2">
                <span class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Hỗ trợ đặt phòng 24/7
                </span>
                <div class="hidden items-center gap-6 md:flex">
                    <a href="tel:0792008096" class="hover:text-primary-400 transition-colors">Hotline: 0792 008 096</a>
                    <span>Check-in 14:00 — Check-out 12:00</span>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between gap-4">
                <Link :href="route('home')" class="group shrink-0 flex items-center gap-2">
                    <div class="w-10 h-10 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl flex items-center justify-center font-black text-xl italic group-hover:bg-primary-500 transition-colors">
                        D
                    </div>
                    <div>
                        <p class="text-xl font-black italic tracking-tighter text-main-text transition-colors group-hover:text-primary-500 dark:text-white">
                            DASHER<span class="text-primary-500">HOTEL</span>
                        </p>
                        <p class="text-[9px] font-black uppercase tracking-[0.22em] text-muted-text">Signature Retreat</p>
                    </div>
                </Link>

                <nav class="hidden items-center gap-1 md:flex lg:gap-2">
                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        class="rounded-full px-5 py-2.5 text-[11px] font-black uppercase tracking-widest transition-all"
                        :class="isActive(item.active) ? 'bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white'"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="flex shrink-0 items-center gap-3">
                    <ThemeToggle />

                    <div class="hidden items-center gap-3 md:flex pl-3 border-l border-slate-200 dark:border-slate-800">
                        <template v-if="user">
                            <div class="profile-dropdown-container relative">
                                <button
                                    type="button"
                                    @click="toggleProfileMenu"
                                    class="group flex items-center gap-3 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-2 py-1.5 transition-all hover:border-primary-500 hover:shadow-sm"
                                    :aria-expanded="isProfileOpen"
                                >
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-500 text-xs font-black text-white shadow-inner">
                                        {{ user?.name?.charAt?.(0) || 'U' }}
                                    </div>
                                    <div class="hidden lg:flex flex-col text-left pr-2">
                                        <span class="text-[9px] font-black uppercase tracking-widest text-muted-text leading-none mb-0.5">Xin chào,</span>
                                        <span class="text-xs font-black text-main-text dark:text-white leading-none truncate max-w-[100px]">{{ user.name }}</span>
                                    </div>
                                </button>

                                <Transition
                                    enter-active-class="transition ease-out duration-200"
                                    enter-from-class="transform translate-y-2 scale-95 opacity-0"
                                    enter-to-class="transform translate-y-0 scale-100 opacity-100"
                                    leave-active-class="transition ease-in duration-150"
                                    leave-from-class="transform translate-y-0 scale-100 opacity-100"
                                    leave-to-class="transform translate-y-2 scale-95 opacity-0"
                                >
                                    <div v-if="isProfileOpen" class="absolute right-0 z-50 mt-3 w-64 rounded-2xl border border-slate-100 bg-white p-2 shadow-xl dark:border-dark-border dark:bg-dark-card">
                                        <div class="mb-2 px-3 py-2 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                                            <p class="text-xs font-black text-main-text dark:text-white truncate">{{ user.name }}</p>
                                            <p class="text-[10px] text-muted-text truncate">{{ user.email }}</p>
                                        </div>

                                        <Link v-if="['admin', 'staff'].includes(user.role)" :href="route('dashboard')" @click="closeProfileMenu" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-50 hover:text-primary-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-primary-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6z" /></svg>
                                            Trang Quản Trị
                                        </Link>

                                        <Link :href="route('client.bookings.index')" @click="closeProfileMenu" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-50 hover:text-primary-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-primary-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5a3 3 0 016 0m-6 4h6m-6 4h6m-6 4h4" /></svg>
                                            Đơn Đặt Phòng
                                        </Link>

                                        <Link :href="route('profile.edit')" @click="closeProfileMenu" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-bold text-slate-600 transition-colors hover:bg-slate-50 hover:text-primary-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-primary-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                                            Hồ Sơ Cá Nhân
                                        </Link>

                                        <div class="my-2 h-px bg-slate-100 dark:bg-dark-border"></div>

                                        <Link :href="route('logout')" method="post" as="button" @click="closeProfileMenu" class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm font-bold text-rose-500 transition-colors hover:bg-rose-50 dark:hover:bg-rose-500/10">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                                            Đăng xuất
                                        </Link>
                                    </div>
                                </Transition>
                                <div v-if="isProfileOpen" class="fixed inset-0 z-40" @click="closeProfileMenu"></div>
                            </div>
                        </template>

                        <template v-else>
                            <Link :href="route('login')" class="text-xs font-black uppercase tracking-widest text-slate-600 hover:text-primary-500 dark:text-slate-300 transition-colors">
                                Đăng nhập
                            </Link>
                            <Link :href="route('register')" class="px-5 py-2.5 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[11px] font-black uppercase tracking-widest hover:bg-primary-500 dark:hover:bg-primary-500 dark:hover:text-white transition-colors">
                                Đăng ký
                            </Link>
                        </template>
                    </div>

                    <button
                        type="button"
                        @click="toggleMobileMenu"
                        class="rounded-full bg-slate-100 dark:bg-slate-800 p-2.5 text-slate-600 dark:text-slate-300 md:hidden"
                    >
                        <svg v-if="!isMobileMenuOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
        </div>

        <transition enter-active-class="transition duration-200 ease-out" enter-from-class="-translate-y-full opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="translate-y-0 opacity-100" leave-to-class="-translate-y-full opacity-0">
            <div v-if="isMobileMenuOpen" class="absolute left-0 top-full z-40 w-full border-b border-slate-100 bg-white/95 backdrop-blur-xl shadow-2xl dark:border-slate-800 dark:bg-slate-950/95 md:hidden">
                <div class="flex flex-col gap-2 p-4">
                    <Link
                        v-for="item in navItems"
                        :key="item.label"
                        :href="item.href"
                        @click="closeMobileMenu"
                        class="rounded-xl px-4 py-3.5 text-sm font-black uppercase tracking-widest transition-colors text-center"
                        :class="isActive(item.active) ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-900'"
                    >
                        {{ item.label }}
                    </Link>

                    <div class="my-2 h-px bg-slate-100 dark:bg-slate-800"></div>

                    <template v-if="user">
                        <Link :href="route('client.bookings.index')" @click="closeMobileMenu" class="rounded-xl bg-slate-50 dark:bg-slate-900 px-4 py-3.5 text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white text-center">Quản Lý Đơn Đặt Phòng</Link>
                        <Link :href="route('profile.edit')" @click="closeMobileMenu" class="rounded-xl bg-slate-50 dark:bg-slate-900 px-4 py-3.5 text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white text-center">Hồ Sơ Cá Nhân</Link>
                        <Link :href="route('logout')" method="post" as="button" @click="closeMobileMenu" class="rounded-xl px-4 py-3.5 text-sm font-black uppercase tracking-widest text-rose-500 hover:bg-rose-50 text-center">Đăng Xuất</Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" @click="closeMobileMenu" class="rounded-xl bg-slate-100 dark:bg-slate-800 px-4 py-3.5 text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white text-center">Đăng Nhập</Link>
                        <Link :href="route('register')" @click="closeMobileMenu" class="rounded-xl bg-primary-500 px-4 py-3.5 text-sm font-black uppercase tracking-widest text-white text-center">Đăng Ký</Link>
                    </template>
                </div>
            </div>
        </transition>
    </header>
</template>
