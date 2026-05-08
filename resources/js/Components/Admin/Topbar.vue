<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/Admin/ThemeToggle.vue';
import BookingNotificationBell from '@/Components/Admin/BookingNotificationBell.vue';

defineEmits(['toggle-sidebar']);

const page = usePage();
const isProfileOpen = ref(false);

const userName = computed(() => page.props.auth?.user?.name || 'Admin');
const roleLabel = computed(() => page.props.auth?.permissions?.includes('*') ? 'Quản trị viên' : 'Nhân sự');

const currentTitle = computed(() => {
    const component = page.component || '';

    if (component.includes('Bookings')) return 'Đặt phòng';
    if (component.includes('Customers')) return 'Khách hàng';
    if (component.includes('Invoices')) return 'Hóa đơn';
    if (component.includes('Rooms')) return 'Phòng';
    if (component.includes('Roles')) return 'Phân quyền';
    if (component.includes('Staff')) return 'Nhân sự';
    if (component.includes('Users')) return 'Tài khoản';
    return 'Dashboard';
});
</script>

<template>
    <header class="sticky top-0 z-30 flex h-[70px] items-center justify-between border-b border-slate-100 bg-white px-4 shadow-sm transition-colors duration-300 dark:border-dark-border dark:bg-dark-card sm:px-6">
        <div class="flex items-center gap-3">
            <button @click="$emit('toggle-sidebar')" class="admin-topbar-button lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <div class="hidden md:block">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-muted-text">Khu vực quản trị</p>
                <h1 class="mt-1 text-lg font-black tracking-tight text-main-text dark:text-white">{{ currentTitle }}</h1>
            </div>
        </div>

        <div class="relative flex items-center gap-2 sm:gap-3">
            <ThemeToggle />
            <BookingNotificationBell />

            <Link :href="route('admin.bookings.index')" class="admin-topbar-button hidden sm:inline-flex" title="Đặt phòng">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 3.75A2.25 2.25 0 0 0 4.75 6v12A2.25 2.25 0 0 0 7 20.25h10A2.25 2.25 0 0 0 19.25 18V8.94a2.25 2.25 0 0 0-.66-1.59l-3.94-3.94a2.25 2.25 0 0 0-1.59-.66H7Zm0 2.25h5.25V8.5A1.75 1.75 0 0 0 14 10.25h2.5V18h-9.5V6Zm2 6.25h5v1.5H9v-1.5Zm0 3h5v1.5H9v-1.5Z" />
                </svg>
            </Link>

            <button @click="isProfileOpen = !isProfileOpen" class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 transition-colors hover:bg-slate-100 dark:border-dark-border dark:bg-dark-bg dark:hover:bg-dark-border/60">
                <img :src="'https://ui-avatars.com/api/?name=' + userName + '&background=3e63e2&color=fff&bold=true'" alt="Avatar" class="h-8 w-8 rounded-lg">
                <div class="hidden sm:block text-left">
                    <p class="text-sm font-bold text-main-text dark:text-white">{{ userName }}</p>
                    <p class="text-[10px] text-muted-text">{{ roleLabel }}</p>
                </div>
                <svg class="hidden h-4 w-4 text-muted-text sm:block transition-transform" :class="{ 'rotate-180': isProfileOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0 translate-y-1 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-1 scale-95">
                <div v-if="isProfileOpen" class="absolute right-0 top-14 z-50 w-56 rounded-2xl border border-slate-100 bg-white p-2 shadow-app dark:border-dark-border dark:bg-dark-card">
                    <Link :href="route('profile.edit')" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-main-text transition-colors hover:bg-slate-50 dark:text-white dark:hover:bg-dark-bg">
                        Hồ sơ cá nhân
                    </Link>
                    <Link :href="route('logout')" method="post" as="button" class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-rose-500 transition-colors hover:bg-rose-50 dark:hover:bg-rose-500/10">
                        Đăng xuất
                    </Link>
                </div>
            </Transition>

            <div v-if="isProfileOpen" @click="isProfileOpen = false" class="fixed inset-0 z-20"></div>
        </div>
    </header>
</template>
