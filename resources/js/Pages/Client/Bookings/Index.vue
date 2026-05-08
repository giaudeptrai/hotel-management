<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    bookings: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, completed: 0, cancelled: 0 }),
    },
});

const showCancelModal = ref(false);
const selectedBookingId = ref(null);
const isCancelling = ref(false);

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
}).format(Number(value || 0));

const formatDate = (value) => {
    if (!value) {
        return '--/--/----';
    }

    return new Date(value).toLocaleDateString('vi-VN');
};

const getStatusLabel = (status) => {
    const map = {
        pending: 'Chờ xác nhận',
        confirmed: 'Đã nhận cọc',
        checked_in: 'Đang lưu trú',
        checked_out: 'Đã hoàn tất',
        cancelled: 'Đã hủy',
    };

    return map[status] || status;
};

const getStatusClass = (status) => {
    const map = {
        pending: 'client-booking-status-pending',
        confirmed: 'client-booking-status-confirmed',
        checked_in: 'client-booking-status-inhouse',
        checked_out: 'client-booking-status-finished',
        cancelled: 'client-booking-status-cancelled',
    };

    return map[status] || 'client-booking-status-default';
};

const cancelBooking = (bookingId) => {
    selectedBookingId.value = bookingId;
    showCancelModal.value = true;
};

const confirmCancel = () => {
    isCancelling.value = true;
    router.post(route('client.bookings.cancel', selectedBookingId.value), {}, {
        onFinish: () => {
            showCancelModal.value = false;
            selectedBookingId.value = null;
            isCancelling.value = false;
        },
    });
};

const closeCancelModal = () => {
    if (!isCancelling.value) {
        showCancelModal.value = false;
        selectedBookingId.value = null;
    }
};
</script>

<template>
    <Head title="Lịch sử đặt phòng của tôi" />

    <ClientLayout>
        <section class="client-bookings-shell">
            <div class="client-bookings-hero">
                <span class="client-bookings-kicker">Hồ sơ đặt phòng</span>
                <h1 class="client-bookings-title">Danh Sách Lịch Sử Lưu Trú</h1>
                <p class="client-bookings-desc">
                    Mỗi đơn đặt phòng được đóng gói thành thẻ tóm tắt để bạn lướt nhanh: mã đơn, trạng thái,
                    hình phòng, thời gian lưu trú và tổng thanh toán.
                </p>
            </div>

            <div class="client-bookings-stats">
                <article class="client-bookings-stat-card">
                    <p class="client-bookings-stat-label">Tổng đơn</p>
                    <p class="client-bookings-stat-value">{{ stats.total || 0 }}</p>
                </article>
                <article class="client-bookings-stat-card">
                    <p class="client-bookings-stat-label">Đã hoàn tất</p>
                    <p class="client-bookings-stat-value text-emerald-500">{{ stats.completed || 0 }}</p>
                </article>
                <article class="client-bookings-stat-card">
                    <p class="client-bookings-stat-label">Đã hủy</p>
                    <p class="client-bookings-stat-value text-rose-500">{{ stats.cancelled || 0 }}</p>
                </article>
            </div>

            <div v-if="bookings.data.length" class="client-bookings-grid">
                <article v-for="booking in bookings.data" :key="booking.id" class="client-bookings-card">
                    <div class="client-bookings-card-media">
                        <img
                            v-if="booking.thumbnail"
                            :src="booking.thumbnail"
                            :alt="booking.room_type_name"
                            class="client-bookings-thumb"
                        >
                        <div v-else class="client-bookings-thumb-placeholder">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A1.5 1.5 0 014.5 6h15A1.5 1.5 0 0121 7.5v9A1.5 1.5 0 0119.5 18h-15A1.5 1.5 0 013 16.5v-9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 12h9m-9 3h5" />
                            </svg>
                        </div>
                    </div>

                    <div class="client-bookings-card-body">
                        <div class="client-bookings-top-row">
                            <p class="client-bookings-code">#{{ booking.booking_code }}</p>
                            <span class="client-bookings-status" :class="getStatusClass(booking.status)">
                                {{ getStatusLabel(booking.status) }}
                            </span>
                        </div>

                        <h3 class="client-bookings-room">{{ booking.room_type_name }}</h3>

                        <p class="text-xs text-gray-500 mb-3">
                            Đặt lúc: <strong class="text-gray-700">{{ new Date(booking.created_at).toLocaleString('vi-VN') }}</strong>
                        </p>

                        <div class="client-bookings-dates">
                            <p>
                                <span>Check-in:</span>
                                <strong>{{ formatDate(booking.check_in_expected) }}</strong>
                            </p>
                            <p>
                                <span>Check-out:</span>
                                <strong>{{ formatDate(booking.check_out_expected) }}</strong>
                            </p>
                        </div>

                        <div class="client-bookings-footer">
                            <div>
                                <p class="client-bookings-total-label">Tổng thanh toán</p>
                                <p class="client-bookings-total-value">{{ formatCurrency(booking.total_amount) }}</p>
                            </div>
                            <div class="flex gap-2">
                                <Link :href="route('client.bookings.show', booking.id)" class="client-bookings-cta">
                                    Xem chi tiết
                                </Link>
                                <button
                                    v-if="booking.can_cancel"
                                    type="button"
                                    class="px-4 py-2 rounded-2xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-xs uppercase tracking-widest transition-colors"
                                    @click="cancelBooking(booking.id)"
                                >
                                    Hủy đơn
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <div v-else class="client-bookings-empty">
                <h2>Chưa có đơn đặt phòng nào</h2>
                <p>Bạn có thể bắt đầu đặt phòng mới và lịch sử sẽ hiển thị đầy đủ tại đây.</p>
                <Link :href="route('client.rooms.index')" class="btn-primary !px-6 !py-3 !text-xs">Đặt phòng ngay</Link>
            </div>

            <div v-if="bookings.links?.length" class="index-pagination">
                <Pagination :links="bookings.links" />
            </div>

            <transition>
                <div v-if="showCancelModal" class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50">
                    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Xác nhận hủy đơn</h3>
                            <p class="text-gray-600 text-sm">
                                Bạn có chắc muốn hủy đơn đặt phòng này không? Hành động này không thể hoàn tác.
                            </p>
                        </div>
                        <div class="border-t border-gray-200 px-6 py-4 flex gap-3 justify-end">
                            <button
                                type="button"
                                class="px-4 py-2 rounded-2xl border border-gray-300 text-gray-700 font-bold text-sm hover:bg-gray-50 transition-colors"
                                @click="closeCancelModal"
                                :disabled="isCancelling"
                            >
                                Không, giữ lại
                            </button>
                            <button
                                type="button"
                                class="px-4 py-2 rounded-2xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                @click="confirmCancel"
                                :disabled="isCancelling"
                            >
                                {{ isCancelling ? 'Đang hủy...' : 'Xác nhận hủy' }}
                            </button>
                        </div>
                    </div>
                </div>
            </transition>
        </section>
    </ClientLayout>
</template>
