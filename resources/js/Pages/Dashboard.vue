<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    dashboard: {
        type: Object,
        required: true,
    },
});

const reportMode = ref('daily');

const reportOptions = [
    { key: 'daily', label: 'Ngày' },
    { key: 'monthly', label: 'Tháng' },
    { key: 'yearly', label: 'Năm' },
];

const activeReport = computed(() => props.dashboard.revenue_reports?.[reportMode.value] ?? null);
const activeRows = computed(() => activeReport.value?.rows ?? []);
const activeTotals = computed(() => activeReport.value?.totals ?? {});

const revenueBars = computed(() => {
    const rows = activeRows.value;

    if (reportMode.value === 'daily') {
        return rows.slice(-14);
    }

    return rows;
});

const maxGrossRevenue = computed(() => {
    const maxValue = Math.max(0, ...revenueBars.value.map((row) => Number(row.gross_revenue || 0)));
    return maxValue > 0 ? maxValue : 1;
});

const roomStatuses = computed(() => {
    const rooms = props.dashboard.rooms ?? {};

    return [
        { key: 'available', label: 'Trống', color: 'bg-emerald-500', value: rooms.available ?? 0 },
        { key: 'occupied', label: 'Đang ở', color: 'bg-sky-500', value: rooms.occupied ?? 0 },
        { key: 'cleaning', label: 'Đang dọn', color: 'bg-amber-500', value: rooms.cleaning ?? 0 },
        { key: 'maintenance', label: 'Bảo trì', color: 'bg-rose-500', value: rooms.maintenance ?? 0 },
    ];
});

const bookingStatuses = computed(() => {
    const status = props.dashboard.bookings?.status ?? {};

    return [
        { key: 'pending', label: 'Chờ xác nhận', value: status.pending ?? 0 },
        { key: 'confirmed', label: 'Đã xác nhận', value: status.confirmed ?? 0 },
        { key: 'checked_in', label: 'Đang lưu trú', value: status.checked_in ?? 0 },
        { key: 'checked_out', label: 'Đã trả phòng', value: status.checked_out ?? 0 },
        { key: 'cancelled', label: 'Đã hủy', value: status.cancelled ?? 0 },
    ];
});

const kpiCards = computed(() => {
    const kpis = props.dashboard.kpis ?? {};
    const finance = props.dashboard.finance ?? {};
    const today = props.dashboard.operations_today ?? {};

    return [
        {
            key: 'revenue-collected',
            title: 'Doanh thu đã thu',
            value: formatCurrency(kpis.collected_revenue ?? 0),
            note: 'Tổng tiền đã thu từ hóa đơn',
            color: 'text-emerald-600',
            chip: 'Tài chính',
        },
        {
            key: 'revenue-outstanding',
            title: 'Công nợ còn lại',
            value: formatCurrency(kpis.outstanding_revenue ?? 0),
            note: 'Tổng tiền chưa tất toán',
            color: 'text-rose-600',
            chip: 'Cần xử lý',
        },
        {
            key: 'occupancy',
            title: 'Tỉ lệ lấp đầy',
            value: `${formatNumber(kpis.occupancy_rate ?? 0)}%`,
            note: `Phòng đang ở / tổng phòng: ${formatNumber(props.dashboard.rooms?.occupied ?? 0)}/${formatNumber(props.dashboard.rooms?.total ?? 0)}`,
            color: 'text-sky-600',
            chip: 'Vận hành',
        },
        {
            key: 'in-house',
            title: 'Khách đang ở',
            value: formatNumber(kpis.bookings_in_house ?? 0),
            note: `Check-in hôm nay: ${formatNumber(today.expected_check_in ?? 0)}`,
            color: 'text-violet-600',
            chip: 'Lễ tân',
        },
        {
            key: 'month-gross',
            title: 'Doanh thu tháng này',
            value: formatCurrency(finance.gross_this_month ?? 0),
            note: `Đã thu tháng này: ${formatCurrency(finance.collected_this_month ?? 0)}`,
            color: 'text-amber-600',
            chip: 'Theo tháng',
        },
        {
            key: 'today-bookings',
            title: 'Đặt phòng mới hôm nay',
            value: formatNumber(today.new_bookings ?? 0),
            note: `Check-out dự kiến: ${formatNumber(today.expected_check_out ?? 0)}`,
            color: 'text-indigo-600',
            chip: 'Hôm nay',
        },
    ];
});

function formatCurrency(value) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0,
    }).format(Number(value || 0));
}

function formatNumber(value) {
    return new Intl.NumberFormat('vi-VN', {
        maximumFractionDigits: 2,
    }).format(Number(value || 0));
}

function paymentStatusLabel(status) {
    const map = {
        unpaid: 'Chưa thu',
        partial: 'Thu một phần',
        paid: 'Đã thu',
    };

    return map[status] ?? status;
}

function paymentStatusClass(status) {
    if (status === 'paid') {
        return 'text-emerald-600 bg-emerald-50';
    }

    if (status === 'partial') {
        return 'text-amber-600 bg-amber-50';
    }

    return 'text-rose-600 bg-rose-50';
}
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <section class="app-card mb-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="admin-index-subtitle mb-2">Điều hành tổng quan</p>
                    <h1 class="admin-index-title !text-3xl">Dashboard vận hành khách sạn</h1>
                    <p class="text-desc mt-2 max-w-3xl">
                        Tổng hợp sức khỏe kinh doanh và lễ tân theo thời gian thực. Báo cáo doanh thu chi tiết theo ngày, tháng, năm để bạn theo dõi xu hướng và công nợ.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 text-right">
                    <div class="rounded-2xl bg-slate-50 dark:bg-dark-bg px-4 py-3 border border-slate-100 dark:border-dark-border">
                        <p class="text-[10px] uppercase tracking-widest text-muted-text font-bold">Tổng hóa đơn</p>
                        <p class="text-lg font-black text-main-text dark:text-white">{{ formatNumber(dashboard.revenue_reports?.daily?.totals?.invoice_count ?? 0) }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 dark:bg-dark-bg px-4 py-3 border border-slate-100 dark:border-dark-border">
                        <p class="text-[10px] uppercase tracking-widest text-muted-text font-bold">Công nợ mở</p>
                        <p class="text-lg font-black text-main-text dark:text-white">{{ formatNumber(dashboard.finance?.open_invoices ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-8">
            <article
                v-for="card in kpiCards"
                :key="card.key"
                class="app-card !p-6 border-slate-100 hover:shadow-app-dark transition-all"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-text">{{ card.title }}</p>
                        <p :class="card.color" class="text-2xl font-black mt-3 tracking-tight">{{ card.value }}</p>
                        <p class="text-xs text-muted-text mt-2">{{ card.note }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600">{{ card.chip }}</span>
                </div>
            </article>
        </section>

        <section class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <article class="app-card !p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em] font-black text-muted-text">Báo cáo doanh thu</p>
                            <h2 class="text-xl font-black text-main-text mt-1">Phân tích theo ngày, tháng, năm</h2>
                            <p class="text-xs text-muted-text mt-1">
                                Phạm vi: {{ activeReport?.from }} đến {{ activeReport?.to }}
                            </p>
                        </div>

                        <div class="inline-flex p-1 rounded-2xl bg-slate-100">
                            <button
                                v-for="option in reportOptions"
                                :key="option.key"
                                @click="reportMode = option.key"
                                :class="reportMode === option.key ? 'bg-white text-primary-600 shadow-sm' : 'text-slate-500 hover:text-main-text'"
                                class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all"
                            >
                                {{ option.label }}
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-black text-muted-text">Doanh thu gộp</p>
                            <p class="text-base font-black text-main-text mt-2">{{ formatCurrency(activeTotals.gross_revenue ?? 0) }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-black text-muted-text">Đã thu</p>
                            <p class="text-base font-black text-emerald-600 mt-2">{{ formatCurrency(activeTotals.collected_revenue ?? 0) }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-black text-muted-text">Công nợ</p>
                            <p class="text-base font-black text-rose-600 mt-2">{{ formatCurrency(activeTotals.outstanding_revenue ?? 0) }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-[10px] uppercase tracking-wider font-black text-muted-text">Hóa đơn</p>
                            <p class="text-base font-black text-main-text mt-2">{{ formatNumber(activeTotals.invoice_count ?? 0) }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="row in revenueBars"
                            :key="row.period_key"
                            class="p-3 rounded-2xl border border-slate-100 bg-slate-50/60"
                        >
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <p class="text-xs font-black text-main-text uppercase tracking-wider">{{ row.period_label }}</p>
                                <p class="text-xs font-bold text-muted-text">{{ formatCurrency(row.gross_revenue) }}</p>
                            </div>
                            <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
                                <div
                                    class="h-full rounded-full bg-primary-500"
                                    :style="{ width: `${(Number(row.gross_revenue || 0) / maxGrossRevenue) * 100}%` }"
                                ></div>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-[11px]">
                                <span class="text-emerald-600 font-bold">Thu: {{ formatCurrency(row.collected_revenue) }}</span>
                                <span class="text-rose-600 font-bold">Nợ: {{ formatCurrency(row.outstanding_revenue) }}</span>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="app-card !p-0 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-main-text">Chi tiết doanh thu</h3>
                        <span class="text-[11px] text-muted-text font-bold">{{ activeRows.length }} kỳ báo cáo</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="index-table min-w-[920px]">
                            <thead class="index-table-head">
                                <tr class="index-table-head-row">
                                    <th class="index-table-th">Kỳ</th>
                                    <th class="index-table-th">Booking</th>
                                    <th class="index-table-th">Hóa đơn</th>
                                    <th class="index-table-th">Tiền phòng</th>
                                    <th class="index-table-th">Dịch vụ</th>
                                    <th class="index-table-th">Thuế</th>
                                    <th class="index-table-th">Tổng doanh thu</th>
                                    <th class="index-table-th">Đã thu</th>
                                    <th class="index-table-th">Công nợ</th>
                                </tr>
                            </thead>
                            <tbody class="index-table-body">
                                <tr
                                    v-for="row in activeRows"
                                    :key="row.period_key"
                                    class="index-table-row"
                                >
                                    <td class="px-6 py-3 font-bold text-main-text">{{ row.period_label }}</td>
                                    <td class="px-6 py-3 text-sm">{{ formatNumber(row.booking_count) }}</td>
                                    <td class="px-6 py-3 text-sm">{{ formatNumber(row.invoice_count) }}</td>
                                    <td class="px-6 py-3 text-sm">{{ formatCurrency(row.room_revenue) }}</td>
                                    <td class="px-6 py-3 text-sm">{{ formatCurrency(row.service_revenue) }}</td>
                                    <td class="px-6 py-3 text-sm">{{ formatCurrency(row.tax_total) }}</td>
                                    <td class="px-6 py-3 text-sm font-bold">{{ formatCurrency(row.gross_revenue) }}</td>
                                    <td class="px-6 py-3 text-sm text-emerald-600 font-bold">{{ formatCurrency(row.collected_revenue) }}</td>
                                    <td class="px-6 py-3 text-sm text-rose-600 font-bold">{{ formatCurrency(row.outstanding_revenue) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>

            <div class="space-y-6">
                <article class="app-card !p-6">
                    <h3 class="text-sm font-black uppercase tracking-[0.2em] text-main-text mb-4">Tình trạng phòng</h3>
                    <div class="space-y-3">
                        <div
                            v-for="status in roomStatuses"
                            :key="status.key"
                            class="rounded-2xl border border-slate-100 p-3 bg-slate-50/60 flex items-center justify-between"
                        >
                            <div class="flex items-center gap-2.5">
                                <span :class="status.color" class="w-2.5 h-2.5 rounded-full"></span>
                                <span class="text-sm font-bold text-main-text">{{ status.label }}</span>
                            </div>
                            <span class="text-sm font-black text-main-text">{{ formatNumber(status.value) }}</span>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-100 text-xs font-bold text-muted-text">
                        Tổng phòng: <span class="text-main-text">{{ formatNumber(dashboard.rooms?.total ?? 0) }}</span>
                    </div>
                </article>

                <article class="app-card !p-6">
                    <h3 class="text-sm font-black uppercase tracking-[0.2em] text-main-text mb-4">Trạng thái đặt phòng</h3>
                    <div class="space-y-2.5">
                        <div
                            v-for="status in bookingStatuses"
                            :key="status.key"
                            class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2"
                        >
                            <span class="text-xs font-bold text-main-text">{{ status.label }}</span>
                            <span class="text-xs font-black text-primary-600">{{ formatNumber(status.value) }}</span>
                        </div>
                    </div>
                </article>

                <article class="app-card !p-0 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-main-text">Hóa đơn gần đây</h3>
                        <Link :href="route('admin.invoices.index')" class="text-[11px] font-black uppercase tracking-wider text-primary-600 hover:underline">
                            Xem tất cả
                        </Link>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <div
                            v-for="invoice in dashboard.recent_invoices"
                            :key="invoice.id"
                            class="px-5 py-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-black text-main-text">{{ invoice.invoice_code }}</p>
                                    <p class="text-[11px] text-muted-text mt-1">{{ invoice.customer_name || 'Khách lẻ' }}</p>
                                    <p class="text-[11px] text-muted-text">{{ invoice.booking_code || '-' }}</p>
                                </div>
                                <span :class="paymentStatusClass(invoice.payment_status)" class="px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-wider">
                                    {{ paymentStatusLabel(invoice.payment_status) }}
                                </span>
                            </div>
                            <div class="mt-3 flex items-center justify-between text-xs">
                                <span class="text-muted-text">Tổng: <strong class="text-main-text">{{ formatCurrency(invoice.total_amount) }}</strong></span>
                                <span class="text-emerald-600">Đã thu: <strong>{{ formatCurrency(invoice.amount_paid) }}</strong></span>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </AdminLayout>
</template>
