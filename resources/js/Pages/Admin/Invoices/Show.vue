<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    invoice: Object,
});

// Helpers Format
const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
const formatDate = (dateString) => {
    if (!dateString) return '...... / ...... / 20...';
    return new Date(dateString).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

// 🎯 NÚT TRỞ VỀ THÔNG MINH
const goBack = () => {
    if (window.history.length > 2) {
        window.history.back(); // Nhớ đường cũ để quay về Booking (nếu đi từ đó qua)
    } else {
        router.get(route('admin.invoices.index')); // Nếu rớt mạng hoặc nhảy tab mới thì về Danh sách Hóa đơn
    }
};

// 🎯 KIỂM TRA TRẠNG THÁI HỦY (NO-SHOW)
const isCancelled = computed(() => props.invoice.booking?.status === 'cancelled');

// 🎯 TÍNH TOÁN KẾ TOÁN CẬP NHẬT
const subTotal = computed(() => {
    // Nếu hủy phòng, tiền phòng và dịch vụ coi như bằng 0 (vì thu cọc làm phí phạt)
    if (isCancelled.value) return 0;
    return Number(props.invoice.room_charge || 0) + Number(props.invoice.service_charge || 0);
});

const totalBill = computed(() => Number(props.invoice.total_amount || 0));
const paidAmount = computed(() => Number(props.invoice.amount_paid || 0));
const balance = computed(() => Math.max(0, totalBill.value - paidAmount.value));
const overstayMinutes = computed(() => {
    const expected = props.invoice.booking?.check_out_expected;
    const actual = props.invoice.booking?.check_out_actual || (props.invoice.booking?.status === 'checked_in' ? new Date().toISOString() : null);
    if (!expected || !actual) return 0;

    const expectedTime = new Date(expected).getTime();
    const actualTime = new Date(actual).getTime();
    if (Number.isNaN(expectedTime) || Number.isNaN(actualTime) || actualTime <= expectedTime) return 0;

    return Math.floor((actualTime - expectedTime) / 60000);
});

const overstayDurationText = computed(() => {
    if (overstayMinutes.value <= 0) return '';
    const hours = Math.floor(overstayMinutes.value / 60);
    const minutes = overstayMinutes.value % 60;
    if (hours > 0 && minutes > 0) return `${hours} giờ ${minutes} phút`;
    if (hours > 0) return `${hours} giờ`;
    return `${minutes} phút`;
});

const overstayHours = computed(() => {
    if (!overstayMinutes.value) return 0;
    return overstayMinutes.value > 60 ? Math.ceil((overstayMinutes.value - 60) / 60) : 0;
});

const overstayRoomText = computed(() => {
    const roomNumbers = (props.invoice.booking?.booking_rooms || [])
        .map((room) => room.room?.room_number)
        .filter(Boolean);

    if (!roomNumbers.length) return 'phòng đã đặt';
    return `phòng ${roomNumbers.join(', ')}`;
});

const shouldShowOverstayNotice = computed(() => {
    if (isCancelled.value) return false;
    return overstayHours.value > 0 || overstayMinutes.value > 0;
});

const roomLineCount = computed(() => (props.invoice.booking?.booking_rooms || []).length);
const serviceLineCount = computed(() => (props.invoice.booking?.booking_services || []).length);

// 🎯 LOGIC THANH TOÁN (ĐÃ SỬA ĐÚNG CỔNG BOOKINGS.PAY VÀ GẮN LOA)
const showPaymentModal = ref(false);
const paymentForm = useForm({
    payment_method: 'cash'
});

const processPayment = () => {
    paymentForm.post(route('admin.bookings.pay', props.invoice.booking_id), {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentModal.value = false;
        },
        onError: (errors) => {
            if (errors.error) alert("🛑 TỪ CHỐI: " + errors.error);
        }
    });
};

const printInvoice = () => window.print();
</script>

<template>
    <Head :title="`Hóa Đơn - ${invoice.invoice_code}`" />
    <AdminLayout>
        <div class="max-w-4xl mx-auto space-y-6 pb-12 animate-in fade-in duration-500 print:!m-0 print:!p-0 print:max-w-none">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 px-2 print:hidden">
                <div>
                    <span class="admin-index-subtitle mb-2 block">Tài chính & Doanh thu</span>
                    <h2 class="admin-index-title !text-3xl">Chi Tiết Hóa Đơn</h2>
                </div>
                <div class="flex gap-3">
                    <button @click="goBack" class="px-6 py-3 rounded-2xl bg-slate-100 text-slate-600 font-bold text-xs uppercase tracking-widest hover:bg-slate-200 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Trở về
                    </button>
                    <button @click="printInvoice" class="btn-primary !bg-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        In Hóa Đơn
                    </button>
                </div>
            </div>

            <div class="invoice-paper-standard relative shadow-2xl print:shadow-none print:border-none print:!rounded-none overflow-hidden">

                <div v-if="isCancelled" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -rotate-[15deg] pointer-events-none opacity-[0.08]">
                    <span class="text-[120px] font-black text-rose-600 border-[10px] border-rose-600 rounded-3xl px-10 py-2 uppercase tracking-widest">HỦY ĐƠN</span>
                </div>
                <div v-else-if="balance <= 0" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -rotate-[15deg] pointer-events-none opacity-[0.05]">
                    <span class="text-[120px] font-black text-emerald-600 border-[10px] border-emerald-600 rounded-3xl px-10 py-2 uppercase tracking-widest">ĐÃ THU</span>
                </div>

                <div class="p-10 sm:p-16 relative z-10">
                    <div class="flex justify-between items-start pb-8 border-b-2 border-slate-800">
                        <div class="space-y-1">
                            <span class="text-3xl font-black text-slate-900 tracking-tighter italic">DASHER<span class="text-slate-500">HOTEL</span></span>
                            <p class="text-[11px] text-slate-600 uppercase font-bold tracking-[0.2em] pt-2">Công ty Cổ phần Dịch vụ Lưu trú Dasher</p>
                            <p class="text-sm font-medium text-slate-600">Địa chỉ: Khu đô thị mới, TP. Long Xuyên, An Giang</p>
                        </div>
                        <div class="text-right">
                            <h1 class="text-3xl font-black text-slate-900 uppercase tracking-widest">HÓA ĐƠN</h1>
                            <p class="text-sm font-black text-primary-500 pt-1 uppercase tracking-widest">{{ invoice.invoice_code }}</p>
                            <p class="text-xs font-bold text-slate-400">Ngày lập: {{ formatDate(invoice.created_at) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-12 py-10">
                        <div class="space-y-2">
                            <p class="text-[11px] text-slate-400 uppercase font-bold tracking-[0.2em] border-b border-slate-100 pb-2 mb-3">Khách hàng</p>
                            <div class="text-sm"><span class="font-bold text-slate-400">Họ tên:</span> <span class="font-black uppercase text-slate-800 ml-2">{{ invoice.booking?.customer?.full_name }}</span></div>
                            <div class="text-sm"><span class="font-bold text-slate-400">SĐT:</span> <span class="font-bold text-slate-800 ml-2">{{ invoice.booking?.customer?.phone }}</span></div>
                        </div>
                        <div class="space-y-2">
                            <p class="text-[11px] text-slate-400 uppercase font-bold tracking-[0.2em] border-b border-slate-100 pb-2 mb-3">Lưu trú (Thực tế)</p>
                            <div class="flex justify-between text-sm"><span class="font-bold text-slate-400">Ngày đến:</span> <span class="font-bold text-slate-800">{{ formatDate(invoice.booking?.check_in_actual) }}</span></div>
                            <div class="flex justify-between text-sm"><span class="font-bold text-slate-400">Ngày đi:</span> <span class="font-bold text-slate-800">{{ formatDate(invoice.booking?.check_out_actual || invoice.booking?.check_out_expected) }}</span></div>
                        </div>
                    </div>

                    <div v-if="shouldShowOverstayNotice" class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
                        <p class="text-xs font-black uppercase tracking-wider text-amber-700">Thông báo lố giờ check-out</p>
                        <p class="mt-2 text-sm font-bold text-amber-800">
                            Khách đã trả theo hạn cũ, nhưng {{ overstayRoomText }} trả trễ
                            <span v-if="overstayHours > 0">{{ overstayHours }} giờ</span>
                            <span v-else-if="overstayDurationText">{{ overstayDurationText }}</span>
                            so với mốc check-out quy định.
                        </p>
                        <p class="mt-1 text-xs text-amber-700">
                            Check-out dự kiến: {{ formatDate(invoice.booking?.check_out_expected) }} | Check-out thực tế: {{ formatDate(invoice.booking?.check_out_actual) }}.
                        </p>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr class="text-[10px] font-black text-slate-500 uppercase tracking-widest">
                                    <th class="px-6 py-4 w-16">STT</th>
                                    <th class="px-6 py-4">Diễn giải nội dung</th>
                                    <th class="px-6 py-4 text-center">SL</th>
                                    <th class="px-6 py-4 text-right">Đơn giá</th>
                                    <th class="px-6 py-4 text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white text-sm">
                                <template v-if="!isCancelled">
                                    <tr v-for="(room, index) in invoice.booking?.booking_rooms" :key="room.id">
                                        <td class="px-6 py-4 text-center text-slate-400">{{ index + 1 }}</td>
                                        <td class="px-6 py-4 font-bold text-slate-900">Tiền phòng ({{ room.definition?.name }}) - P.{{ room.room?.room_number }}</td>
                                        <td class="px-6 py-4 text-center">1</td>
                                        <td class="px-6 py-4 text-right">{{ formatCurrency(room.price) }}</td>
                                        <td class="px-6 py-4 text-right font-black">{{ formatCurrency(room.price) }}</td>
                                    </tr>
                                    <tr v-for="(svc, index) in invoice.booking?.booking_services" :key="svc.id">
                                        <td class="px-6 py-4 text-center text-slate-400">{{ (invoice.booking?.booking_rooms?.length || 0) + index + 1 }}</td>
                                        <td class="px-6 py-4 font-bold text-slate-900">Dịch vụ: {{ svc.service?.name }}</td>
                                        <td class="px-6 py-4 text-center">{{ svc.quantity }}</td>
                                        <td class="px-6 py-4 text-right">{{ formatCurrency(svc.price) }}</td>
                                        <td class="px-6 py-4 text-right font-black">{{ formatCurrency(svc.total_price) }}</td>
                                    </tr>
                                </template>

                                <template v-else>
                                    <tr>
                                        <td class="px-6 py-4 text-center text-slate-400">1</td>
                                        <td class="px-6 py-4 font-bold text-rose-600">Phí hủy đơn đặt phòng (Khấu trừ tiền đặt cọc)</td>
                                        <td class="px-6 py-4 text-center">1</td>
                                        <td class="px-6 py-4 text-right">{{ formatCurrency(invoice.booking?.deposit_amount) }}</td>
                                        <td class="px-6 py-4 text-right font-black">{{ formatCurrency(invoice.booking?.deposit_amount) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 bg-rose-50/50 text-rose-500 italic text-xs text-center">
                                            Lý do: Khách hàng không đến làm thủ tục nhận phòng (No-show) sau thời gian quy định.
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-10 flex justify-end">
                        <div class="w-full sm:w-80 space-y-3">
                            <div class="flex justify-between text-sm font-bold text-slate-500">
                                <span>Tiền hàng (Subtotal):</span>
                                <span>{{ formatCurrency(subTotal) }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-bold text-slate-500 pb-3 border-b border-slate-100">
                                <span>Thuế GTGT:</span>
                                <span>+ {{ formatCurrency(invoice.tax_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-black text-slate-900 uppercase pt-2">
                                <span>Tổng hóa đơn:</span>
                                <span>{{ formatCurrency(totalBill) }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-bold text-emerald-600">
                                <span>{{ isCancelled ? 'Tiền cọc đã thu:' : 'Khách đã trả:' }}</span>
                                <span class="font-black">- {{ formatCurrency(paidAmount) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-4 border-t-2 border-slate-900">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Số tiền còn lại:</span>
                                <span class="text-2xl font-black tracking-tighter" :class="balance > 0 ? 'text-rose-600' : 'text-slate-900'">
                                    {{ formatCurrency(balance) }}
                                </span>
                            </div>
                            <p v-if="shouldShowOverstayNotice" class="text-[11px] font-bold text-amber-700 leading-relaxed">
                                Ghi chú: Hệ thống chỉ cảnh báo lố giờ để theo dõi.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8 p-10 sm:px-16 text-center border-t border-slate-50 mt-16">
                        <div>
                            <p class="text-xs font-black uppercase text-slate-800">Khách Hàng</p>
                            <div class="h-20"></div>
                        </div>
                        <div>
                            <p class="text-xs font-black uppercase text-slate-800">Người Thanh Toán</p>
                            <div class="h-20"></div>
                            <p class="text-sm font-bold text-slate-700 italic">{{ invoice.cashier?.name || 'Chưa ghi nhận' }}</p>
                            <p class="text-[11px] text-slate-500 font-medium mt-1">{{ invoice.paid_at ? `Lúc: ${formatDate(invoice.paid_at)}` : 'Thời gian: Chưa thanh toán' }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-rose-50 p-8 border-t-2 border-rose-100 flex flex-col sm:flex-row items-center justify-between gap-6 print:hidden" v-if="balance > 0">
                    <div class="text-center sm:text-left">
                        <h4 class="text-lg font-black text-rose-600 uppercase tracking-tight italic">Hóa đơn chưa tất toán!</h4>
                        <p class="text-xs font-bold text-rose-400 mt-1 uppercase tracking-widest">Cần thu thêm số tiền còn thiếu trước khi giao hóa đơn</p>
                    </div>
                    <button @click="showPaymentModal = true" class="btn-primary !bg-rose-600 !px-10 !py-4 shadow-xl shadow-rose-200 animate-pulse flex items-center gap-2">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.456-.342 1.456-1.096V5.625c0-.754-.729-1.294-1.456-1.096a60.864 60.864 0 00-15.797 2.102m15.797 2.102c-.343.093-.686.184-1.03.273m-10.122 3.167a.75.75 0 10-1.5 0 .75.75 0 001.5 0z" /></svg>
                        Thu Tiền Ngay: {{ formatCurrency(balance) }}
                    </button>
                </div>

            </div>
        </div>

        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showPaymentModal" class="fixed inset-0 z-[999] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-sm print:hidden">
                <div class="absolute inset-0" @click="showPaymentModal = false"></div>
                <div class="app-card !p-10 w-full max-w-md relative overflow-hidden z-10 animate-in zoom-in duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -mr-16 -mt-16 blur-2xl pointer-events-none"></div>

                    <div class="flex justify-between items-start mb-8 relative z-10">
                        <div>
                            <span class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.3em] block mb-1">Xác nhận</span>
                            <h3 class="text-title italic !text-2xl">Thu Tiền Khách</h3>
                        </div>
                        <button @click="showPaymentModal = false" class="index-action-btn !w-8 !h-8 !rounded-full hover:text-rose-500 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="space-y-6 relative z-10">
                        <div class="p-6 bg-slate-50 dark:bg-dark-bg rounded-2xl border border-slate-100 dark:border-dark-border text-center">
                            <p class="text-[10px] font-black text-muted-text uppercase tracking-widest mb-2">Số tiền cần thu thêm</p>
                            <p class="text-4xl font-black text-emerald-500 tracking-tighter italic">{{ formatCurrency(balance) }}</p>
                        </div>

                        <div class="space-y-3">
                            <label class="admin-index-subtitle px-1">Phương thức thanh toán</label>
                            <div class="form-input-pms flex items-center justify-between">
                                <span class="font-bold text-slate-700">Tiền mặt</span>
                                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600">Cố định</span>
                            </div>
                        </div>

                        <button @click="processPayment" :disabled="paymentForm.processing" class="btn-primary !bg-emerald-500 hover:!bg-emerald-600 !w-full !py-4 !text-[11px] !uppercase !tracking-widest">
                            {{ paymentForm.processing ? 'Đang xử lý...' : 'Xác nhận Đã Thu Tiền' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>

    </AdminLayout>
</template>

<style scoped>
/* 🎯 ÉP CHỦ ĐỀ SÁNG CỐ ĐỊNH */
.invoice-paper-standard {
    background-color: #ffffff !important;
    color: #0f172a !important;
    border-radius: 24px;
    border: 1px solid #e2e8f0;
}

.invoice-paper-standard :deep(*) {
    border-color: #e2e8f0 !important;
    color: inherit;
}

.invoice-paper-standard :deep(.bg-slate-50) {
    background-color: #f8fafc !important;
}

.invoice-paper-standard :deep(.bg-white) {
    background-color: #ffffff !important;
}

@media print {
    :deep(aside), :deep(nav), :deep(header), .print\:hidden, button {
        display: none !important;
    }
    .invoice-paper-standard {
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 0 !important;
        border: none !important;
        box-shadow: none !important;
        width: 100% !important;
    }
}
</style>
