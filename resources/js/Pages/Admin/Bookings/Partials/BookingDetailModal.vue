<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    booking: Object,
});

const emit = defineEmits(['close']);

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);

// Ưu tiên metadata từ backend để tránh lệch múi giờ khi parse date phía frontend
const isOverstay = computed(() => {
    if (typeof props.booking?.is_overstay === 'boolean') return props.booking.is_overstay;
    if (props.booking?.status !== 'checked_in') return false;
    const expectedOut = new Date(props.booking.check_out_expected);
    expectedOut.setHours(12,0,0,0);
    return new Date() > expectedOut;
});

const overstayHours = computed(() => {
    if (typeof props.booking?.overstay_hours === 'number') return props.booking.overstay_hours;
    if (!isOverstay.value) return 0;
    const expectedOut = new Date(props.booking.check_out_expected);
    expectedOut.setHours(12,0,0,0);
    const now = new Date();
    const overtimeMinutes = Math.floor((now - expectedOut) / (1000 * 60));
    return overtimeMinutes > 60 ? Math.ceil((overtimeMinutes - 60) / 60) : 0;
});

const statusColors = {
    pending: 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400',
    confirmed: 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400',
    checked_in: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400',
    checked_out: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-400',
    cancelled: 'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400',
};

const getStatusClass = (status) => statusColors[status] || 'bg-slate-100 text-slate-700 border-slate-200';

const statusLabels = {
    pending: 'Chờ xác nhận',
    confirmed: 'Đã nhận cọc',
    checked_in: 'Đang lưu trú',
    checked_out: 'Đã trả phòng',
    cancelled: 'Đã hủy',
};
</script>

<template>
    <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="show" class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
            <div class="absolute inset-0" @click="emit('close')"></div>

            <div class="app-card !p-8 md:!p-10 w-full max-w-2xl relative overflow-hidden z-10 animate-in zoom-in duration-300 shadow-2xl" :class="isOverstay ? '!border-rose-500 !border-2' : ''" @click.stop>
                <div class="absolute top-0 right-0 w-48 h-48 bg-primary-500/10 rounded-full -mr-24 -mt-24 blur-3xl pointer-events-none" :class="{'bg-rose-500/20': isOverstay}"></div>

                <div class="flex justify-between items-start mb-6 relative z-10 border-b border-slate-100 dark:border-dark-border pb-6">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="text-[10px] font-black uppercase tracking-[0.3em]" :class="isOverstay ? 'text-rose-500' : 'text-primary-500'">Xem Nhanh Đơn</span>

                            <span v-if="isOverstay" class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest shadow-sm bg-rose-500 text-white animate-pulse">
                                🚨 CHƯA CHECK-OUT (LỐ GIỜ)
                            </span>
                            <span v-else-if="booking" :class="getStatusClass(booking.status)" class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest shadow-sm border">
                                {{ statusLabels[booking.status] }}
                            </span>
                        </div>
                        <h3 class="text-title italic !text-3xl">{{ booking?.booking_code }}</h3>
                    </div>
                    <button @click="emit('close')" class="index-action-btn hover:text-rose-500 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div v-if="booking" class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                    <div class="space-y-6">
                        <div>
                            <h4 class="text-[10px] font-black text-muted-text uppercase tracking-widest mb-3">Khách hàng</h4>
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center text-primary-500 font-black">
                                    {{ booking.customer?.full_name.charAt(0) }}
                                </div>
                                <div>
                                    <p class="font-black text-main-text dark:text-white uppercase tracking-tight">{{ booking.customer?.full_name }}</p>
                                    <p class="text-[10px] font-bold text-muted-text mt-0.5">{{ booking.customer?.phone }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 dark:bg-dark-bg p-4 rounded-2xl border border-slate-100 dark:border-dark-border" :class="{'bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30': isOverstay}">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="text-[9px] uppercase font-bold text-slate-400 tracking-widest" :class="{'text-rose-500': isOverstay}">Check-in</span>
                                    <span class="text-xs font-black text-primary-500 mt-1" :class="{'text-rose-600': isOverstay}">{{ new Date(booking.check_in_expected).toLocaleDateString('vi-VN') }}</span>
                                </div>
                                <div class="h-px w-6 bg-slate-200 dark:bg-dark-border" :class="{'bg-rose-300': isOverstay}"></div>
                                <div class="flex flex-col text-right">
                                    <span class="text-[9px] uppercase font-bold text-slate-400 tracking-widest" :class="{'text-rose-500': isOverstay}">Check-out</span>
                                    <span class="text-xs font-black mt-1" :class="isOverstay ? 'text-rose-400 line-through opacity-70' : 'text-rose-500'">
                                        {{ new Date(booking.check_out_expected).toLocaleDateString('vi-VN') }}
                                    </span>
                                </div>
                            </div>
                            <p v-if="isOverstay" class="text-[9px] text-center text-rose-600 font-black uppercase mt-3">Đã lố giờ trả phòng!</p>
                            <p v-if="isOverstay && overstayHours > 0" class="text-[9px] text-center text-rose-700 font-black uppercase mt-2">
                                Lố {{ overstayHours }} giờ
                            </p>
                        </div>
                    </div>

                    <div class="space-y-6 flex flex-col justify-between">
                        <div class="p-5 rounded-2xl border" :class="isOverstay ? 'bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30' : 'bg-primary-500/5 border-primary-500/10 dark:bg-primary-500/10 dark:border-primary-500/20'">
                            <p class="text-[10px] font-black uppercase tracking-widest mb-1" :class="isOverstay ? 'text-rose-600' : 'text-primary-600/70'">Tổng dự kiến</p>
                            <p class="text-2xl font-black tracking-tighter" :class="isOverstay ? 'text-rose-600' : 'text-primary-500'">{{ formatCurrency(booking.total_amount) }}</p>

                            <div class="mt-3 pt-3 flex justify-between" :class="isOverstay ? 'border-t border-rose-200' : 'border-t border-primary-500/10'">
                                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Đã cọc: {{ formatCurrency(booking.deposit_amount) }}</span>
                            </div>
                        </div>

                        <div class="flex gap-3 mt-4">
                            <button @click="emit('close')" class="flex-1 admin-index-secondary-btn !py-3 !text-[10px] uppercase tracking-widest justify-center">Đóng</button>
                            <Link :href="route('admin.bookings.show', booking.id)" class="flex-[2] btn-primary !py-3 !text-[10px] uppercase tracking-widest text-center" :class="{'!bg-rose-500 hover:!bg-rose-600': isOverstay}">
                                Giải Quyết Đơn ➡️
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
