<script setup>
import { ref, computed, watch } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AddServiceModal from './Partials/AddServiceModal.vue';
import BookingDetailModal from './Partials/BookingDetailModal.vue';
import BookingOrdersList from './Partials/BookingOrdersList.vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({
    bookings: Object,
    roomsMatrix: Array,
    filters: Object,
    services: Array,
});

const activeTab = ref(props.filters.tab || 'matrix');
const { flashSuccess } = useAdminFlash();

// Ngày mặc định: Hôm nay và Ngày mai
const getToday = () => new Date().toISOString().split('T')[0];
const getTomorrow = () => {
    const d = new Date();
    d.setDate(d.getDate() + 1);
    return d.toISOString().split('T')[0];
};

const search = ref(props.filters.search || '');
const source = ref(props.filters.source || '');
const status = ref(props.filters.status || '');
const startDate = ref(props.filters.start_date || getToday());
const endDate = ref(props.filters.end_date || getTomorrow());
const listStartDate = ref(props.filters.list_start_date || '');
const listEndDate = ref(props.filters.list_end_date || '');
const matrixSearch = ref(''); // Tìm nhanh trên sơ đồ

// Lọc backend (đổi ngày)
const handleDateFilter = () => {
    router.get(route('admin.bookings.index'), {
        tab: 'matrix',
        start_date: startDate.value,
        end_date: endDate.value,
        search: search.value,
        source: source.value,
        status: status.value,
        list_start_date: listStartDate.value,
        list_end_date: listEndDate.value,
    }, {
        preserveState: true,
        replace: true,
        onSuccess: () => { activeTab.value = 'matrix'; }
    });
};

const applyListFilters = () => {
    router.get(route('admin.bookings.index'), {
        tab: 'list',
        search: search.value,
        source: source.value,
        status: status.value,
        list_start_date: listStartDate.value,
        list_end_date: listEndDate.value,
        start_date: startDate.value,
        end_date: endDate.value,
    }, {
        preserveState: true,
        replace: true,
        onSuccess: () => { activeTab.value = 'list'; }
    });
};

// NÚT BỎ LỌC (RESET)
const clearFilters = () => {
    startDate.value = getToday();
    endDate.value = getTomorrow();
    listStartDate.value = '';
    listEndDate.value = '';
    search.value = '';
    source.value = '';
    status.value = '';
    matrixSearch.value = '';
    handleDateFilter();
};

const clearListFilters = () => {
    search.value = '';
    source.value = '';
    status.value = '';
    listStartDate.value = '';
    listEndDate.value = '';
    applyListFilters();
};

const getCurrentBooking = (room) => {
    if (!room.booking_rooms || room.booking_rooms.length === 0) return null;

    const bookings = room.booking_rooms
        .map((item) => item.booking)
        .filter(Boolean);

    if (bookings.length === 0) return null;

    const priority = {
        checked_in: 3,
        confirmed: 2,
        pending: 1,
    };

    return bookings.sort((a, b) => {
        const pA = priority[a.status] || 0;
        const pB = priority[b.status] || 0;

        if (pA !== pB) return pB - pA;

        return new Date(a.check_in_expected) - new Date(b.check_in_expected);
    })[0];
};

// Lọc Frontend cho sơ đồ Matrix (Tìm số phòng hoặc tên khách)
const filteredGroupedRooms = computed(() => {
    if (!props.roomsMatrix) return {};
    return props.roomsMatrix.reduce((groups, room) => {
        // Lọc theo từ khóa tìm kiếm nhanh
        if (matrixSearch.value) {
            const query = matrixSearch.value.toLowerCase();
            const roomMatch = room.room_number.toString().includes(query);
            const customerMatch = getCurrentBooking(room)?.customer?.full_name?.toLowerCase().includes(query);
            if (!roomMatch && !customerMatch) return groups;
        }

        const floor = room.floor || 'Khác';
        if (!groups[floor]) groups[floor] = [];
        groups[floor].push(room);
        return groups;
    }, {});
});

const MATRIX_ROOMS_PER_PAGE = 10;
const matrixFloorPages = ref({});

const getTotalMatrixPagesByFloor = (floor) => {
    const rooms = filteredGroupedRooms.value[floor] || [];
    return Math.max(1, Math.ceil(rooms.length / MATRIX_ROOMS_PER_PAGE));
};
const getCurrentMatrixFloorPage = (floor) => {
    const totalPages = getTotalMatrixPagesByFloor(floor);
    const current = matrixFloorPages.value[floor] || 1;
    return Math.min(Math.max(current, 1), totalPages);
};
const setMatrixFloorPage = (floor, page) => {
    const totalPages = getTotalMatrixPagesByFloor(floor);
    matrixFloorPages.value[floor] = Math.min(Math.max(page, 1), totalPages);
};
const getPaginatedMatrixRoomsByFloor = (floor) => {
    const rooms = filteredGroupedRooms.value[floor] || [];
    const currentPage = getCurrentMatrixFloorPage(floor);
    const start = (currentPage - 1) * MATRIX_ROOMS_PER_PAGE;
    return rooms.slice(start, start + MATRIX_ROOMS_PER_PAGE);
};
const getMatrixFloorPageNumbers = (floor) => {
    return Array.from({ length: getTotalMatrixPagesByFloor(floor) }, (_, i) => i + 1);
};

// Reset trang khi gõ tìm kiếm Matrix
watch(matrixSearch, () => {
    for (const floor in matrixFloorPages.value) {
        matrixFloorPages.value[floor] = 1;
    }
});

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);

const formatDateTime = (value) => {
    if (!value) return '--';
    const date = new Date(value);
    return date.toLocaleString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
};

const statusLabelMap = {
    pending: 'Chờ xác nhận',
    confirmed: 'Đã xác nhận',
    checked_in: 'Đang lưu trú',
    checked_out: 'Đã trả phòng',
    cancelled: 'Đã hủy',
};

const statusClassMap = {
    pending: 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400',
    confirmed: 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400',
    checked_in: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400',
    checked_out: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-400',
    cancelled: 'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400',
};

const getStatusLabel = (statusValue) => statusLabelMap[statusValue] || statusValue;
const getStatusClass = (statusValue) => statusClassMap[statusValue] || 'bg-slate-100 text-slate-700 border-slate-200';

const isAddServiceOpen = ref(false);
const selectedRoomForService = ref(null);

const openAddService = (room) => {
    selectedRoomForService.value = room;
    isAddServiceOpen.value = true;
};

const isDetailModalOpen = ref(false);
const selectedBookingForDetail = ref(null);

const openDetailModal = (booking) => {
    if(booking) {
        selectedBookingForDetail.value = booking;
        isDetailModalOpen.value = true;
    }
};
</script>

<template>
    <Head title="Sơ đồ phòng & Đặt phòng" />
    <AdminLayout>
        <SuccessToast :message="flashSuccess" />

        <div class="space-y-6 pb-12 animate-in fade-in duration-500">

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="admin-index-subtitle">Lễ tân & Điều hành</span>
                    <h1 class="admin-index-title uppercase">Quản lý trạng thái phòng</h1>
                </div>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="booking-tab-switch">
                        <button @click="activeTab = 'matrix'" :class="['booking-tab-btn', activeTab === 'matrix' ? 'booking-tab-btn-active' : 'booking-tab-btn-inactive']">
                            Sơ đồ Matrix
                        </button>
                        <button @click="activeTab = 'list'" :class="['booking-tab-btn', activeTab === 'list' ? 'booking-tab-btn-active' : 'booking-tab-btn-inactive']">
                            Danh sách đơn
                        </button>
                    </div>

                    <Link :href="route('admin.bookings.history')" class="admin-index-history-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m4-3a8 8 0 11-16 0 8 8 0 0116 0z" /></svg>
                        Lịch sử
                    </Link>

                    <Link :href="route('admin.bookings.create', { start_date: startDate, end_date: endDate })" class="admin-index-create-btn">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Tạo đơn mới
                    </Link>
                </div>
            </div>

            <template v-if="activeTab === 'matrix'">
                <div class="app-card !p-4 md:!p-6 !rounded-[2rem] flex flex-wrap items-end gap-4 border-l-4 border-l-primary-500">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Từ ngày</label>
                        <input type="date" v-model="startDate" @change="handleDateFilter" class="form-input-pms form-input-pms-compact w-full md:!w-40">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Đến ngày</label>
                        <input type="date" v-model="endDate" @change="handleDateFilter" class="form-input-pms form-input-pms-compact w-full md:!w-40">
                    </div>

                    <div class="w-[1px] h-10 bg-slate-200 dark:bg-dark-border hidden md:block mx-2"></div>

                    <div class="space-y-2 flex-1 min-w-[200px]">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Tìm nhanh sơ đồ</label>
                        <input type="text" v-model="matrixSearch" placeholder="Nhập số phòng hoặc tên khách..." class="form-input-pms form-input-pms-compact w-full">
                    </div>

                    <button @click="clearFilters" class="admin-filter-reset-btn !px-5 !text-xs h-[42px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        Bỏ Lọc
                    </button>
                </div>

                <div class="flex flex-wrap gap-6 text-[10px] font-black uppercase tracking-widest text-muted-text bg-white dark:bg-dark-card p-4 rounded-2xl border border-slate-100 dark:border-dark-border shadow-sm inline-flex mt-4">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-slate-100 dark:bg-slate-800 border-2 border-slate-200"></span> Trống</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Chờ xác nhận</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Đã nhận cọc</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Đang lưu trú</div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-600 animate-pulse"></span> Quá giờ</div>
                </div>

                <div v-for="(rooms, floor) in filteredGroupedRooms" :key="floor" class="space-y-4 mt-6">
                    <div class="flex items-center gap-4">
                        <h3 class="text-sm font-black text-main-text dark:text-white uppercase tracking-[0.2em] whitespace-nowrap italic">Tầng {{ floor }}</h3>
                        <div class="h-[1px] w-full bg-slate-100 dark:bg-dark-border/50"></div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5">
                        <div v-for="room in getPaginatedMatrixRoomsByFloor(floor)" :key="room.id"
                             @click="openDetailModal(getCurrentBooking(room))"
                             class="relative p-6 rounded-[2.5rem] border-2 transition-all group overflow-hidden flex flex-col cursor-pointer"
                             :class="{
                                 'bg-white dark:bg-dark-card border-slate-50 dark:border-dark-border hover:border-primary-500/30 shadow-sm': !getCurrentBooking(room),
                                 'bg-rose-50 border-rose-400 dark:bg-rose-900/20 dark:border-rose-500/50 hover:border-rose-500 shadow-lg shadow-rose-500/20': getCurrentBooking(room)?.is_overstay,
                                 'bg-emerald-50 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/30 hover:border-emerald-400 shadow-sm': getCurrentBooking(room)?.status === 'checked_in' && !getCurrentBooking(room)?.is_overstay,
                                 'bg-amber-50 border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/30 hover:border-amber-400 shadow-sm': getCurrentBooking(room)?.status === 'confirmed',
                                 'bg-blue-50 border-blue-200 dark:bg-blue-500/10 dark:border-blue-500/30 hover:border-blue-400 shadow-sm': getCurrentBooking(room)?.status === 'pending',
                             }">

                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-2xl font-black tracking-tighter leading-none"
                                        :class="getCurrentBooking(room)?.is_overstay ? 'text-rose-700 dark:text-rose-400' : 'text-main-text dark:text-white'">
                                        P.{{ room.room_number }}
                                    </h4>
                                    <span class="text-[10px] font-bold uppercase mt-1 block"
                                          :class="getCurrentBooking(room)?.is_overstay ? 'text-rose-600/70' : 'text-muted-text'">
                                        {{ room.definition?.name }}
                                    </span>
                                </div>
                                <div class="w-2.5 h-2.5 rounded-full shrink-0"
                                     :class="{
                                         'bg-rose-600 shadow-[0_0_10px_rgba(225,29,72,0.8)] animate-pulse': getCurrentBooking(room)?.is_overstay,
                                         'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]': getCurrentBooking(room)?.status === 'checked_in' && !getCurrentBooking(room)?.is_overstay,
                                         'bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]': getCurrentBooking(room)?.status === 'confirmed',
                                         'bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]': getCurrentBooking(room)?.status === 'pending',
                                         'bg-slate-300': !getCurrentBooking(room)
                                     }">
                                </div>
                            </div>

                            <div v-if="getCurrentBooking(room)" class="space-y-3 mt-auto flex-1 flex flex-col justify-end">
                                <div v-if="getCurrentBooking(room).is_overstay" class="bg-rose-600 text-white text-[9px] font-black uppercase px-2 py-1.5 rounded-lg mb-1 flex items-center gap-1.5 shadow-md animate-pulse w-max">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    CHƯA TRẢ PHÒNG
                                </div>
                                <span v-else class="inline-block w-max px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-[0.1em]"
                                      :class="getCurrentBooking(room).source === 'online' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600'">
                                    {{ getCurrentBooking(room).source === 'online' ? 'Booking Online' : 'Tại quầy' }}
                                </span>

                                <p class="text-xs font-black truncate text-main-text dark:text-white" :title="getCurrentBooking(room).customer?.full_name">
                                    👤 {{ getCurrentBooking(room).customer?.full_name }}
                                </p>

                                <div class="flex items-center justify-between text-[9px] font-black p-2 rounded-xl bg-white/50 dark:bg-black/20 text-slate-500">
                                    <div class="flex flex-col">
                                        <span class="uppercase opacity-50">Đến</span>
                                        <span class="text-main-text dark:text-white">
                                            {{ new Date(getCurrentBooking(room).check_in_expected).toLocaleDateString('vi-VN', {day:'2-digit', month:'2-digit'}) }}
                                        </span>
                                    </div>
                                    <div class="h-4 w-[1px] bg-slate-200 dark:bg-slate-700"></div>
                                    <div class="flex flex-col text-right">
                                        <span class="uppercase opacity-50 text-amber-500">Đi</span>
                                        <span class="text-amber-500">
                                            {{ new Date(getCurrentBooking(room).check_out_expected).toLocaleDateString('vi-VN', {day:'2-digit', month:'2-digit'}) }}
                                        </span>
                                    </div>
                                </div>

                                <button @click.stop="openAddService(room)"
                                        class="w-full mt-2 py-2.5 rounded-xl text-[9px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400 hover:bg-amber-500 hover:text-white transition-colors flex justify-center items-center gap-2">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                    Thêm dịch vụ
                                </button>
                            </div>

                            <div v-else class="mt-auto flex flex-col pt-4 opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0">
                                <Link @click.stop :href="route('admin.bookings.create', { start_date: startDate, end_date: endDate, room_id: room.id })"
                                      class="text-[11px] font-black text-primary-500 bg-primary-50 py-2.5 rounded-xl text-center hover:bg-primary-500 hover:text-white transition-colors border border-primary-100 uppercase tracking-widest">
                                    Đặt Ngay
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-if="getTotalMatrixPagesByFloor(floor) > 1" class="flex flex-wrap items-center justify-between gap-3 mt-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-text">
                            Tầng {{ floor }}: Trang {{ getCurrentMatrixFloorPage(floor) }} / {{ getTotalMatrixPagesByFloor(floor) }}
                        </p>
                        <div class="flex items-center gap-2">
                            <button @click="setMatrixFloorPage(floor, getCurrentMatrixFloorPage(floor) - 1)" :disabled="getCurrentMatrixFloorPage(floor) === 1" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-slate-100 bg-white dark:bg-dark-card text-main-text disabled:opacity-40 disabled:cursor-not-allowed transition-all">Trước</button>
                            <button v-for="page in getMatrixFloorPageNumbers(floor)" :key="`matrix-${floor}-${page}`" @click="setMatrixFloorPage(floor, page)"
                                    class="w-9 h-9 rounded-xl text-[10px] font-black border transition-all"
                                    :class="page === getCurrentMatrixFloorPage(floor) ? 'bg-slate-900 text-white border-slate-900 shadow-glow' : 'bg-white dark:bg-dark-card text-main-text border-slate-100 hover:border-primary-500'">
                                {{ page }}
                            </button>
                            <button @click="setMatrixFloorPage(floor, getCurrentMatrixFloorPage(floor) + 1)" :disabled="getCurrentMatrixFloorPage(floor) === getTotalMatrixPagesByFloor(floor)" class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-slate-100 bg-white dark:bg-dark-card text-main-text disabled:opacity-40 disabled:cursor-not-allowed transition-all">Sau</button>
                        </div>
                    </div>
                </div>
            </template>

            <template v-if="activeTab === 'list'">
                <BookingOrdersList
                    :bookings="bookings"
                    :search="search"
                    :source="source"
                    :status="status"
                    :list-start-date="listStartDate"
                    :list-end-date="listEndDate"
                    :apply-list-filters="applyListFilters"
                    :clear-list-filters="clearListFilters"
                    :format-date-time="formatDateTime"
                    :format-currency="formatCurrency"
                    :get-status-class="getStatusClass"
                    :get-status-label="getStatusLabel"
                    @update:search="search = $event"
                    @update:source="source = $event"
                    @update:status="status = $event"
                    @update:list-start-date="listStartDate = $event"
                    @update:list-end-date="listEndDate = $event"
                />
            </template>

            <AddServiceModal
                v-if="selectedRoomForService"
                :show="isAddServiceOpen"
                :booking-id="getCurrentBooking(selectedRoomForService)?.id"
                :room-number="selectedRoomForService.room_number"
                :guest-name="getCurrentBooking(selectedRoomForService)?.customer?.full_name"
                :services="services"
                @close="isAddServiceOpen = false"
            />

            <BookingDetailModal
                v-if="selectedBookingForDetail"
                :show="isDetailModalOpen"
                :booking="selectedBookingForDetail"
                @close="isDetailModalOpen = false"
            />

        </div>
    </AdminLayout>
</template>
