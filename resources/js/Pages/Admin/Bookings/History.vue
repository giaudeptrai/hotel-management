<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    bookings: Object,
    filters: Object,
    stats: Object, // Thêm prop này từ Controller để truyền số liệu thống kê
});

const search = ref(props.filters.search || '');
const dateFilter = ref(props.filters.date || '');
const statusFilter = ref(props.filters.status || '');

// Xử lý lọc dữ liệu mượt mà
watch([search, dateFilter, statusFilter], debounce(() => {
    router.get(route('admin.bookings.history'), {
        search: search.value,
        date: dateFilter.value,
        status: statusFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 400));

const clearFilters = () => {
    search.value = '';
    dateFilter.value = '';
    statusFilter.value = '';
};

// Kiểm tra xem có đang lọc không để hiện nút "Bỏ lọc"
const hasActiveFilters = computed(() => {
    return search.value !== '' || dateFilter.value !== '' || statusFilter.value !== '';
});

function formatCurrency(value) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
}

function formatDateTime(value) {
    if (!value) return '--';
    const date = new Date(value);
    return date.toLocaleString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
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

const getRowNumber = (index) => {
    const currentPage = Number(props.bookings?.current_page || 1);
    const perPage = Number(props.bookings?.per_page || (props.bookings?.data?.length || 0));
    return (currentPage - 1) * perPage + index + 1;
};
</script>

<template>
    <Head title="Lịch sử đặt phòng hệ thống" />

    <AdminLayout>
        <div class="space-y-6 pb-12 animate-in fade-in duration-500">
            <section class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-2">
                    <span class="admin-index-subtitle">Vận hành & Đối soát</span>
                    <h1 class="admin-index-title !text-3xl">Lịch sử đặt phòng tổng quát</h1>
                    <p class="text-desc max-w-2xl mt-1">
                        Theo dõi toàn bộ đơn đặt phòng, thống kê doanh thu và quản lý trạng thái lưu trú của khách hàng.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link :href="route('admin.bookings.index')" class="admin-index-secondary-btn flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Quay lại sơ đồ
                    </Link>
                    <Link :href="route('admin.bookings.create')" class="admin-index-create-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Tạo đơn mới
                    </Link>
                </div>
            </section>

            <section v-if="stats" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="app-card !p-5 !rounded-2xl border-l-4 border-l-primary-500">
                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-text mb-1">Tổng đơn hệ thống</p>
                    <p class="text-2xl font-black text-main-text dark:text-white">{{ stats?.total_bookings || 0 }}</p>
                </div>
                <div class="app-card !p-5 !rounded-2xl border-l-4 border-l-emerald-500">
                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-text mb-1">Đã hoàn tất</p>
                    <p class="text-2xl font-black text-emerald-600">{{ stats?.completed_bookings || 0 }}</p>
                </div>
                <div class="app-card !p-5 !rounded-2xl border-l-4 border-l-rose-500">
                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-text mb-1">Đã hủy</p>
                    <p class="text-2xl font-black text-rose-600">{{ stats?.cancelled_bookings || 0 }}</p>
                </div>
                <div class="app-card !p-5 !rounded-2xl border-l-4 border-l-amber-500 bg-amber-50/30 dark:bg-amber-900/10">
                    <p class="text-[10px] font-black uppercase tracking-widest text-amber-600/70 mb-1">Tổng doanh thu lũy kế</p>
                    <p class="text-2xl font-black text-amber-600">{{ formatCurrency(stats?.total_revenue || 0) }}</p>
                </div>
            </section>

            <section class="app-card !p-5 md:!p-6 !rounded-[2rem] flex flex-col xl:flex-row xl:items-end gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Lọc theo ngày đặt</label>
                        <input v-model="dateFilter" type="date" class="form-input-pms form-input-pms-compact w-full" />
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Trạng thái</label>
                        <select v-model="statusFilter" class="form-input-pms form-input-pms-compact w-full">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending">Chờ xác nhận</option>
                            <option value="confirmed">Đã nhận cọc</option>
                            <option value="checked_in">Đang lưu trú</option>
                            <option value="checked_out">Đã hoàn tất</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Tìm kiếm nhanh</label>
                        <input v-model="search" type="text" placeholder="Mã đơn, tên khách..." class="form-input-pms form-input-pms-compact w-full" />
                    </div>
                </div>

                <div class="flex-shrink-0 pt-2 xl:pt-0">
                    <button v-if="hasActiveFilters" @click="clearFilters" class="admin-filter-reset-btn w-full xl:w-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        Bỏ lọc
                    </button>
                </div>
            </section>

            <article class="index-table-card !rounded-[2.5rem]">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="index-table">
                        <thead class="index-table-head">
                            <tr class="index-table-head-row">
                                <th class="index-table-th text-center w-16">STT</th>
                                <th class="index-table-th w-20">Mã đơn</th>
                                <th class="index-table-th">Thông tin khách hàng</th>
                                <th class="index-table-th">Phòng & Loại</th>
                                <th class="index-table-th">Thời gian đặt</th>
                                <th class="index-table-th">Thời gian lưu trú</th>
                                <th class="index-table-th">Trạng thái</th>
                                <th class="index-table-th text-right">Doanh thu</th>
                                <th class="index-table-th"></th>
                            </tr>
                        </thead>
                        <tbody class="index-table-body">
                            <tr v-for="(booking, index) in bookings.data" :key="booking.id" class="index-table-row font-bold">
                                <td class="index-table-th text-center">
                                    <span class="text-xs font-black text-muted-text">{{ getRowNumber(index) }}</span>
                                </td>
                                <td class="index-table-th">
                                    <span class="text-primary-500 font-black tracking-wider italic">#{{ booking.booking_code }}</span>
                                    <div class="text-[9px] text-muted-text mt-1 uppercase tracking-widest font-black">{{ booking.source || 'Hệ thống' }}</div>
                                </td>
                                <td class="index-table-th">
                                    <div class="text-main-text dark:text-white">{{ booking.customer_name }}</div>
                                    <div class="text-[10px] text-muted-text mt-0.5 tracking-tighter">{{ booking.customer_phone }}</div>
                                </td>
                                <td class="index-table-th">
                                    <div class="text-main-text dark:text-white font-black">P.{{ booking.room_number || 'Chưa gán' }}</div>
                                    <div class="text-[10px] text-muted-text italic">{{ booking.room_type_name }}</div>
                                </td>
                                <td class="index-table-th">
                                    <div class="text-sm font-bold text-main-text dark:text-white">{{ formatDateTime(booking.created_at) }}</div>
                                </td>
                                <td class="index-table-th text-slate-500 font-medium text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 block"></span>
                                        {{ booking.check_in }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 block"></span>
                                        {{ booking.check_out }}
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
                                        <Link :href="route('admin.bookings.show', booking.id)" class="index-action-btn index-action-btn-edit" title="Xem chi tiết">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="bookings.data.length === 0">
                                <td colspan="9" class="px-6 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800/50 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                        </div>
                                        <h3 class="text-sm font-black text-main-text dark:text-white uppercase tracking-widest">Không tìm thấy dữ liệu</h3>
                                        <p class="text-xs text-muted-text mt-1 max-w-sm mx-auto">Chưa có đơn đặt phòng nào khớp với bộ lọc hiện tại. Thử xóa lọc hoặc thay đổi ngày tìm kiếm.</p>
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
