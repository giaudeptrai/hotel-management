<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    customer: Object,       // Thông tin chi tiết khách hàng
    bookings: Object,       // Phân trang danh sách các lần đặt phòng
    stats: Object,          // Thống kê: tổng số lần ở, tổng tiền đã chi
    filters: Object,
});

const search = ref(props.filters.search || '');

// Tìm kiếm trong phạm vi lịch sử của riêng khách hàng này
watch(search, debounce(() => {
    router.get(route('admin.customers.history', props.customer.id), {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 400));

function formatCurrency(value) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
}

function getStatusLabel(status) {
    const map = {
        pending: 'Chờ xác nhận',
        confirmed: 'Đã xác nhận',
        checked_in: 'Đang lưu trú',
        checked_out: 'Đã hoàn tất',
        cancelled: 'Đã hủy',
    };
    return map[status] || status;
}

function getStatusClass(status) {
    const map = {
        pending: 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30',
        confirmed: 'bg-sky-100 text-sky-700 border-sky-200 dark:bg-sky-500/10 dark:text-sky-400 dark:border-sky-500/30',
        checked_in: 'bg-indigo-100 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/30',
        checked_out: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30',
        cancelled: 'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30',
    };
    return map[status] || 'bg-slate-100 text-slate-600 border-slate-200';
}
</script>

<template>
    <Head :title="`Hồ sơ khách hàng - ${customer.full_name}`" />

    <AdminLayout>
        <div class="space-y-6 pb-12 animate-in fade-in duration-500">

            <div class="flex items-center gap-4 mb-2">
                <Link :href="route('admin.customers.index')" class="w-10 h-10 rounded-full bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border text-slate-500 hover:text-primary-500 flex items-center justify-center transition-all shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <div>
                    <span class="admin-index-subtitle">Hồ sơ lưu trú</span>
                    <h1 class="admin-index-title !text-3xl">Chi Tiết Khách Hàng</h1>
                </div>
            </div>

            <section class="app-card !p-8 relative overflow-hidden bg-gradient-to-br from-white to-slate-50 dark:from-dark-card dark:to-dark-bg border-l-4 border-l-primary-500">
                <div class="absolute top-0 right-0 p-8 opacity-5">
                    <svg class="w-32 h-32 text-primary-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>

                <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2 flex items-center gap-5">
                        <div class="w-20 h-20 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center text-2xl font-black uppercase shadow-inner border-2 border-white dark:border-dark-card">
                            {{ customer.full_name?.charAt(0) || 'K' }}
                        </div>
                        <div>
                            <h2 class="text-2xl font-black text-main-text dark:text-white tracking-tight">{{ customer.full_name }}</h2>
                            <div class="flex items-center gap-4 mt-2 text-sm font-bold text-slate-500 dark:text-slate-400">
                                <span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> {{ customer.phone || 'Chưa cập nhật' }}</span>
                                <span v-if="customer.email" class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> {{ customer.email }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-100 dark:border-dark-border shadow-sm flex flex-col justify-center">
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-text mb-1">Tổng lượt lưu trú</p>
                        <p class="text-2xl font-black text-primary-600">{{ stats?.total_visits || 0 }} <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Lần</span></p>
                    </div>

                    <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-100 dark:border-dark-border shadow-sm flex flex-col justify-center">
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-text mb-1">Tổng doanh thu mang lại</p>
                        <p class="text-2xl font-black text-emerald-500">{{ formatCurrency(stats?.total_spent) }}</p>
                    </div>
                </div>
            </section>

            <article class="index-table-card !rounded-[2.5rem]">
                <div class="p-6 md:p-8 border-b border-slate-100 dark:border-dark-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <h3 class="admin-index-title !text-xl italic">Nhật ký lưu trú</h3>
                    <div class="relative w-full sm:w-72">
                        <input v-model="search" type="text" placeholder="Tìm theo mã đơn hoặc số phòng..." class="admin-index-search !w-full" />
                        <svg class="w-5 h-5 absolute right-4 top-1/2 -translate-y-1/2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="index-table">
                        <thead class="index-table-head">
                            <tr class="index-table-head-row">
                                <th class="index-table-th">Mã đơn</th>
                                <th class="index-table-th">Phòng lưu trú</th>
                                <th class="index-table-th">Thời gian</th>
                                <th class="index-table-th">Trạng thái</th>
                                <th class="index-table-th text-right">Tổng thanh toán</th>
                                <th class="index-table-th"></th>
                            </tr>
                        </thead>
                        <tbody class="index-table-body">
                            <tr v-for="booking in bookings.data" :key="booking.id" class="index-table-row font-bold">
                                <td class="index-table-th">
                                    <span class="text-primary-500 font-black tracking-wider italic">#{{ booking.booking_code }}</span>
                                    <div class="text-[9px] text-muted-text mt-1 uppercase tracking-widest font-black">{{ booking.source || 'Hệ thống' }}</div>
                                </td>
                                <td class="index-table-th">
                                    <div class="text-main-text dark:text-white font-black">P.{{ booking.room_number || 'Chưa gán' }}</div>
                                    <div class="text-[10px] text-muted-text mt-0.5 italic">{{ booking.room_type_name }}</div>
                                </td>
                                <td class="index-table-th text-slate-500 font-medium text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 block"></span>
                                        {{ new Date(booking.check_in_expected).toLocaleDateString('vi-VN') }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 block"></span>
                                        {{ new Date(booking.check_out_expected).toLocaleDateString('vi-VN') }}
                                    </div>
                                </td>
                                <td class="index-table-th">
                                    <span :class="getStatusClass(booking.status)" class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border">
                                        {{ getStatusLabel(booking.status) }}
                                    </span>
                                </td>
                                <td class="index-table-th font-black text-main-text dark:text-white text-right">
                                    {{ formatCurrency(booking.total_price) }}
                                </td>
                                <td class="index-table-th text-right px-6">
                                    <div class="index-actions">
                                        <Link :href="route('admin.bookings.show', booking.id)" class="index-action-btn index-action-btn-edit" title="Xem chi tiết hóa đơn này">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="bookings.data.length === 0">
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800/50 rounded-full flex items-center justify-center mb-3 border border-slate-100 dark:border-dark-border">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                        </div>
                                        <p class="text-xs font-black uppercase tracking-widest italic text-slate-400">Chưa có lịch sử lưu trú phù hợp</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="bookings.links" class="index-pagination flex justify-center">
                    <Pagination :links="bookings.links" />
                </div>
            </article>

        </div>
    </AdminLayout>
</template>
