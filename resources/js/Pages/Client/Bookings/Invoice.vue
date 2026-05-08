<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    booking: { type: Object, required: true },
    room_items: { type: Array, default: () => [] },
    service_items: { type: Array, default: () => [] },
    invoice_summary: { type: Object, default: () => ({}) },
    hotel_info: { type: Object, default: () => ({}) },
});

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
}).format(Number(value || 0));

const formatDate = (value) => {
    if (!value) return '--/--/----';

    return new Date(value).toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};

const formatDateTime = (value) => {
    if (!value) return '--:-- --/--/----';

    return new Date(value).toLocaleString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};

const totalBill = computed(() => Number(props.invoice_summary.total_amount || 0));
const paidAmount = computed(() => Number(props.invoice_summary.paid_amount || 0));
const balance = computed(() => Math.max(0, totalBill.value - paidAmount.value));
const roomLineCount = computed(() => props.room_items.length);
const serviceLineCount = computed(() => props.service_items.length);
const subtotal = computed(() => Number(props.invoice_summary.room_subtotal || 0) + Number(props.invoice_summary.service_subtotal || 0));
const hasPaid = computed(() => (props.invoice_summary.payment_status || '') === 'paid');
const cashierName = computed(() => props.invoice_summary.cashier_name || 'Chưa ghi nhận');
const paidAtText = computed(() => formatDateTime(props.invoice_summary.paid_at || booking.booked_at));

const printInvoice = () => window.print();
</script>

<template>
    <Head :title="`Hóa đơn - ${booking.booking_code}`" />

    <div class="min-h-screen bg-slate-100 text-slate-900">
        <div class="max-w-4xl mx-auto space-y-6 pb-12 pt-6 animate-in fade-in duration-500 print:!m-0 print:!p-0 print:max-w-none">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 px-2 print:hidden">
                <div>
                    <span class="admin-index-subtitle mb-2 block">Đối soát hóa đơn khách hàng</span>
                    <h2 class="admin-index-title !text-3xl">Hóa Đơn Chi Tiết</h2>
                    <p class="text-desc mt-2">Hóa đơn chi tiết của khách {{ booking.customer_name || 'Khách đặt phòng' }} cho booking #{{ booking.booking_code }}.</p>
                </div>
                <div class="flex gap-3">
                    <button @click="printInvoice" class="btn-primary !bg-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        In Hóa Đơn
                    </button>
                </div>
            </div>

            <div class="invoice-paper-standard relative shadow-2xl print:shadow-none print:border-none print:!rounded-none overflow-hidden">
                <div v-if="hasPaid" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 -rotate-[15deg] pointer-events-none opacity-[0.05]">
                    <span class="text-[120px] font-black text-emerald-600 border-[10px] border-emerald-600 rounded-3xl px-10 py-2 uppercase tracking-widest">ĐÃ THU</span>
                </div>

                <div class="p-10 sm:p-16 relative z-10">
                    <div class="flex justify-between items-start pb-8 border-b-2 border-slate-800">
                        <div class="space-y-1">
                            <span class="text-3xl font-black text-slate-900 tracking-tighter italic">DASHER<span class="text-slate-500">HOTEL</span></span>
                            <p class="text-[11px] text-slate-600 uppercase font-bold tracking-[0.2em] pt-2">Công ty Cổ phần Dịch vụ Lưu trú Dasher</p>
                            <p class="text-sm font-medium text-slate-600">Địa chỉ: {{ hotel_info.address }}</p>
                        </div>
                        <div class="text-right">
                            <h1 class="text-3xl font-black text-slate-900 uppercase tracking-widest">HÓA ĐƠN</h1>
                            <p class="text-sm font-black text-primary-500 pt-1 uppercase tracking-widest">{{ booking.booking_code }}</p>
                            <p class="text-xs font-bold text-slate-400">Ngày lập: {{ formatDateTime(booking.booked_at) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-12 py-10">
                        <div class="space-y-2">
                            <p class="text-[11px] text-slate-400 uppercase font-bold tracking-[0.2em] border-b border-slate-100 pb-2 mb-3">Khách hàng</p>
                            <div class="text-sm"><span class="font-bold text-slate-400">Họ tên:</span> <span class="font-black uppercase text-slate-800 ml-2">{{ booking.customer_name || 'Khách đặt phòng' }}</span></div>
                            <div class="text-sm"><span class="font-bold text-slate-400">Số đơn:</span> <span class="font-bold text-slate-800 ml-2">#{{ booking.booking_code }}</span></div>
                            <div class="text-sm"><span class="font-bold text-slate-400">Trạng thái:</span> <span class="font-bold text-emerald-600 ml-2 uppercase">{{ hasPaid ? 'Đã thanh toán' : 'Chưa tất toán' }}</span></div>
                        </div>
                        <div class="space-y-2">
                            <p class="text-[11px] text-slate-400 uppercase font-bold tracking-[0.2em] border-b border-slate-100 pb-2 mb-3">Lưu trú (Dự kiến)</p>
                            <div class="flex justify-between text-sm"><span class="font-bold text-slate-400">Ngày đến:</span> <span class="font-bold text-slate-800">{{ formatDate(booking.check_in_expected) }}</span></div>
                            <div class="flex justify-between text-sm"><span class="font-bold text-slate-400">Ngày đi:</span> <span class="font-bold text-slate-800">{{ formatDate(booking.check_out_expected) }}</span></div>
                        </div>
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
                                <tr v-for="(room, index) in room_items" :key="`room-${index}`">
                                    <td class="px-6 py-4 text-center text-slate-400">{{ index + 1 }}</td>
                                    <td class="px-6 py-4 font-bold text-slate-900">Tiền phòng ({{ room.room_type || 'Phòng' }}) - P.{{ room.room_number || '--' }}</td>
                                    <td class="px-6 py-4 text-center">{{ room.nights || 1 }}</td>
                                    <td class="px-6 py-4 text-right">{{ formatCurrency(room.nightly_rate || 0) }}</td>
                                    <td class="px-6 py-4 text-right font-black">{{ formatCurrency(room.subtotal || 0) }}</td>
                                </tr>
                                <tr v-for="(service, index) in service_items" :key="`service-${index}`">
                                    <td class="px-6 py-4 text-center text-slate-400">{{ roomLineCount + index + 1 }}</td>
                                    <td class="px-6 py-4 font-bold text-slate-900">Dịch vụ: {{ service.name || 'Dịch vụ POS' }}</td>
                                    <td class="px-6 py-4 text-center">{{ service.quantity || 0 }}</td>
                                    <td class="px-6 py-4 text-right">{{ formatCurrency(service.unit_price || 0) }}</td>
                                    <td class="px-6 py-4 text-right font-black">{{ formatCurrency(service.total_price || 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-10 flex justify-end">
                        <div class="w-full sm:w-80 space-y-3">
                            <div class="flex justify-between text-sm font-bold text-slate-500">
                                <span>Tiền phòng & dịch vụ:</span>
                                <span>{{ formatCurrency(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-bold text-slate-500 pb-3 border-b border-slate-100">
                                <span>Tiền cọc:</span>
                                <span>- {{ formatCurrency(invoice_summary.deposit_amount || 0) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-black text-slate-900 uppercase pt-2">
                                <span>Tổng hóa đơn:</span>
                                <span>{{ formatCurrency(totalBill) }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-bold text-emerald-600">
                                <span>Khách đã trả:</span>
                                <span class="font-black">- {{ formatCurrency(paidAmount) }}</span>
                            </div>
                            <div class="flex justify-between items-center pt-4 border-t-2 border-slate-900">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Số tiền còn lại:</span>
                                <span class="text-2xl font-black tracking-tighter" :class="balance > 0 ? 'text-rose-600' : 'text-slate-900'">
                                    {{ formatCurrency(balance) }}
                                </span>
                            </div>
                            <p class="text-[11px] font-bold text-emerald-700 leading-relaxed" v-if="hasPaid">
                                Hóa đơn này đã được tất toán và hiển thị theo thông tin thanh toán thực tế.
                            </p>
                            <p class="text-[11px] font-bold text-rose-600 leading-relaxed" v-else>
                                Hóa đơn này chưa tất toán đủ, nhưng vẫn đang hiển thị để bạn kiểm tra chi tiết.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8 p-10 sm:px-16 text-center border-t border-slate-50 mt-16">
                        <div>
                            <p class="text-xs font-black uppercase text-slate-800">Khách Hàng</p>
                            <div class="h-20"></div>
                        </div>
                        <div>
                            <p class="text-xs font-black uppercase text-slate-800">Người Xác Nhận</p>
                            <div class="h-20"></div>
                            <p class="text-sm font-bold text-slate-700 italic">{{ cashierName }}</p>
                            <p class="text-[11px] text-slate-500 font-medium mt-1">{{ hasPaid ? `Thời gian: ${paidAtText}` : 'Thời gian: Chưa thanh toán' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
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
    :deep(aside),
    :deep(nav),
    :deep(header),
    .print\:hidden,
    button {
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
