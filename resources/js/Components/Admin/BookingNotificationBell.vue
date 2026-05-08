<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const isOpen = ref(false);

const onlineBookingAlert = computed(() => page.props?.onlineBookingAlert || { pending_count: 0, latest: [] });
const pendingOnlineCount = computed(() => Number(onlineBookingAlert.value?.pending_count || 0));
const pendingOnlineList = computed(() => onlineBookingAlert.value?.latest || []);

const formatTimeAgo = (value) => {
    if (!value) return '';

    const created = new Date(value);
    const now = new Date();
    const diffMinutes = Math.max(0, Math.floor((now - created) / 60000));

    if (diffMinutes < 1) return 'Vua xong';
    if (diffMinutes < 60) return `${diffMinutes} phut truoc`;

    const diffHours = Math.floor(diffMinutes / 60);
    if (diffHours < 24) return `${diffHours} gio truoc`;

    const diffDays = Math.floor(diffHours / 24);
    return `${diffDays} ngay truoc`;
};

let alertInterval = null;

onMounted(() => {
    alertInterval = setInterval(() => {
        if (document.visibilityState !== 'visible') {
            return;
        }

        router.reload({
            only: ['onlineBookingAlert'],
            preserveState: true,
            preserveScroll: true,
        });
    }, 30000);
});

onUnmounted(() => {
    if (alertInterval) {
        clearInterval(alertInterval);
        alertInterval = null;
    }
});
</script>

<template>
    <div class="relative">
        <button
            @click="isOpen = !isOpen"
            class="admin-topbar-button relative"
            title="Thong bao"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <span v-if="pendingOnlineCount > 0" class="absolute -top-1 -right-1 min-w-4 h-4 rounded-full bg-rose-500 px-1 text-[9px] font-black leading-none text-white flex items-center justify-center">
                {{ pendingOnlineCount > 9 ? '9+' : pendingOnlineCount }}
            </span>
        </button>

        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-1 scale-95"
        >
            <div v-if="isOpen" class="absolute right-0 top-14 z-50 w-[340px] overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-app dark:border-dark-border dark:bg-dark-card">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 dark:border-dark-border/50">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-primary-500">Don online</p>
                        <p class="mt-1 text-xs font-semibold text-main-text dark:text-white">
                            {{ pendingOnlineCount > 0 ? `Co ${pendingOnlineCount} don cho xu ly` : 'Khong co don moi' }}
                        </p>
                    </div>
                    <Link
                        :href="route('admin.bookings.index', { tab: 'list', status: 'pending' })"
                        class="text-[10px] font-black uppercase tracking-[0.2em] text-primary-500 hover:underline"
                        @click="isOpen = false"
                    >
                        Xem tat ca
                    </Link>
                </div>

                <div v-if="pendingOnlineList.length" class="max-h-80 overflow-y-auto">
                    <Link
                        v-for="item in pendingOnlineList"
                        :key="item.id"
                        :href="route('admin.bookings.show', item.id)"
                        class="block border-b border-slate-100 px-4 py-3 last:border-b-0 hover:bg-slate-50 dark:border-dark-border/50 dark:hover:bg-dark-bg"
                        @click="isOpen = false"
                    >
                        <p class="text-xs font-black text-main-text dark:text-white">{{ item.booking_code }}</p>
                        <p class="mt-1 text-[11px] text-muted-text">{{ item.customer?.full_name || 'Khach online' }} - {{ item.customer?.phone || 'Khong co so dien thoai' }}</p>
                        <p class="mt-1 text-[10px] font-bold text-primary-500">{{ formatTimeAgo(item.created_at) }}</p>
                    </Link>
                </div>

                <div v-else class="px-4 py-6 text-center text-xs font-semibold text-muted-text">
                    Chua co don dat phong online moi.
                </div>
            </div>
        </Transition>

        <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-20"></div>
    </div>
</template>
