<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import RatingStars from '@/Components/Client/RatingStars.vue';

const props = defineProps({
    booking: { type: Object, required: true },
    room_items: { type: Array, default: () => [] },
    service_items: { type: Array, default: () => [] },
    invoice_summary: { type: Object, default: () => ({}) },
    hotel_info: { type: Object, default: () => ({}) },
    review_summary: { type: Object, default: () => ({ average: 0, count: 0 }) },
    actions: { type: Object, default: () => ({ can_cancel: false, can_review: false, reviewed: false }) },
});

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

const formatDateTime = (value) => {
    if (!value) {
        return '--:-- --/--/----';
    }

    return new Date(value).toLocaleString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
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

const showCancelModal = ref(false);
const isCancelling = ref(false);

const cancelBooking = () => {
    showCancelModal.value = true;
};

const confirmCancel = () => {
    isCancelling.value = true;
    router.post(route('client.bookings.cancel', props.booking.id), {}, {
        onFinish: () => {
            isCancelling.value = false;
            showCancelModal.value = false;
        },
    });
};

const reviewForm = useForm({
    rating: 5,
    comment: '',
});

const setRating = (rating) => {
    reviewForm.rating = rating;
};

const submitReview = () => {
    reviewForm.post(route('client.bookings.review', props.booking.id), {
        preserveScroll: true,
        onSuccess: () => reviewForm.reset('comment'),
    });
};
</script>

<template>
    <Head :title="`Chi tiết đơn ${booking.booking_code}`" />

    <ClientLayout>
        <section class="client-invoice-shell">
            <div class="client-invoice-head">
                <div>
                    <Link :href="route('client.bookings.index')" class="client-invoice-back">Quay lại lịch sử</Link>
                    <h1>Chi Tiết Đơn Đặt Phòng</h1>
                    <p class="client-invoice-code">Mã đơn: #{{ booking.booking_code }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 mt-2">
                        Thời gian đặt: {{ formatDateTime(booking.booked_at) }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="client-bookings-status" :class="getStatusClass(booking.status)">
                        {{ getStatusLabel(booking.status) }}
                    </span>
                    <Link
                        v-if="Number(invoice_summary.outstanding_amount || 0) <= 0"
                        :href="booking.invoice_url"
                        class="px-6 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm uppercase tracking-widest transition-colors"
                    >
                        Xem hóa đơn
                    </Link>
                    <button
                        v-if="actions.can_cancel"
                        type="button"
                        class="px-6 py-3 rounded-2xl bg-rose-500 hover:bg-rose-600 text-white font-bold text-sm uppercase tracking-widest transition-colors"
                        @click="cancelBooking"
                    >
                        Hủy đơn
                    </button>
                </div>
            </div>

            <div class="client-invoice-top-grid">
                <article class="client-invoice-info-card">
                    <h2>Thông tin nhận phòng</h2>
                    <ul>
                        <li>{{ hotel_info.check_in_notice }}</li>
                        <li>{{ hotel_info.check_out_notice }}</li>
                        <li>{{ hotel_info.address }}</li>
                        <li>Hotline: {{ hotel_info.hotline }}</li>
                    </ul>
                    <div class="client-invoice-date-row">
                        <p><span>Ngày nhận:</span> <strong>{{ formatDate(booking.check_in_expected) }}</strong></p>
                        <p><span>Ngày trả:</span> <strong>{{ formatDate(booking.check_out_expected) }}</strong></p>
                    </div>
                </article>

                <article class="client-invoice-summary-card">
                    <p class="client-bookings-total-label">Tổng thanh toán</p>
                    <p class="client-bookings-total-value">{{ formatCurrency(invoice_summary.total_amount) }}</p>
                    <p class="client-invoice-summary-note">Đã cọc: {{ formatCurrency(invoice_summary.deposit_amount) }}</p>
                    <p class="client-invoice-summary-note">Còn trả tại quầy: {{ formatCurrency(invoice_summary.outstanding_amount) }}</p>
                </article>
            </div>

            <article class="client-invoice-table-card">
                <div class="client-invoice-table-head">
                    <h3>Bảng kê chi phí phòng</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="client-invoice-table">
                        <thead>
                            <tr>
                                <th>Hạng phòng</th>
                                <th>Giá / đêm</th>
                                <th>Số đêm</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in room_items" :key="`${item.room_type}-${index}`">
                                <td>
                                    <div class="client-invoice-room-cell">
                                        <img
                                            v-if="item.thumbnail"
                                            :src="item.thumbnail"
                                            :alt="item.room_type"
                                            class="client-invoice-room-thumb"
                                        >
                                        <div>
                                            <p class="font-black text-main-text dark:text-white">{{ item.room_type }}</p>
                                            <p class="text-xs text-slate-500">Phòng {{ item.room_number || '--' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ formatCurrency(item.nightly_rate) }}</td>
                                <td>{{ item.nights }}</td>
                                <td class="font-black">{{ formatCurrency(item.subtotal) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="client-invoice-table-card">
                <div class="client-invoice-table-head">
                    <h3>Bảng kê dịch vụ POS</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="client-invoice-table">
                        <thead>
                            <tr>
                                <th>Dịch vụ</th>
                                <th>Số lượng</th>
                                <th>Đơn giá</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in service_items" :key="`${item.name}-${index}`">
                                <td>{{ item.name }}</td>
                                <td>{{ item.quantity }}</td>
                                <td>{{ formatCurrency(item.unit_price) }}</td>
                                <td class="font-black">{{ formatCurrency(item.total_price) }}</td>
                            </tr>
                            <tr v-if="service_items.length === 0">
                                <td colspan="4" class="py-6 text-center text-sm text-slate-500">
                                    Chưa phát sinh dịch vụ POS.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="client-invoice-payment-card">
                <h3>Tình trạng thanh toán</h3>
                <div class="client-invoice-payment-grid">
                    <div>
                        <p>Đã cọc</p>
                        <strong>{{ formatCurrency(invoice_summary.deposit_amount) }}</strong>
                    </div>
                    <div>
                        <p>Đã thanh toán</p>
                        <strong>{{ formatCurrency(invoice_summary.paid_amount) }}</strong>
                    </div>
                    <div>
                        <p>Còn trả tại quầy</p>
                        <strong>{{ formatCurrency(invoice_summary.outstanding_amount) }}</strong>
                    </div>
                </div>
            </article>

            <article v-if="actions.can_review" id="review-section" class="client-invoice-table-card">
                <div class="client-invoice-table-head">
                    <h3>Đánh giá phòng</h3>
                </div>

                <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
                    <div class="rounded-[1.75rem] bg-slate-50 p-5 dark:bg-slate-800/60">
                        <div class="flex items-center gap-2">
                            <RatingStars :value="Number(review_summary.average || 0)" size-class="h-5 w-5" />
                            <span class="text-sm font-black text-main-text dark:text-white">{{ review_summary.average ? review_summary.average.toFixed(1) : '0.0' }}/5</span>
                        </div>
                        <p class="mt-2 text-sm font-bold text-muted-text">{{ review_summary.count || 0 }} lượt đánh giá</p>
                        <p class="mt-4 text-desc leading-7">
                            {{ review_summary.count ? 'Khách trước đã để lại đánh giá thực tế cho hạng phòng này.' : 'Hạng phòng này chưa có đánh giá nào. Bạn có thể là người đầu tiên.' }}
                        </p>
                    </div>

                    <div class="rounded-[1.75rem] border border-slate-100 bg-white p-5 dark:border-dark-border dark:bg-dark-card">
                        <p class="admin-index-subtitle mb-2">Gửi đánh giá của bạn</p>
                        <h4 class="text-title text-2xl">Đánh giá sau khi hoàn tất lưu trú</h4>
                        <p class="text-desc mt-2 leading-7">Chỉ khách đã đăng nhập và đã hoàn tất đơn mới có thể gửi đánh giá.</p>

                        <div class="mt-5 flex items-center gap-2">
                            <button
                                v-for="star in 5"
                                :key="star"
                                type="button"
                                class="rounded-full p-1 transition-transform hover:scale-110"
                                @click="setRating(star)"
                            >
                                <svg class="h-8 w-8" :class="star <= reviewForm.rating ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.955a1 1 0 00.95.69h4.158c.969 0 1.371 1.24.588 1.81l-3.365 2.445a1 1 0 00-.364 1.118l1.286 3.955c.3.921-.755 1.688-1.538 1.118l-3.365-2.445a1 1 0 00-1.175 0l-3.365 2.445c-.783.57-1.838-.197-1.538-1.118l1.286-3.955a1 1 0 00-.364-1.118L2.067 9.382c-.783-.57-.38-1.81.588-1.81h4.158a1 1 0 00.95-.69l1.286-3.955z" /></svg>
                            </button>
                        </div>

                        <textarea
                            v-model="reviewForm.comment"
                            rows="4"
                            class="form-input-pms mt-5"
                            placeholder="Chia sẻ cảm nhận của bạn về phòng và dịch vụ..."
                        />

                        <p v-if="reviewForm.errors.rating" class="mt-2 text-xs font-bold text-rose-500">{{ reviewForm.errors.rating }}</p>
                        <p v-if="reviewForm.errors.comment" class="mt-2 text-xs font-bold text-rose-500">{{ reviewForm.errors.comment }}</p>
                        <p v-if="reviewForm.errors.review" class="mt-2 text-xs font-bold text-rose-500">{{ reviewForm.errors.review }}</p>

                        <button type="button" class="btn-primary mt-5 !px-6 !py-3" :disabled="reviewForm.processing" @click="submitReview">
                            {{ reviewForm.processing ? 'Đang gửi...' : 'Gửi đánh giá' }}
                        </button>
                    </div>
                </div>
            </article>

            <div class="flex justify-center mb-8">
                <Link
                    v-if="actions.can_review"
                    href="#review-section"
                    class="btn-primary"
                >
                    Đánh giá / Review
                </Link>
            </div>
        </section>

        <transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
            <div v-if="showCancelModal" class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md">
                <div class="bg-white dark:bg-dark-card rounded-[3rem] p-10 md:p-12 max-w-md w-full text-center shadow-2xl relative overflow-hidden border border-rose-100 dark:border-rose-900/30">
                    <div class="w-24 h-24 bg-rose-50 dark:bg-rose-500/10 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6 border-[6px] border-white dark:border-dark-card shadow-inner">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4v2m0 0v2m0-2H9m3 0h3m-6-8a9 9 0 110 18 9 9 0 010-18z"></path></svg>
                    </div>
                    <h3 class="text-3xl font-black italic text-main-text dark:text-white mb-3">Xác nhận hủy đơn</h3>
                    <p class="text-sm font-bold text-slate-500 mb-8 leading-relaxed">
                        Bạn có chắc chắn muốn hủy đơn đặt phòng này? Hành động này không thể hoàn tác.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button type="button" @click="showCancelModal = false" class="btn-secondary !px-6 !py-3 !w-full sm:!w-1/2 flex-1 text-center !bg-slate-100 hover:!bg-slate-200 dark:!bg-slate-800 dark:hover:!bg-slate-700 text-main-text dark:text-white font-bold">
                            Không, giữ lại
                        </button>
                        <button type="button" @click="confirmCancel" :disabled="isCancelling" class="btn-primary !px-6 !py-3 !w-full sm:!w-1/2 flex-1 !bg-rose-500 hover:!bg-rose-600 disabled:opacity-70 disabled:cursor-not-allowed flex justify-center items-center gap-2">
                            <svg v-if="isCancelling" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ isCancelling ? 'Đang hủy...' : 'Xác nhận hủy' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </ClientLayout>
</template>
