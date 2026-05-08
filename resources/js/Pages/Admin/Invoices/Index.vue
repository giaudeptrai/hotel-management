<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import debounce from 'lodash/debounce';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    invoices: Object,
    filters: Object,
    // Nhan du lieu tu Backend de do vao bo loc
    rooms: Array,
    roomDefinitions: Array
});

// Khoi tao trang thai bo loc
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const dateType = ref(props.filters?.date_type || 'all');
const dateValue = ref(props.filters?.date_value || '');
const roomId = ref(props.filters?.room_id || '');
const roomDefId = ref(props.filters?.room_definition_id || '');
const { flashSuccess } = useAdminFlash();

// Tu dong gui request khi thay doi cac o loc
watch([search, status, dateType, dateValue, roomId, roomDefId], debounce(([newSearch, newStatus, newType, newValue, newRoom, newDef]) => {
    if (newType === 'all' && newValue !== '') {
        dateValue.value = '';
        return;
    }

    router.get(route('admin.invoices.index'), {
        search: newSearch,
        status: newStatus,
        date_type: newType,
        date_value: newValue,
        room_id: newRoom,
        room_definition_id: newDef
    }, { preserveState: true, replace: true });
}, 300));

// Ham xoa sach bo loc ve mac dinh
const clearFilters = () => {
    search.value = '';
    status.value = '';
    dateType.value = 'all';
    dateValue.value = '';
    roomId.value = '';
    roomDefId.value = '';
};

// Ham xuat Excel kem theo xac nhan
const exportExcel = () => {
    const confirmMsg = "Ban co chac chan muon xuat bao cao Excel cho danh sach hoa don hien tai khong?";

    if (confirm(confirmMsg)) {
        const queryParams = new URLSearchParams({
            search: search.value,
            status: status.value,
            date_type: dateType.value,
            date_value: dateValue.value,
            room_id: roomId.value,
            room_definition_id: roomDefId.value
        }).toString();

        window.location.href = route('admin.invoices.export') + '?' + queryParams;
    }
};

// Helpers dinh dang tien te va thoi gian
const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);
const formatDate = (dateString) => {
    if (!dateString) return '---';
    return new Date(dateString).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

// Ham lay danh sach so phong de hien thi tren bang
const getDisplayRooms = (invoice) => {
    const bookingRooms = invoice.booking?.booking_rooms || [];
    if (bookingRooms.length === 0) return 'N/A';
    return bookingRooms.map(br => br.room?.room_number || '...').join(', ');
};

// TINH TOAN THONG KE NHANH
const pageStats = computed(() => {
    let total = 0, paid = 0, debt = 0;
    const dataList = props.invoices?.data || props.invoices || [];

    if (Array.isArray(dataList)) {
        dataList.forEach(inv => {
            total += Number(inv.total_amount || 0);
            paid += Number(inv.amount_paid || 0);
            debt += Math.max(0, Number(inv.total_amount || 0) - Number(inv.amount_paid || 0));
        });
    }
    return { total, paid, debt, count: Array.isArray(dataList) ? dataList.length : 0 };
});

const getStatusConfig = (status) => {
    const configs = {
        unpaid: { text: 'No / Chua thu', class: 'bg-rose-100 text-rose-600 border border-rose-200' },
        partial: { text: 'Thu mot phan', class: 'bg-amber-100 text-amber-600 border border-amber-200' },
        paid: { text: 'Da tat toan', class: 'bg-emerald-100 text-emerald-600 border border-emerald-200' }
    };
    return configs[status] || { text: status, class: 'bg-slate-100 text-slate-600' };
};

const getRowNumber = (index) => {
    const currentPage = Number(props.invoices?.current_page || 1);
    const perPage = Number(props.invoices?.per_page || (props.invoices?.data?.length || 0));
    return (currentPage - 1) * perPage + index + 1;
};
</script>

<template>
    <Head title="Quản lý Hóa Đơn & Doanh Thu" />
    <AdminLayout>
        <SuccessToast :message="flashSuccess" />

        <div class="max-w-7xl mx-auto space-y-8 pb-12 animate-in fade-in duration-500">

            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 px-2">
                <div>
                    <span class="admin-index-subtitle mb-2 block">Tài chính & Doanh thu</span>
                    <h2 class="admin-index-title !text-3xl">Trung Tâm Hóa Đơn</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="app-card !p-6 flex items-center justify-between border-l-4 !border-l-primary-500">
                    <div>
                        <p class="admin-index-subtitle">Tổng Giá Trị Hóa Đơn</p>
                        <p class="text-2xl font-black text-main-text dark:text-white tracking-tighter mt-1">{{ formatCurrency(pageStats.total) }}</p>
                    </div>
                </div>
                <div class="app-card !p-6 flex items-center justify-between border-l-4 !border-l-emerald-500">
                    <div>
                        <p class="admin-index-subtitle !text-emerald-500">Thực Thu (Đã vào két)</p>
                        <p class="text-2xl font-black text-emerald-500 tracking-tighter mt-1">{{ formatCurrency(pageStats.paid) }}</p>
                    </div>
                </div>
                <div class="app-card !p-6 flex items-center justify-between border-l-4 !border-l-rose-500">
                    <div>
                        <p class="admin-index-subtitle !text-rose-500">Công Nợ (Cần thu thêm)</p>
                        <p class="text-2xl font-black text-rose-500 tracking-tighter mt-1">{{ formatCurrency(pageStats.debt) }}</p>
                    </div>
                </div>
            </div>

            <div class="app-card !py-6 !px-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <select v-model="status" class="form-input-pms form-input-pms-compact cursor-pointer">
                        <option value="">Tất cả trạng thái</option>
                        <option value="unpaid">Đang nợ / Chưa thu</option>
                        <option value="partial">Thu một phần</option>
                        <option value="paid">Đã tất toán</option>
                    </select>

                    <select v-model="dateType" class="form-input-pms form-input-pms-compact cursor-pointer">
                        <option value="all">Tất cả thời gian</option>
                        <option value="day">Theo Ngày</option>
                        <option value="month">Theo Tháng</option>
                        <option value="year">Theo Năm</option>
                    </select>

                    <div class="w-full">
                        <input v-if="dateType === 'day'" type="date" v-model="dateValue" class="form-input-pms form-input-pms-compact cursor-pointer">
                        <input v-if="dateType === 'month'" type="month" v-model="dateValue" class="form-input-pms form-input-pms-compact cursor-pointer">
                        <input v-if="dateType === 'year'" type="number" min="2020" max="2099" placeholder="Năm" v-model="dateValue" class="form-input-pms form-input-pms-compact">
                        <div v-if="dateType === 'all'" class="form-input-pms form-input-pms-compact bg-slate-100 text-slate-400 flex items-center">Toàn thời gian</div>
                    </div>

                    <select v-model="roomId" class="form-input-pms form-input-pms-compact cursor-pointer">
                        <option value="">Tất cả phòng</option>
                        <option v-for="room in rooms" :key="room.id" :value="room.id">Phòng {{ room.room_number }}</option>
                    </select>
                </div>

                <div class="flex flex-col lg:flex-row gap-4 items-center">
                    <select v-model="roomDefId" class="form-input-pms form-input-pms-compact !w-full lg:!w-64 cursor-pointer">
                        <option value="">Tất cả hạng phòng</option>
                        <option v-for="def in roomDefinitions" :key="def.id" :value="def.id">{{ def.name }}</option>
                    </select>

                    <input type="text" v-model="search" placeholder="Mã HĐ, Mã Booking, Tên KH..." class="form-input-pms form-input-pms-compact !w-full lg:flex-1">

                    <div class="flex gap-2 w-full lg:w-auto shrink-0">
                        <button v-if="search || status || dateValue || dateType !== 'all' || roomId || roomDefId"
                                @click="clearFilters"
                                class="px-6 py-3 rounded-2xl bg-rose-50 text-rose-500 font-bold text-xs uppercase tracking-widest hover:bg-rose-100 transition-all border-2 border-transparent">
                            Xóa Lọc
                        </button>

                        <button @click="exportExcel" class="btn-primary !py-3 !px-6 text-[11px] uppercase tracking-widest flex-1 lg:flex-none">
                            Xuất Excel
                        </button>
                    </div>
                </div>
            </div>

            <div class="index-table-card shadow-2xl">
                <div class="overflow-x-auto custom-scrollbar dark:custom-scrollbar-dark">
                    <table class="index-table">
                        <thead class="index-table-head !bg-slate-800 dark:!bg-slate-900">
                            <tr class="index-table-head-row !text-slate-300">
                                <th class="index-table-th text-center w-16">STT</th>
                                <th class="index-table-th">Chứng từ</th>
                                <th class="index-table-th">Khách hàng</th>
                                <th class="index-table-th text-center">Phòng đặt</th>
                                <th class="index-table-th text-right">Tổng Mức (VNĐ)</th>
                                <th class="index-table-th text-right">Thực Thu / Công Nợ</th>
                                <th class="index-table-th text-center">Trạng thái</th>
                                <th class="index-table-th">Nhân sự thực hiện</th>
                                <th class="index-table-th-right text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="index-table-body">
                            <tr v-for="(invoice, index) in (invoices?.data || [])" :key="invoice.id" class="index-table-row">
                                <td class="index-table-th text-center">
                                    <span class="text-xs font-black text-muted-text">{{ getRowNumber(index) }}</span>
                                </td>
                                <td class="index-table-th">
                                    <div class="flex flex-col">
                                        <span class="font-black text-main-text dark:text-white uppercase tracking-widest text-sm">{{ invoice.invoice_code }}</span>
                                        <span class="text-[10px] font-bold text-muted-text mt-1">{{ formatDate(invoice.created_at) }}</span>
                                    </div>
                                </td>
                                <td class="index-table-th">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-main-text dark:text-white uppercase">{{ invoice.booking?.customer?.full_name || 'Khách lẻ' }}</span>
                                        <Link :href="route().has('admin.bookings.show') ? route('admin.bookings.show', invoice.booking_id) : '#'" class="text-[10px] font-black text-primary-500 hover:text-primary-600 mt-1 uppercase tracking-widest">
                                            Booking: {{ invoice.booking?.booking_code || 'N/A' }}
                                        </Link>
                                    </div>
                                </td>
                                <td class="index-table-th text-center">
                                    <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg font-black text-slate-700 dark:text-slate-300 text-xs">
                                        P.{{ getDisplayRooms(invoice) }}
                                    </span>
                                </td>
                                <td class="index-table-th text-right">
                                    <span class="font-black text-main-text dark:text-white">{{ formatCurrency(invoice.total_amount) }}</span>
                                </td>
                                <td class="index-table-th text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="font-black text-emerald-500 text-xs">+ {{ formatCurrency(invoice.amount_paid) }}</span>
                                        <span v-if="(invoice.total_amount - invoice.amount_paid) > 0" class="text-[10px] font-black text-rose-500 mt-1 px-2 py-0.5 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 rounded tracking-widest">
                                            NỢ: {{ formatCurrency(invoice.total_amount - invoice.amount_paid) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="index-table-th text-center">
                                    <span :class="getStatusConfig(invoice.payment_status).class" class="px-3 py-1 rounded-md text-[9px] font-black uppercase tracking-widest">
                                        {{ getStatusConfig(invoice.payment_status).text }}
                                    </span>
                                </td>
                                <td class="index-table-th">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 dark:text-slate-300 text-xs uppercase">{{ invoice.cashier?.name || 'Hệ thống Auto' }}</span>
                                        <span class="text-desc !text-[9px] mt-1">{{ invoice.payment_method ? `Thanh toán: ${invoice.payment_method}` : 'Chưa giao dịch' }}</span>
                                    </div>
                                </td>
                                <td class="index-table-th index-actions index-table-th-right justify-center">
                                    <Link :href="route().has('admin.invoices.show') ? route('admin.invoices.show', invoice.id) : '#'" class="text-xs font-bold text-primary-500 hover:underline px-2 py-1">
                                        Xem Chi Tiết
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!invoices?.data?.length">
                                <td colspan="9" class="index-empty-cell">
                                    <p class="index-empty-text">Chưa có hóa đơn nào được hệ thống ghi nhận.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="invoices?.links" class="index-pagination flex justify-center">
                    <Pagination :links="invoices.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
