<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import ErrorToast from '@/Components/Admin/ErrorToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({
    booking: Object,
    availableServices: Array,
    transferCandidates: Array,
});

const { flashSuccess, flashError } = useAdminFlash();

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);

// Ưu tiên metadata backend để tránh lệch múi giờ ở frontend
const isOverstay = computed(() => {
    if (typeof props.booking?.is_overstay === 'boolean') return props.booking.is_overstay;
    if (props.booking?.status !== 'checked_in') return false;
    const expectedOut = new Date(props.booking.check_out_expected);
    expectedOut.setHours(12, 0, 0, 0);
    return new Date() > expectedOut;
});

const overstayHours = computed(() => {
    if (typeof props.booking?.overstay_hours === 'number') return props.booking.overstay_hours;
    if (!isOverstay.value) return 0;
    const expectedOut = new Date(props.booking.check_out_expected);
    expectedOut.setHours(12, 0, 0, 0);
    const now = new Date();
    const overtimeMinutes = Math.floor((now - expectedOut) / (1000 * 60));
    return overtimeMinutes > 60 ? Math.ceil((overtimeMinutes - 60) / 60) : 0;
});

// Tiền bạc
const totalBill = computed(() => props.booking.invoice?.total_amount || props.booking.total_amount || 0);
const paidAmount = computed(() => props.booking.invoice?.amount_paid || props.booking.deposit_amount || 0);
const balance = computed(() => Math.max(0, totalBill.value - paidAmount.value));

// Kiểm tra xem ngày hiện tại có đủ để check-in không (phải >= ngày dự kiến)
const canCheckIn = computed(() => {
    if (!props.booking?.check_in_expected) return false;
    const checkInExpected = new Date(props.booking.check_in_expected);
    checkInExpected.setHours(0, 0, 0, 0);
    const now = new Date();
    now.setHours(0, 0, 0, 0);
    return now >= checkInExpected;
});

const checkInBlockReason = computed(() => {
    if (canCheckIn.value) return null;
    const checkInExpected = new Date(props.booking.check_in_expected);
    return checkInExpected.toLocaleDateString('vi-VN');
});

const statusColors = {
    pending: 'bg-amber-100 text-amber-600',
    confirmed: 'bg-blue-100 text-blue-600',
    checked_in: 'bg-emerald-100 text-emerald-600',
    checked_out: 'bg-slate-200 text-slate-600',
    cancelled: 'bg-rose-100 text-rose-600',
};

const statusLabels = {
    pending: 'Chờ xác nhận',
    confirmed: 'Đã xác nhận',
    checked_in: 'Đang lưu trú',
    checked_out: 'Đã trả phòng',
    cancelled: 'Đã hủy (No-show)',
};

// 🎯 LOGIC 1: ĐỔI TRẠNG THÁI (CÓ LOA CẢNH BÁO LỖI NỢ TIỀN)
const changeStatus = (newStatus) => {
    let msg = newStatus === 'checked_in' ? 'Xác nhận khách đã nhận phòng (Check-in)?'
            : newStatus === 'checked_out' ? 'Xác nhận khách trả phòng (Check-out)?'
            : 'Đổi trạng thái đơn này?';

    if (confirm(msg)) {
        router.patch(route('admin.bookings.update-status', props.booking.id), { status: newStatus }, {
            preserveScroll: true,
        });
    }
};

// 🎯 LOGIC 2: HỦY ĐƠN
const cancelBooking = () => {
    if (confirm("⚠️ CẢNH BÁO: Xác nhận HỦY đặt phòng? Toàn bộ tiền cọc sẽ được chuyển thành doanh thu phí phạt!")) {
        router.post(route('admin.bookings.cancel', props.booking.id), {}, {
            preserveScroll: true,
        });
    }
};

// 🎯 LOGIC 3: MODAL NHẬN CỌC
const showDepositModal = ref(false);
const depositForm = useForm({ deposit_amount: '' });
const processDeposit = () => {
    depositForm.post(route('admin.bookings.deposit', props.booking.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDepositModal.value = false;
            depositForm.reset();
        }
    });
};

const showTransferModal = ref(false);
const transferForm = useForm({
    booking_room_id: '',
    new_room_id: '',
});
const transferFilterDefinitionId = ref('');
const transferSearch = ref('');

const transferCandidates = computed(() => {
    return (props.transferCandidates || []).filter((room) => {
        const status = room.trang_thai ?? room.status;
        return status === 'available';
    });
});

const selectedTransferRoom = computed(() => {
    return transferCandidates.value.find((room) => Number(room.id) === Number(transferForm.new_room_id)) || null;
});

const transferDefinitionOptions = computed(() => {
    const map = new Map();
    transferCandidates.value.forEach((room) => {
        const id = room.room_definition_id;
        if (!id || map.has(id)) return;
        map.set(id, {
            id,
            name: room.definition?.name || `Hạng #${id}`,
        });
    });

    return Array.from(map.values()).sort((a, b) => a.name.localeCompare(b.name, 'vi'));
});

const filteredTransferCandidates = computed(() => {
    const source = transferCandidates.value;
    const query = transferSearch.value.trim().toLowerCase();

    return source.filter((room) => {
        if (transferFilterDefinitionId.value && Number(transferFilterDefinitionId.value) !== Number(room.room_definition_id)) {
            return false;
        }

        if (!query) return true;

        const roomNumber = String(room.room_number || '').toLowerCase();
        const floor = String(room.floor || '').toLowerCase();
        const definitionName = String(room.definition?.name || '').toLowerCase();

        return roomNumber.includes(query) || floor.includes(query) || definitionName.includes(query);
    });
});

const openTransferModal = (bookingRoomId = null) => {
    const rooms = props.booking.booking_rooms || [];
    const defaultBookingRoomId = bookingRoomId || rooms[0]?.id || '';
    transferForm.booking_room_id = defaultBookingRoomId;
    transferForm.new_room_id = '';
    transferFilterDefinitionId.value = '';
    transferSearch.value = '';
    transferForm.clearErrors();
    showTransferModal.value = true;
};

const processTransferRoom = () => {
    transferForm.post(route('admin.bookings.transfer-room', props.booking.id), {
        preserveScroll: true,
        onSuccess: () => {
            showTransferModal.value = false;
            transferForm.reset();
            transferFilterDefinitionId.value = '';
            transferSearch.value = '';

            // Đảm bảo danh sách phòng chuyển được lấy mới sau mỗi lần đổi phòng.
            router.reload({
                only: ['booking', 'transferCandidates'],
                preserveScroll: true,
            });
        },
    });
};

const pickTransferRoom = (roomId) => {
    transferForm.new_room_id = roomId;
};

const transferRoomLabel = (room) => `P.${room.room_number} - ${room.definition?.name || 'Chưa có hạng'} - Tầng ${room.floor || '--'}`;
</script>

<template>
    <Head :title="`Chi tiết đơn ${booking.booking_code}`" />
    <AdminLayout>
        <div class="max-w-7xl mx-auto space-y-6 pb-12 animate-in fade-in duration-500">

            <SuccessToast :message="flashSuccess" />
            <ErrorToast :message="flashError" />

            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="admin-index-subtitle mb-2 block">Lễ tân & Điều hành</span>
                    <div class="flex items-center gap-4">
                        <h2 class="admin-index-title !text-3xl">{{ booking.booking_code }}</h2>

                        <span v-if="isOverstay" class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-md bg-rose-500 text-white animate-pulse">
                            🚨 KHÁCH CHƯA CHECK-OUT (LỐ {{ overstayHours }} GIỜ)
                        </span>
                        <span v-else :class="statusColors[booking.status]" class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm">
                            {{ statusLabels[booking.status] }}
                        </span>
                    </div>
                </div>
                <Link :href="route('admin.bookings.index')" class="admin-index-back-link mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Quay lại sơ đồ
                </Link>
            </div>

            <div v-if="isOverstay" class="bg-rose-50 dark:bg-rose-500/10 border-l-4 border-rose-500 p-5 rounded-r-2xl flex items-start gap-4 shadow-sm animate-in slide-in-from-top-4">
                <div class="w-10 h-10 bg-rose-500 rounded-full flex items-center justify-center shrink-0 text-white shadow-lg shadow-rose-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h4 class="text-rose-700 dark:text-rose-400 font-black tracking-tight uppercase text-sm mb-1">Cảnh báo: Phòng đang vượt quá thời gian lưu trú!</h4>
                    <p class="text-rose-600/80 dark:text-rose-300 text-xs font-bold leading-relaxed">
                        Khách hàng đáng lẽ phải check-out từ ngày <span class="text-rose-600 font-black underline">{{ new Date(booking.check_out_expected).toLocaleDateString('vi-VN') }}</span>.
                        Hệ thống chỉ cảnh báo lố {{ overstayHours }} giờ để lễ tân xử lý.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start mt-4">

                <div class="xl:col-span-4 space-y-6">
                    <div class="app-card relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-primary-500/10 rounded-full -mr-12 -mt-12 blur-2xl pointer-events-none"></div>
                        <h3 class="admin-index-subtitle mb-6">Thông tin Khách Hàng</h3>

                        <div class="flex items-center gap-4 mb-6 relative z-10">
                            <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center text-primary-500 font-black text-lg">
                                {{ booking.customer?.full_name.charAt(0) }}
                            </div>
                            <div>
                                <h4 class="text-main-text dark:text-white font-black uppercase tracking-tighter">{{ booking.customer?.full_name }}</h4>
                                <p class="text-desc !text-xs font-bold mt-0.5">{{ booking.customer?.phone }}</p>
                            </div>
                        </div>

                        <div class="space-y-3 pt-6 border-t border-slate-100 dark:border-dark-border relative z-10">
                            <div class="flex justify-between items-start">
                                <span class="text-[10px] text-muted-text uppercase font-bold tracking-widest">Email:</span>
                                <span class="text-sm font-semibold text-main-text dark:text-white text-right break-all">{{ booking.customer?.email || '--' }}</span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="text-[10px] text-muted-text uppercase font-bold tracking-widest">Điện thoại:</span>
                                <span class="text-sm font-semibold text-main-text dark:text-white">{{ booking.customer?.phone || '--' }}</span>
                            </div>
                            <div v-if="booking.customer?.cccd_number" class="flex justify-between items-start">
                                <span class="text-[10px] text-muted-text uppercase font-bold tracking-widest">CCCD:</span>
                                <span class="text-sm font-semibold text-main-text dark:text-white">{{ booking.customer?.cccd_number }}</span>
                            </div>
                            <div v-if="booking.customer?.address" class="flex justify-between items-start">
                                <span class="text-[10px] text-muted-text uppercase font-bold tracking-widest">Địa chỉ:</span>
                                <span class="text-sm font-semibold text-main-text dark:text-white text-right max-w-xs">{{ booking.customer?.address }}</span>
                            </div>
                            <div v-if="booking.customer?.gender || booking.customer?.birthday" class="flex justify-between items-start">
                                <span class="text-[10px] text-muted-text uppercase font-bold tracking-widest">Thông tin cá nhân:</span>
                                <div class="text-right">
                                    <div v-if="booking.customer?.gender" class="text-sm font-semibold text-main-text dark:text-white">
                                        {{ booking.customer?.gender === 'male' ? 'Nam' : booking.customer?.gender === 'female' ? 'Nữ' : booking.customer?.gender }}
                                    </div>
                                    <div v-if="booking.customer?.birthday" class="text-[10px] text-muted-text mt-1">
                                        {{ new Date(booking.customer?.birthday).toLocaleDateString('vi-VN') }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[10px] text-muted-text uppercase font-bold tracking-widest">Nguồn đặt:</span>
                                <span class="text-sm font-black text-main-text dark:text-white">{{ booking.source === 'online' ? 'Website / App' : 'Tại quầy' }}</span>
                            </div>
                            <div class="flex justify-between items-center bg-emerald-50 dark:bg-emerald-900/20 p-3 rounded-xl border border-emerald-100 dark:border-emerald-800">
                                <span class="text-[10px] text-emerald-600 uppercase font-black tracking-widest">Tiền cọc đã thu:</span>
                                <span class="text-lg font-black text-emerald-500 tracking-tighter">{{ formatCurrency(booking.deposit_amount) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="app-card relative overflow-hidden !bg-slate-900 !text-white !border-none shadow-2xl" :class="{'!bg-rose-950': isOverstay}">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/20 rounded-full -mr-16 -mt-16 blur-3xl pointer-events-none" :class="{'bg-rose-500/20': isOverstay}"></div>

                        <div class="flex items-center justify-between mb-8 relative z-10">
                            <div class="flex flex-col">
                                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-widest" :class="{'text-rose-300': isOverstay}">Check-in</span>
                                <span class="font-black text-primary-400 mt-1" :class="{'text-rose-400': isOverstay}">{{ new Date(booking.check_in_actual || booking.check_in_expected).toLocaleDateString('vi-VN') }}</span>
                            </div>
                            <div class="h-px w-8 bg-slate-700" :class="{'bg-rose-500/30': isOverstay}"></div>
                            <div class="flex flex-col text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-widest" :class="{'text-rose-300': isOverstay}">Check-out</span>
                                <span class="font-black mt-1" :class="isOverstay ? 'text-white line-through opacity-70' : 'text-rose-400'">
                                    {{ new Date(booking.check_out_actual || booking.check_out_expected).toLocaleDateString('vi-VN') }}
                                </span>
                                <span v-if="isOverstay" class="text-[9px] font-black text-rose-400 uppercase mt-1">Đáng lẽ phải ra</span>
                            </div>
                        </div>

                        <div class="space-y-3 relative z-10 mt-8 border-t border-white/10 pt-6">
                            <template v-if="['pending', 'confirmed'].includes(booking.status)">
                                <button @click="showDepositModal = true" class="btn-secondary !w-full !py-3 !text-[11px] !bg-white/10 !text-white hover:!bg-white/20 !border-none">
                                    💰 Nhận Tiền Cọc
                                </button>
                                <button @click="changeStatus('checked_in')" :disabled="!canCheckIn" class="btn-primary !bg-emerald-500 hover:!bg-emerald-600 !w-full !py-4 !text-[11px] !uppercase !tracking-widest disabled:opacity-50 disabled:cursor-not-allowed">
                                    {{ canCheckIn ? '🔑 Khách Check-in' : `❌ Check-in sau ${checkInBlockReason}` }}
                                </button>
                                <button @click="cancelBooking" class="btn-secondary !w-full !py-3 !text-[11px] !uppercase !tracking-widest !bg-rose-500/10 !text-rose-400 hover:!bg-rose-500 hover:!text-white !border-none mt-2">
                                    ❌ Hủy Đặt Phòng
                                </button>
                            </template>

                            <template v-if="booking.status === 'checked_in'">
                                <button @click="showDepositModal = true" class="btn-secondary !w-full !py-3 !text-[11px] !bg-white/10 !text-white hover:!bg-white/20 !border-none mb-2">
                                    💰 Nhận Thêm Cọc
                                </button>
                                <button @click="openTransferModal()" class="btn-secondary !w-full !py-3 !text-[11px] !bg-blue-500/15 !text-blue-200 hover:!bg-blue-500/25 !border-none mb-2">
                                    🔁 Đổi phòng cho khách
                                </button>
                                <Link v-if="booking.invoice?.id" :href="route('admin.invoices.show', booking.invoice.id)"
                                      class="btn-primary !w-full !py-4 !text-[11px] !uppercase !tracking-widest flex items-center justify-center gap-2"
                                      :class="{'!bg-rose-600 hover:!bg-rose-700': isOverstay}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.456-.342 1.456-1.096V5.625c0-.754-.729-1.294-1.456-1.096a60.864 60.864 0 00-15.797 2.102m15.797 2.102c-.343.093-.686.184-1.03.273m-10.122 3.167a.75.75 0 10-1.5 0 .75.75 0 001.5 0z"/></svg>
                                    Chi Tiết & Thanh Toán Bill
                                </Link>
                                <button @click="changeStatus('checked_out')" class="btn-primary !w-full !py-4 !text-[11px] !uppercase !tracking-widest mt-2"
                                    :class="isOverstay ? '!bg-rose-500 hover:!bg-rose-600 !text-white' : '!bg-slate-700 hover:!bg-slate-600 !text-white'">
                                    👋 Xác Nhận Check-out {{ isOverstay ? '(Chốt Bill)' : '' }}
                                </button>
                            </template>

                            <template v-if="['checked_out', 'cancelled'].includes(booking.status)">
                                <Link v-if="booking.invoice?.id" :href="route('admin.invoices.show', booking.invoice.id)"
                                      class="btn-secondary !w-full !py-4 !text-[11px] !uppercase !tracking-widest !bg-slate-800 !text-white hover:!bg-slate-700 !border-none">
                                    🖨️ Xem Hóa Đơn Tất Toán
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-8">
                    <div class="index-table-card shadow-xl p-8 border-2" :class="isOverstay ? 'border-rose-500/30' : 'border-transparent'">
                        <div class="flex justify-between items-center mb-6 border-b border-slate-100 dark:border-dark-border pb-4">
                            <h3 class="admin-index-title !text-xl">Phòng & Dịch Vụ Tạm Tính</h3>
                            <span v-if="booking.status === 'cancelled'" class="px-3 py-1 bg-rose-100 text-rose-600 rounded text-xs font-black uppercase tracking-widest italic">Đã Hủy</span>
                            <span v-if="isOverstay" class="px-3 py-1 bg-rose-100 text-rose-600 rounded text-[10px] font-black uppercase tracking-widest flex items-center gap-1 animate-pulse">
                                Lố {{ overstayHours }} giờ
                            </span>
                        </div>

                        <div class="space-y-2 mb-6">
                            <h4 class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1 mb-3">1. Tiền Phòng (Giá cơ bản)</h4>
                            <div v-for="room in booking.booking_rooms" :key="room.id" class="flex justify-between items-center p-4 bg-slate-50 dark:bg-dark-bg rounded-xl border border-slate-100 dark:border-dark-border">
                                <div>
                                    <span class="font-black text-main-text dark:text-white italic tracking-tighter text-lg">P.{{ room.room?.room_number || '?' }}</span>
                                    <span class="text-[10px] font-bold text-muted-text uppercase ml-2">{{ room.definition?.name }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-black text-primary-500">{{ formatCurrency(room.price) }} / Đêm</span>
                                    <button
                                        v-if="booking.status === 'checked_in'"
                                        @click="openTransferModal(room.id)"
                                        class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-blue-500/10 text-blue-600 hover:bg-blue-500 hover:text-white transition"
                                    >
                                        Chuyển
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 mb-8">
                            <h4 class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1 mb-3">2. Dịch Vụ & Mini Bar</h4>
                            <div v-if="booking.booking_services?.length > 0">
                                <div v-for="svc in booking.booking_services" :key="svc.id" class="flex justify-between items-center p-4 bg-amber-50 dark:bg-amber-900/10 rounded-xl border border-amber-100 dark:border-amber-900/30 mb-2">
                                    <div>
                                        <span class="font-bold text-main-text dark:text-white">{{ svc.service?.name }}</span>
                                        <span class="text-xs text-amber-600 ml-2 font-black">x{{ svc.quantity }}</span>
                                    </div>
                                    <span class="text-sm font-black text-amber-500">{{ formatCurrency(svc.total_price) }}</span>
                                </div>
                            </div>
                            <div v-else class="py-6 text-center border-2 border-dashed border-slate-100 dark:border-dark-border rounded-xl">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-300">Không có dịch vụ phát sinh</p>
                            </div>
                        </div>

                        <div class="p-6 text-white rounded-[2rem] shadow-xl" :class="isOverstay ? 'bg-rose-900' : 'bg-slate-900'">
                            <div class="flex justify-between items-center mb-4 pb-4 border-b border-white/10">
                                <span class="text-xs font-bold uppercase tracking-widest" :class="isOverstay ? 'text-rose-300' : 'text-slate-400'">Tổng Tạm Tính Tới Hiện Tại</span>
                                <span class="text-2xl font-black">{{ formatCurrency(totalBill) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-black uppercase tracking-widest" :class="isOverstay ? 'text-rose-200' : 'text-emerald-400'">Cọc & Trả Trước: -{{ formatCurrency(paidAmount) }}</span>
                                <div class="text-right">
                                    <p class="text-[10px] font-black uppercase tracking-widest mb-1" :class="isOverstay ? 'text-rose-300' : 'text-slate-400'">Khách Cần Thanh Toán</p>
                                    <p class="text-3xl font-black italic tracking-tighter" :class="balance > 0 ? (isOverstay ? 'text-white' : 'text-rose-400') : 'text-emerald-400'">
                                        {{ formatCurrency(balance) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showDepositModal" class="fixed inset-0 z-[999] flex items-center justify-center p-6 bg-slate-900/80 backdrop-blur-sm print:hidden">
                <div class="absolute inset-0" @click="showDepositModal = false"></div>
                <div class="app-card !p-10 w-full max-w-sm relative overflow-hidden z-10 animate-in zoom-in duration-300">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <span class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.3em] block mb-1">Giao dịch</span>
                            <h3 class="text-title italic !text-2xl">Nhận Tiền Cọc</h3>
                        </div>
                        <button @click="showDepositModal = false" class="index-action-btn hover:text-rose-500 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="admin-index-subtitle px-1">Số tiền cọc (VNĐ)</label>
                            <input v-model="depositForm.deposit_amount" type="number" class="form-input-pms !text-emerald-500 !text-2xl" placeholder="Ví dụ: 500000" min="0">
                                <p v-if="depositForm.errors.deposit_amount" class="text-[10px] font-bold text-rose-500 px-1 mt-2">{{ depositForm.errors.deposit_amount }}</p>
                        </div>
                        <button @click="processDeposit" :disabled="depositForm.processing" class="btn-primary !w-full !py-4 !bg-emerald-500 hover:!bg-emerald-600 !uppercase !tracking-widest !text-xs">
                            {{ depositForm.processing ? 'Đang xử lý...' : 'Xác nhận thu cọc' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showTransferModal" class="fixed inset-0 z-[999] flex items-center justify-center p-4 sm:p-6 bg-slate-900/80 backdrop-blur-sm print:hidden">
                <div class="absolute inset-0" @click="showTransferModal = false"></div>
                <div class="bg-white dark:bg-dark-card w-full max-w-5xl relative overflow-hidden z-10 animate-in zoom-in duration-300 rounded-[2rem] sm:rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-dark-border">
                    <div class="absolute top-0 right-0 w-72 h-72 bg-blue-500/10 rounded-full -mr-36 -mt-36 blur-3xl pointer-events-none"></div>

                    <div class="px-6 sm:px-8 py-5 sm:py-6 border-b border-slate-100 dark:border-dark-border flex items-start justify-between gap-4 relative z-10">
                        <div>
                            <span class="text-[10px] font-black text-blue-500 uppercase tracking-[0.3em] block mb-1">Điều phối phòng</span>
                            <h3 class="text-2xl sm:text-3xl font-black text-main-text dark:text-white tracking-tight uppercase">Chuyển phòng cho khách</h3>
                            <p class="text-xs text-muted-text mt-2">Chỉ hiển thị phòng <span class="font-black text-emerald-500">available</span>.</p>
                        </div>
                        <button @click="showTransferModal = false" class="w-10 h-10 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/20 flex items-center justify-center transition-colors shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-6 sm:p-8 space-y-6 relative z-10">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="rounded-2xl border border-slate-100 dark:border-dark-border bg-slate-50 dark:bg-dark-bg p-4">
                                <div class="text-[10px] font-black uppercase tracking-widest text-muted-text mb-2">Phòng hiện tại</div>
                                <select v-model="transferForm.booking_room_id" class="form-input-pms">
                                    <option value="" disabled>Chọn phòng cần chuyển</option>
                                    <option v-for="room in booking.booking_rooms" :key="room.id" :value="room.id">
                                        P.{{ room.room?.room_number || '?' }} - {{ room.definition?.name }}
                                    </option>
                                </select>
                                <p v-if="transferForm.errors.booking_room_id" class="text-[10px] font-bold text-rose-500 px-1 mt-1">{{ transferForm.errors.booking_room_id }}</p>
                            </div>

                            <div class="rounded-2xl border border-blue-100 dark:border-blue-900/30 bg-blue-50/60 dark:bg-blue-900/10 p-4">
                                <div class="text-[10px] font-black uppercase tracking-widest text-blue-600 mb-2">Phòng đích đã chọn</div>
                                <div v-if="selectedTransferRoom" class="space-y-1">
                                    <div class="text-xl font-black text-main-text dark:text-white">P.{{ selectedTransferRoom.room_number }}</div>
                                    <div class="text-xs font-bold text-muted-text">{{ selectedTransferRoom.definition?.name || 'Chưa có hạng' }}</div>
                                    <div class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-widest">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Trạng thái: Trống
                                    </div>
                                </div>
                                <div v-else class="text-sm text-muted-text">Chọn một phòng từ danh sách bên dưới.</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="admin-index-subtitle px-1">Lọc theo hạng</label>
                                <select v-model="transferFilterDefinitionId" class="form-input-pms">
                                    <option value="">Tất cả hạng phòng</option>
                                    <option v-for="option in transferDefinitionOptions" :key="option.id" :value="option.id">
                                        {{ option.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="admin-index-subtitle px-1">Tìm nhanh</label>
                                <input
                                    v-model="transferSearch"
                                    type="text"
                                    class="form-input-pms"
                                    placeholder="Số phòng, tầng, hạng..."
                                >
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 dark:border-dark-border bg-slate-50/70 dark:bg-dark-bg/70 p-4 sm:p-5">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <div>
                                    <div class="text-[10px] font-black uppercase tracking-widest text-muted-text">Danh sách phòng trống</div>
                                    <div class="text-sm font-bold text-main-text dark:text-white">{{ filteredTransferCandidates.length }} phòng khả dụng</div>
                                </div>
                            </div>

                            <div v-if="filteredTransferCandidates.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4 max-h-[42vh] overflow-y-auto pr-1 custom-scrollbar">
                                <button
                                    v-for="room in filteredTransferCandidates"
                                    :key="room.id"
                                    type="button"
                                    @click="pickTransferRoom(room.id)"
                                    class="text-left rounded-2xl border-2 p-4 transition-all duration-200 bg-white dark:bg-dark-card hover:shadow-lg"
                                    :class="Number(transferForm.new_room_id) === Number(room.id) ? 'border-blue-500 ring-2 ring-blue-500/20 shadow-lg' : 'border-slate-100 dark:border-dark-border hover:border-blue-200 dark:hover:border-blue-700/40'"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-lg font-black text-main-text dark:text-white">P.{{ room.room_number }}</div>
                                            <div class="text-[11px] font-bold text-muted-text uppercase tracking-widest mt-1">{{ room.definition?.name || 'Chưa có hạng' }}</div>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase tracking-widest whitespace-nowrap">
                                            ✓ Trống
                                        </span>
                                    </div>

                                    <div class="mt-4 flex items-center justify-between text-xs font-bold text-muted-text">
                                        <span>Tầng {{ room.floor || '--' }}</span>
                                        <span v-if="room.definition?.base_price">{{ formatCurrency(room.definition.base_price) }}</span>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-blue-500">Chọn để chuyển</span>
                                        <span v-if="Number(transferForm.new_room_id) === Number(room.id)" class="text-[10px] font-black uppercase tracking-widest text-blue-600">Đã chọn</span>
                                    </div>
                                </button>
                            </div>

                            <div v-else class="py-14 text-center">
                                <div class="w-16 h-16 mx-auto bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center text-slate-300 mb-4">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </div>
                                <p class="text-sm font-black text-main-text dark:text-white">Không có phòng trống phù hợp</p>
                                <p class="text-xs text-muted-text mt-1">Hệ thống chỉ hiển thị phòng có trạng thái <span class="font-black text-emerald-500">available</span>.</p>
                            </div>
                        </div>

                        <p v-if="transferForm.errors.new_room_id" class="text-[10px] font-bold text-rose-500 px-1 -mt-2">{{ transferForm.errors.new_room_id }}</p>

                        <button
                            @click="processTransferRoom"
                            :disabled="transferForm.processing || !transferForm.booking_room_id || !transferForm.new_room_id"
                            class="btn-primary !w-full !py-4 !bg-blue-500 hover:!bg-blue-600 !uppercase !tracking-widest !text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ transferForm.processing ? 'Đang xử lý...' : 'Xác nhận chuyển phòng' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>

    </AdminLayout>
</template>
