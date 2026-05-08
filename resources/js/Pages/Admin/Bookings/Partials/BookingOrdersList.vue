<script setup>
import { Link } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

defineProps({
    bookings: {
        type: Object,
        required: true,
    },
    search: {
        type: String,
        required: true,
    },
    source: {
        type: String,
        required: true,
    },
    status: {
        type: String,
        required: true,
    },
    listStartDate: {
        type: String,
        required: true,
    },
    listEndDate: {
        type: String,
        required: true,
    },
    applyListFilters: {
        type: Function,
        required: true,
    },
    clearListFilters: {
        type: Function,
        required: true,
    },
    formatDateTime: {
        type: Function,
        required: true,
    },
    formatCurrency: {
        type: Function,
        required: true,
    },
    getStatusClass: {
        type: Function,
        required: true,
    },
    getStatusLabel: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits([
    'update:search',
    'update:source',
    'update:status',
    'update:listStartDate',
    'update:listEndDate',
]);
</script>

<template>
    <div class="space-y-4">
        <div class="app-card !p-5 md:!p-6 !rounded-[2rem] border-l-4 border-l-primary-500 space-y-4">
            <div class="space-y-1">
                <h3 class="admin-index-title !text-xl">Danh sách đơn</h3>
                <p class="text-xs text-muted-text">Quản lý các đơn đặt phòng hiện tại</p>
            </div>

            <div class="flex flex-col xl:flex-row xl:items-center gap-4 w-full">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 flex-1 w-full">
                    <input
                        :value="listStartDate"
                        @input="emit('update:listStartDate', $event.target.value)"
                        type="date"
                        class="admin-index-search !w-full"
                    />
                    <input
                        :value="listEndDate"
                        @input="emit('update:listEndDate', $event.target.value)"
                        type="date"
                        class="admin-index-search !w-full"
                    />
                    <select
                        :value="source"
                        @change="emit('update:source', $event.target.value)"
                        class="admin-index-search !w-full"
                    >
                        <option value="">Tất cả nguồn</option>
                        <option value="online">Online</option>
                        <option value="walk_in">Tại quầy</option>
                    </select>
                    <select
                        :value="status"
                        @change="emit('update:status', $event.target.value)"
                        class="admin-index-search !w-full"
                    >
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending">Chưa xác nhận</option>
                        <option value="confirmed">Đã nhận cọc</option>
                        <option value="checked_in">Đang lưu trú</option>
                    </select>
                    <input
                        :value="search"
                        @input="emit('update:search', $event.target.value)"
                        @keyup.enter="applyListFilters"
                        type="text"
                        placeholder="Tên, SĐT, Mã đơn..."
                        class="admin-index-search !w-full"
                    />
                </div>

                <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto pt-2 xl:pt-0">
                    <button @click="applyListFilters" class="btn-primary !px-8 !py-3 w-full sm:w-auto text-center flex-1 sm:flex-none">
                        Lọc
                    </button>
                    <button
                        v-if="search || source || status || listStartDate || listEndDate"
                        @click="clearListFilters"
                        class="admin-filter-reset-btn whitespace-nowrap w-full sm:w-auto text-center flex-1 sm:flex-none"
                    >
                        Bỏ lọc
                    </button>
                </div>
            </div>
        </div>

        <div class="index-table-card !rounded-[2rem]">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="index-table">
                    <thead class="index-table-head">
                        <tr class="index-table-head-row">
                            <th class="index-table-th">Mã đơn</th>
                            <th class="index-table-th">Khách hàng</th>
                            <th class="index-table-th">Thời gian đặt</th>
                            <th class="index-table-th">Trạng thái</th>
                            <th class="index-table-th">Thời gian ở</th>
                            <th class="index-table-th text-right px-10">Thanh toán</th>
                            <th class="index-table-th"></th>
                        </tr>
                    </thead>
                    <tbody class="index-table-body">
                        <tr v-for="booking in bookings.data" :key="booking.id" class="index-table-row font-bold">
                            <td class="index-table-th">
                                <span class="text-primary-500 font-black tracking-wider">#{{ booking.booking_code }}</span>
                                <div class="text-[9px] text-muted-text mt-1 uppercase tracking-widest font-black">{{ booking.source }}</div>
                                <div class="text-[10px] text-slate-500 mt-1" v-if="booking.booking_rooms?.[0]?.room?.room_number">
                                    Phòng: P.{{ booking.booking_rooms[0].room.room_number }}
                                </div>
                            </td>
                            <td class="index-table-th">
                                <div class="text-main-text dark:text-white">{{ booking.customer?.full_name }}</div>
                                <div class="text-[10px] text-muted-text mt-0.5 tracking-tighter">{{ booking.customer?.phone }}</div>
                            </td>
                            <td class="index-table-th">
                                <div class="text-sm font-bold text-main-text dark:text-white">{{ formatDateTime(booking.created_at) }}</div>
                            </td>
                            <td class="index-table-th">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border" :class="getStatusClass(booking.status)">
                                    {{ getStatusLabel(booking.status) }}
                                </span>
                            </td>
                            <td class="index-table-th text-slate-500 font-medium text-xs">
                                {{ new Date(booking.check_in_expected).toLocaleDateString('vi-VN') }}
                                <span class="mx-1 opacity-30 italic">đến</span>
                                {{ new Date(booking.check_out_expected).toLocaleDateString('vi-VN') }}
                            </td>
                            <td class="index-table-th text-right px-10 font-black text-main-text dark:text-white">
                                {{ formatCurrency(booking.total_amount) }}
                            </td>
                            <td class="index-table-th text-right">
                                <div class="index-actions">
                                    <Link :href="route('admin.bookings.show', booking.id)" class="index-action-btn index-action-btn-edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="bookings.data.length === 0">
                            <td colspan="7" class="index-empty-cell">
                                <p class="index-empty-text italic">Không tìm thấy dữ liệu đặt phòng phù hợp...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="bookings.links" class="index-pagination flex justify-center">
                <Pagination :links="bookings.links" />
            </div>
        </div>
    </div>
</template>
