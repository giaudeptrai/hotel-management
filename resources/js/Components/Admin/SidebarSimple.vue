<script setup>
import { computed, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions || []);
const user = computed(() => page.props.auth?.user || {});
const isAdmin = computed(() => permissions.value.includes('*'));

const can = (permission) => isAdmin.value || permissions.value.includes(permission);
const routeIs = (pattern) => route().current(pattern);
const isRoomConfigOpen = ref(routeIs('admin.room-definitions.*') || routeIs('admin.room-types.*') || routeIs('admin.room-categories.*'));

watch(
    () => routeIs('admin.room-definitions.*') || routeIs('admin.room-types.*') || routeIs('admin.room-categories.*'),
    (value) => {
        if (value) {
            isRoomConfigOpen.value = true;
        }
    },
    { immediate: true }
);

const items = [
    { label: 'Tổng quan', route: 'dashboard', permission: 'dashboard.view', active: 'dashboard' },
    { label: 'Đặt phòng', route: 'admin.bookings.index', permission: 'booking.view', active: 'admin.bookings.*' },
    { label: 'Danh sách phòng', route: 'admin.rooms.index', permission: 'rooms.manage', active: 'admin.rooms.*' },
    { label: 'Cấu hình phòng', permission: 'rooms.manage', isConfig: true },
    { label: 'Dịch vụ', route: 'admin.services.index', permission: 'rooms.manage', active: 'admin.services.*' },
    // { label: 'Tiện ích', route: 'admin.amenities.index', permission: 'rooms.manage', active: 'admin.amenities.*' },
    { label: 'Khách hàng', route: 'admin.customers.index', permission: 'customer.view', active: 'admin.customers.*' },
    { label: 'Hóa đơn', route: 'admin.invoices.index', permission: 'invoice.view', active: 'admin.invoices.*' },
    { label: 'Nhân sự', route: 'admin.staffs.index', permission: 'staff.view', active: 'admin.staffs.*' },
    { label: 'Vai trò', route: 'admin.roles.index', permission: 'roles.manage', active: 'admin.roles.*' },
    { label: 'Tài khoản', route: 'admin.users.index', permission: 'staff.manage', active: 'admin.users.*' },
    { label: 'Sao lưu dữ liệu', route: 'admin.backups.index', permission: 'roles.manage', active: 'admin.backups.*' },
    { label: 'Hỗ trợ liên hệ', route: 'admin.contact-requests.index', permission: 'contact_requests.view', active: 'admin.contact-requests.*' },
];

const visibleItems = computed(() => items.filter((item) => item.isConfig || can(item.permission)));
const userRole = computed(() => (isAdmin.value ? 'Quản trị viên' : user.value?.role || 'Nhân sự'));
</script>

<template>
    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-100 bg-white text-main-text transition-colors duration-300 dark:border-dark-border dark:bg-dark-card dark:text-dark-text">
        <div class="flex h-[70px] items-center gap-3 border-b border-slate-100 px-5 dark:border-dark-border">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary-500 text-sm font-black italic text-white shadow-sm">PMS</div>
            <div class="min-w-0">
                <h2 class="truncate text-lg font-black tracking-tight">Dasher PMS</h2>
                <p class="mt-1 text-[10px] font-bold uppercase tracking-[0.2em] text-muted-text">Quản lý khách sạn</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-4 custom-scrollbar dark:custom-scrollbar-dark">
            <div class="space-y-1">
                <template v-for="item in visibleItems" :key="item.label">
                    <Link
                        v-if="item.route"
                        :href="route(item.route)"
                        preserve-scroll
                        class="mt-1 flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold transition-colors duration-200"
                        :class="routeIs(item.active) ? 'bg-primary-500 text-white' : 'text-slate-600 hover:bg-white hover:text-main-text dark:text-slate-300 dark:hover:bg-dark-card dark:hover:text-white'"
                    >
                        <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-white/20 text-current dark:bg-dark-bg/30">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path v-if="item.label === 'Tổng quan'" d="M4 13.5V20h6v-6.5H4Zm0-9V11h6V4.5H4Zm10 0V8h6V4.5h-6ZM14 20h6v-9.5h-6V20Z" stroke-linecap="round" stroke-linejoin="round" />
                                <path v-else-if="item.label === 'Đặt phòng' || item.label === 'Lịch sử đặt phòng'" d="M7 3.75A2.25 2.25 0 0 0 4.75 6v12A2.25 2.25 0 0 0 7 20.25h10A2.25 2.25 0 0 0 19.25 18V8.94a2.25 2.25 0 0 0-.66-1.59l-3.94-3.94a2.25 2.25 0 0 0-1.59-.66H7Zm0 2.25h5.25V8.5A1.75 1.75 0 0 0 14 10.25h2.5V18h-9.5V6Zm2 6.25h5v1.5H9v-1.5Zm0 3h5v1.5H9v-1.5Z" stroke-linecap="round" stroke-linejoin="round" />
                                <path v-else-if="item.label === 'Danh sách phòng'" d="M3 19.5V9.75l9-5.25 9 5.25V19.5h-3.75v-6.75h-10.5v6.75H3Zm5.25-3.75h7.5v1.5h-7.5v-1.5Zm0-3h7.5v1.5h-7.5v-1.5Z" stroke-linecap="round" stroke-linejoin="round" />
                                <path v-else-if="item.label === 'Dịch vụ' || item.label === 'Tiện ích'" d="M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75Zm3 1.5h8.25v1.5H7.5v-1.5Zm0 3h8.25v1.5H7.5v-1.5Zm0 3h5.25v1.5H7.5v-1.5Z" stroke-linecap="round" stroke-linejoin="round" />
                                <path v-else-if="item.label === 'Khách hàng'" d="M12 12a4.5 4.5 0 1 0-4.5-4.5A4.5 4.5 0 0 0 12 12Zm0 2.25c-4.42 0-8 2.24-8 5V21h16v-1.75c0-2.76-3.58-5-8-5Z" stroke-linecap="round" stroke-linejoin="round" />
                                <path v-else-if="item.label === 'Hóa đơn'" d="M6 3.75A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25h12A2.25 2.25 0 0 0 20.25 18V8.94a2.25 2.25 0 0 0-.66-1.59l-3.94-3.94a2.25 2.25 0 0 0-1.59-.66H6Zm2.25 7.5h7.5v1.5h-7.5v-1.5Zm0 3h7.5v1.5h-7.5v-1.5Zm0-6h4.5v1.5h-4.5v-1.5Z" stroke-linecap="round" stroke-linejoin="round" />
                                <path v-else-if="item.label === 'Nhân sự' || item.label === 'Tài khoản'" d="M12 12a4.5 4.5 0 1 0-4.5-4.5A4.5 4.5 0 0 0 12 12Zm0 2.25c-4.42 0-8 2.24-8 5V21h16v-1.75c0-2.76-3.58-5-8-5Z" stroke-linecap="round" stroke-linejoin="round" />
                                <path v-else-if="item.label === 'Vai trò'" d="M12 3.75 5.25 6v5.25c0 4.69 3.12 8.94 6.75 9.75 3.63-.81 6.75-5.06 6.75-9.75V6L12 3.75Zm0 4.5 3 3-3 3-3-3 3-3Z" stroke-linecap="round" stroke-linejoin="round" />
                                <path v-else-if="item.label === 'Sao lưu dữ liệu'" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" stroke-linecap="round" stroke-linejoin="round" />
                                <path v-else d="M11.25 3.75a.75.75 0 0 1 1.5 0v1.06c.5.08.99.2 1.46.36l.75-.75a.75.75 0 1 1 1.06 1.06l-.75.75c.16.47.28.96.36 1.46h1.06a.75.75 0 0 1 0 1.5h-1.06c-.08.5-.2.99-.36 1.46l.75.75a.75.75 0 1 1-1.06 1.06l-.75-.75c-.47.16-.96.28-1.46.36v1.06a.75.75 0 0 1-1.5 0v-1.06c-.5-.08-.99-.2-1.46-.36l-.75.75a.75.75 0 1 1-1.06-1.06l.75-.75a6.8 6.8 0 0 1-.36-1.46H7.5a.75.75 0 0 1 0-1.5h1.06c.08-.5.2-.99.36-1.46l-.75-.75a.75.75 0 1 1 1.06-1.06l.75.75c.47-.16.96-.28 1.46-.36V3.75ZM12 9.75A2.25 2.25 0 1 0 12 14.25 2.25 2.25 0 0 0 12 9.75Z" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <span class="flex-1 truncate text-left">{{ item.label }}</span>
                    </Link>

                    <div v-else-if="item.isConfig">
                            <button
                                type="button"
                                @click="isRoomConfigOpen = !isRoomConfigOpen"
                                class="mt-2 flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-bold transition-colors duration-200"
                                :class="isRoomConfigOpen ? 'bg-primary-500 text-white' : 'text-slate-600 hover:bg-white hover:text-main-text dark:text-slate-300 dark:hover:bg-dark-card dark:hover:text-white'"
                            >
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 text-current dark:bg-dark-bg/30">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M4.5 7.5 12 3l7.5 4.5V21H4.5V7.5Zm3 6.75h7v1.5h-7v-1.5Zm0-3h7v1.5h-7v-1.5Z" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                                <span class="flex-1 truncate text-left">Cấu hình phòng</span>
                                <svg class="h-4 w-4 transition-transform" :class="isRoomConfigOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div v-show="isRoomConfigOpen" class="mt-2 space-y-1 pl-2">
                                <Link :href="route('admin.room-definitions.index')" preserve-scroll class="block rounded-lg px-3 py-2 text-xs font-bold text-slate-500 hover:bg-white hover:text-main-text dark:text-slate-400 dark:hover:bg-dark-bg dark:hover:text-white" :class="routeIs('admin.room-definitions.*') ? 'bg-white text-primary-500 dark:bg-dark-bg dark:text-primary-400' : ''">Thiết lập Hạng phòng</Link>
                                <Link :href="route('admin.room-types.index')" preserve-scroll class="block rounded-lg px-3 py-2 text-xs font-bold text-slate-500 hover:bg-white hover:text-main-text dark:text-slate-400 dark:hover:bg-dark-bg dark:hover:text-white" :class="routeIs('admin.room-types.*') ? 'bg-white text-primary-500 dark:bg-dark-bg dark:text-primary-400' : ''">Loại phòng</Link>
                                <Link :href="route('admin.room-categories.index')" preserve-scroll class="block rounded-lg px-3 py-2 text-xs font-bold text-slate-500 hover:bg-white hover:text-main-text dark:text-slate-400 dark:hover:bg-dark-bg dark:hover:text-white" :class="routeIs('admin.room-categories.*') ? 'bg-white text-primary-500 dark:bg-dark-bg dark:text-primary-400' : ''">Hạng phòng</Link>
                                <Link :href="route('admin.amenities.index')" preserve-scroll class="block rounded-lg px-3 py-2 text-xs font-bold text-slate-500 hover:bg-white hover:text-main-text dark:text-slate-400 dark:hover:bg-dark-bg dark:hover:text-white" :class="routeIs('admin.amenities.*') ? 'bg-white text-primary-500 dark:bg-dark-bg dark:text-primary-400' : ''">Tiện ích</Link>
                            </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="border-t border-slate-100 p-4 dark:border-dark-border">
            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-dark-bg">
                <div class="flex items-center gap-3">
                    <img
                        :src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(user?.name || 'Admin') + '&background=3e63e2&color=fff&bold=true'"
                        alt="Avatar"
                        class="h-10 w-10 rounded-lg"
                    >
                    <div class="min-w-0">
                        <p class="truncate text-sm font-black text-main-text dark:text-white">{{ user?.name || 'Admin' }}</p>
                        <p class="truncate text-[11px] text-muted-text">{{ userRole }}</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>
</template>
