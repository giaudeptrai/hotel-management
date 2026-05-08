<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import axios from 'axios';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import ErrorToast from '@/Components/Admin/ErrorToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({
    preFill: Object,
    roomDefinitions: Array,
});

const { flashSuccess, flashError } = useAdminFlash();

const sanitizePhone = (value) => value.replace(/\D/g, '');

// 1. FORM CHÍNH
const form = useForm({
    customer_id: '',
    check_in_expected: props.preFill?.check_in || '',
    check_out_expected: props.preFill?.check_out || '',
    status: 'confirmed',
    source: 'walk_in',
    deposit_amount: '',
    special_requests: '',
    rooms: [],
});

// --- LOGIC TÌM KHÁCH HÀNG ---
const searchPhone = ref('');
const searchResults = ref([]);
const isSearchingCustomer = ref(false);
const selectedCustomer = ref(null);

watch(searchPhone, async (val) => {
    if (val.length < 2) { searchResults.value = []; return; }
    isSearchingCustomer.value = true;
    try {
        const res = await axios.get(route('admin.bookings.search-customers'), { params: { q: val } });
        searchResults.value = res.data;
    } finally { isSearchingCustomer.value = false; }
});

const selectCustomer = (c) => {
    selectedCustomer.value = c;
    form.customer_id = c.id;
    searchPhone.value = '';
    searchResults.value = [];
};

// --- MODAL THÊM KHÁCH ---
const showCustomerModal = ref(false);
const isSavingCustomer = ref(false);
const customerForm = useForm({
    full_name: '',
    phone: '',
    cccd_number: '',
    email: '',
    birthday: '',
    gender: 'other',
    address: '',
});
const openNewCustomerModal = () => {
    customerForm.reset();
    customerForm.clearErrors();
    customerForm.phone = searchPhone.value;
    showCustomerModal.value = true;
};
const submitNewCustomer = async () => {
    isSavingCustomer.value = true;
    customerForm.clearErrors();

    try {
        const res = await axios.post(route('admin.bookings.quick-customer'), customerForm.data());
        if (res.data.success) {
            selectCustomer(res.data.customer);
            showCustomerModal.value = false;
            customerForm.reset();
        }
    } catch (error) {
        const validationErrors = error?.response?.data?.errors;
        if (validationErrors) {
            Object.keys(validationErrors).forEach((field) => {
                customerForm.setError(field, validationErrors[field][0]);
            });
        }
    } finally {
        isSavingCustomer.value = false;
    }
};

// --- LOGIC CHỌN PHÒNG & LỌC NÂNG CAO ---
const showRoomPicker = ref(false);
const availableRooms = ref([]);
const isSearchingRooms = ref(false);

const roomSearchQuery = ref('');
const filterDefinition = ref('');
const filterCapacity = ref('');

// 🎯 Lấy danh sách phòng trống (status = 'available') từ backend
const getAvailableRooms = async () => {
    if (!form.check_in_expected || !form.check_out_expected) return;
    isSearchingRooms.value = true;
    try {
        const res = await axios.post(route('admin.bookings.api-rooms'), {
            check_in: form.check_in_expected,
            check_out: form.check_out_expected
        });
        // ✅ LỌC LẦN NỮA: Chỉ lấy phòng có status='available' (tránh lỗi lọc backend)
        availableRooms.value = (res.data || []).filter(room => room.status === 'available');
        if (props.preFill?.room_id && form.rooms.length === 0) {
            const target = availableRooms.value.find(r => r.id == props.preFill.room_id);
            if (target) addRoomToCart(target);
        }
    } finally { isSearchingRooms.value = false; }
};

watch([() => form.check_in_expected, () => form.check_out_expected], getAvailableRooms, { immediate: true });

const filteredAvailableRooms = computed(() => {
    return availableRooms.value.filter(room => {
        const isNotSelected = !form.rooms.some(r => r.room_id === room.id);
        const matchNumber = room.room_number.toString().includes(roomSearchQuery.value);
        const matchDefinition = filterDefinition.value ? room.room_definition_id == filterDefinition.value : true;
        const matchCapacity = filterCapacity.value ? (room.definition?.max_occupancy >= parseInt(filterCapacity.value)) : true;
        return isNotSelected && matchNumber && matchDefinition && matchCapacity;
    });
});

const addRoomToCart = (room) => {
    form.rooms.push({
        room_id: room.id,
        room_definition_id: room.room_definition_id,
        room_number: room.room_number,
        price: room.definition?.base_price,
        definition_name: room.definition?.name
    });
};

const removeRoomFromCart = (index) => form.rooms.splice(index, 1);

// Tính toán tiền bạc
const stayNights = computed(() => {
    if (!form.check_in_expected || !form.check_out_expected) return 1;

    const checkIn = new Date(`${form.check_in_expected}T00:00:00`);
    const checkOut = new Date(`${form.check_out_expected}T00:00:00`);
    const diffDays = Math.round((checkOut.getTime() - checkIn.getTime()) / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 1;
});

const totalEstimate = computed(() => form.rooms.reduce((sum, r) => sum + Number(r.price || 0), 0) * stayNights.value);
const remainingBalance = computed(() => totalEstimate.value - (Number(form.deposit_amount) || 0));
const hasDeposit = computed(() => Number(form.deposit_amount) > 0);

// KIỂM TRA ĐIỀU KIỆN CHỐT ĐƠN
const canSubmitBooking = computed(() => {
    return form.customer_id &&
           form.rooms.length > 0;
});

const submitBooking = () => {
    if (!canSubmitBooking.value) return;
    form.post(route('admin.bookings.store'));
};
const formatCurrency = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v);
</script>

<template>
    <Head title="Tạo Đơn Đặt Phòng" />
    <AdminLayout>
        <div class="max-w-6xl mx-auto space-y-8 pb-20 animate-in fade-in duration-500">

            <SuccessToast :message="flashSuccess" />
            <ErrorToast :message="flashError" />

            <div class="flex flex-col sm:flex-row sm:items-end justify-between px-2 gap-4">
                <div>
                    <span class="admin-index-subtitle mb-2 block">Lễ tân & Điều hành</span>
                    <h2 class="admin-index-title !text-3xl">Khởi Tạo Đơn Đặt</h2>
                </div>
                <Link :href="route('admin.bookings.index')" class="admin-index-back-link mb-1 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Quay lại sơ đồ
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <div class="lg:col-span-7 space-y-8">
                    <div class="app-card relative overflow-visible !p-8 !rounded-[2rem]">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/5 rounded-full -mr-16 -mt-16 blur-3xl pointer-events-none"></div>
                        <h3 class="text-[11px] font-black text-primary-500 uppercase tracking-[0.2em] mb-6 px-1">1. Định danh khách hàng</h3>

                        <div v-if="!selectedCustomer" class="space-y-6">
                            <div class="relative">
                                <label class="text-[10px] font-black uppercase text-muted-text px-1 block mb-2">Tìm dữ liệu khách cũ</label>
                                <input v-model="searchPhone" type="text" placeholder="Tìm theo Số điện thoại hoặc Tên khách..." class="form-input-pms w-full !text-base">

                                <div v-if="searchResults.length > 0" class="absolute z-[100] w-full mt-2 bg-white dark:bg-dark-card border border-slate-100 dark:border-dark-border shadow-2xl rounded-2xl overflow-hidden animate-in fade-in slide-in-from-top-2">
                                    <button v-for="c in searchResults" :key="c.id" @click="selectCustomer(c)" class="w-full text-left p-4 hover:bg-slate-50 dark:hover:bg-slate-800 flex justify-between items-center transition-all border-b border-slate-100 dark:border-dark-border last:border-0 group">
                                        <div class="flex flex-col">
                                            <span class="font-black text-main-text dark:text-white uppercase text-sm group-hover:text-primary-500 transition-colors">{{ c.full_name }}</span>
                                            <span class="text-slate-500 text-xs font-bold tracking-widest mt-0.5">{{ c.phone }}</span>
                                        </div>
                                        <span class="w-8 h-8 rounded-full bg-primary-50 text-primary-500 flex items-center justify-center dark:bg-primary-500/10 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <div v-if="searchPhone.length >= 3 && searchResults.length === 0" class="p-8 bg-slate-50 dark:bg-dark-bg rounded-2xl border-2 border-dashed border-slate-200 dark:border-dark-border flex flex-col items-center gap-4 text-center">
                                <div>
                                    <p class="text-sm font-black text-main-text dark:text-white">Không tìm thấy khách hàng!</p>
                                    <p class="text-xs text-muted-text mt-1">Hệ thống chưa ghi nhận dữ liệu khớp với từ khóa này.</p>
                                </div>
                                <button @click="openNewCustomerModal" class="btn-primary !px-6 !py-3 !text-xs !rounded-xl shadow-lg shadow-primary-500/20">
                                    + Tạo hồ sơ khách mới
                                </button>
                            </div>
                        </div>

                        <div v-else class="flex justify-between items-center p-6 bg-emerald-50/50 dark:bg-emerald-900/10 rounded-2xl border border-emerald-100 dark:border-emerald-500/20 animate-in zoom-in">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-emerald-500 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-inner shadow-emerald-700/50">{{ selectedCustomer.full_name.charAt(0) }}</div>
                                <div>
                                    <span class="text-[9px] font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 block mb-0.5">Khách đặt phòng</span>
                                    <h4 class="text-lg font-black text-main-text dark:text-white leading-none tracking-tight">{{ selectedCustomer.full_name }}</h4>
                                    <p class="text-sm font-bold text-slate-500 mt-1">{{ selectedCustomer.phone }}</p>
                                </div>
                            </div>
                            <button @click="selectedCustomer = null; form.customer_id = ''" class="w-10 h-10 rounded-full bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border text-slate-400 hover:text-rose-500 hover:border-rose-200 flex items-center justify-center transition-colors shadow-sm" title="Hủy chọn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <div class="app-card !p-8 !rounded-[2rem] relative overflow-hidden">
                        <h3 class="text-[11px] font-black text-primary-500 uppercase tracking-[0.2em] mb-6 px-1">2. Lịch trình lưu trú</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1">Ngày Check-in</label>
                                <input v-model="form.check_in_expected" type="date" class="form-input-pms w-full">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1">Ngày Check-out</label>
                                <input v-model="form.check_out_expected" type="date" class="form-input-pms w-full">
                            </div>
                        </div>
                    </div>

                    <div class="app-card !p-8 !rounded-[2rem] relative overflow-hidden border-slate-200 dark:border-dark-border">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-slate-200/40 dark:bg-slate-700/20 rounded-full -mr-10 -mt-10 blur-2xl pointer-events-none"></div>
                        <h3 class="text-[11px] font-black text-primary-500 uppercase tracking-[0.2em] mb-6 px-1">3. Thanh toán & Yêu cầu</h3>

                        <div class="space-y-6">
                            <div class="space-y-3 relative p-5 bg-slate-50 dark:bg-dark-bg rounded-2xl border border-slate-200 dark:border-dark-border">
                                <label class="text-[11px] font-black text-main-text dark:text-white uppercase tracking-widest flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Tiền cọc (VNĐ)
                                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Tùy chọn</span>
                                </label>
                                <input v-model="form.deposit_amount" type="number" min="0" placeholder="Để trống nếu chưa thu cọc"
                                       class="form-input-pms w-full !border-slate-200 dark:!border-dark-border focus:!border-primary-500 focus:!ring-primary-500/20 !text-main-text dark:!text-white font-black text-lg input-number-clean">
                                <p class="text-[10px] font-bold text-muted-text italic">đặt phòng trước hạn phải cọc trước.</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1">Ghi chú cho bộ phận buồng (Tùy chọn)</label>
                                <textarea v-model="form.special_requests" rows="3" placeholder="Ví dụ: Cần nôi em bé, setup trăng mật..." class="form-input-pms w-full resize-none custom-scrollbar"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="bg-slate-900 text-white p-8 md:p-10 rounded-[3rem] shadow-2xl relative flex flex-col border border-white/5 lg:sticky lg:top-24">
                        <div class="absolute top-0 right-0 w-48 h-48 bg-primary-500/10 rounded-full blur-3xl -mr-24 -mt-24 pointer-events-none"></div>

                        <div class="flex justify-between items-center mb-8 relative z-10">
                            <h3 class="text-xl font-black uppercase tracking-tight text-white">Giỏ Hàng</h3>
                            <button @click="showRoomPicker = true" :disabled="!form.check_in_expected || !form.check_out_expected"
                                    class="bg-primary-500 hover:bg-primary-600 text-white px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all disabled:opacity-30 disabled:grayscale shadow-lg shadow-primary-500/30 active:scale-95 flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Thêm Phòng
                            </button>
                        </div>

                        <div class="flex-1 space-y-4 overflow-y-auto max-h-[300px] custom-scrollbar-dark pr-2 relative z-10 mb-8">
                            <div v-for="(room, index) in form.rooms" :key="room.room_id"
                                 class="p-5 bg-white/5 border border-white/10 rounded-2xl flex justify-between items-center animate-in slide-in-from-right-5">
                                <div>
                                    <h4 class="text-xl font-black tracking-tighter text-white">Phòng {{ room.room_number }}</h4>
                                    <p class="text-[10px] font-bold uppercase text-slate-400 tracking-widest mt-0.5">{{ room.definition_name }}</p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="text-sm font-black text-primary-400">{{ formatCurrency(room.price) }}</span>
                                    <button @click="removeRoomFromCart(index)" class="w-8 h-8 flex items-center justify-center bg-rose-500/10 hover:bg-rose-500 text-rose-500 hover:text-white rounded-lg transition-colors" title="Xóa phòng">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>

                            <div v-if="form.rooms.length === 0" class="py-16 text-center border-2 border-dashed border-white/10 rounded-3xl">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-1">Chưa có phòng nào</p>
                                <p class="text-xs font-bold text-slate-600">Vui lòng chọn ngày và bấm "Thêm Phòng"</p>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-white/10 relative z-10 space-y-4">
                            <div class="flex justify-between items-center text-slate-300">
                                <span class="text-sm font-bold">Tạm tính tiền phòng ({{ stayNights }} đêm)</span>
                                <span class="text-base font-black">{{ formatCurrency(totalEstimate) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-300">
                                <span class="text-sm font-bold">Số tiền đã cọc</span>
                                <span class="text-base font-black">
                                    <template v-if="hasDeposit">- {{ formatCurrency(form.deposit_amount || 0) }}</template>
                                    <template v-else>Chưa thu cọc</template>
                                </span>
                            </div>

                            <div class="flex justify-between items-end pt-4 border-t border-white/5">
                                <span class="text-[10px] font-black uppercase text-slate-400 tracking-[0.2em]">CÒN LẠI PHẢI THU</span>
                                <span class="text-3xl font-black text-primary-400 tracking-tighter italic">{{ formatCurrency(remainingBalance > 0 ? remainingBalance : 0) }}</span>
                            </div>

                            <button @click="submitBooking" :disabled="!canSubmitBooking || form.processing"
                                    class="btn-primary w-full !py-4 !rounded-2xl !text-sm !uppercase !tracking-widest disabled:opacity-30 disabled:grayscale mt-6 shadow-xl shadow-primary-500/20 active:scale-95 transition-all flex justify-center items-center gap-2">
                                <svg v-if="form.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                  {{ form.processing ? 'Đang khởi tạo...' : (canSubmitBooking ? 'Xác nhận và Lưu' : 'Nhập đủ thông tin để chốt đơn') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showCustomerModal" class="fixed inset-0 z-[300] flex items-center justify-center p-4 sm:p-6 bg-slate-900/60 backdrop-blur-md">
            <div class="bg-white dark:bg-dark-card !rounded-[2.5rem] w-full max-w-2xl shadow-2xl animate-in zoom-in-95 duration-300 relative flex flex-col max-h-[90vh] border border-slate-100 dark:border-dark-border">

                <div class="absolute top-0 right-0 w-64 h-64 bg-primary-500/10 rounded-full -mr-32 -mt-32 blur-3xl pointer-events-none"></div>

                <div class="px-8 py-6 border-b border-slate-100 dark:border-dark-border flex justify-between items-center shrink-0 relative z-10">
                    <div>
                        <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.3em] block mb-1">Khách hàng vãng lai</span>
                        <h3 class="text-2xl font-black text-main-text dark:text-white uppercase tracking-tight">Thêm Khách Mới</h3>
                    </div>
                    <button @click="showCustomerModal = false" class="w-10 h-10 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/20 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-8 overflow-y-auto custom-scrollbar relative z-10">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-2 sm:col-span-2 md:col-span-1">
                            <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1">Họ và tên khách <span class="text-rose-500">*</span></label>
                            <input v-model="customerForm.full_name" type="text" placeholder="Vd: Nguyễn Văn A" class="form-input-pms w-full">
                            <p v-if="customerForm.errors.full_name" class="text-[10px] font-bold text-rose-500 px-1">{{ customerForm.errors.full_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1">Số điện thoại <span class="text-rose-500">*</span></label>
                            <input v-model="customerForm.phone" type="text" inputmode="numeric" pattern="[0-9]*" placeholder="Vd: 0901234567" class="form-input-pms w-full input-number-clean" @input="customerForm.phone = sanitizePhone(customerForm.phone)">
                            <p v-if="customerForm.errors.phone" class="text-[10px] font-bold text-rose-500 px-1">{{ customerForm.errors.phone }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1">Email</label>
                            <input v-model="customerForm.email" type="email" placeholder="email@domain.com" class="form-input-pms w-full">
                            <p v-if="customerForm.errors.email" class="text-[10px] font-bold text-rose-500 px-1">{{ customerForm.errors.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1">CCCD / CMND</label>
                            <input v-model="customerForm.cccd_number" type="text" placeholder="Nhập số định danh..." class="form-input-pms w-full">
                            <p v-if="customerForm.errors.cccd_number" class="text-[10px] font-bold text-rose-500 px-1">{{ customerForm.errors.cccd_number }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 sm:col-span-2">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1">Ngày sinh</label>
                                <input v-model="customerForm.birthday" type="date" class="form-input-pms w-full">
                                <p v-if="customerForm.errors.birthday" class="text-[10px] font-bold text-rose-500 px-1">{{ customerForm.errors.birthday }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1">Giới tính</label>
                                <select v-model="customerForm.gender" class="form-input-pms appearance-none cursor-pointer w-full">
                                    <option value="male">Nam</option>
                                    <option value="female">Nữ</option>
                                    <option value="other">Khác</option>
                                </select>
                                <p v-if="customerForm.errors.gender" class="text-[10px] font-bold text-rose-500 px-1">{{ customerForm.errors.gender }}</p>
                            </div>
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1">Địa chỉ thường trú</label>
                            <textarea v-model="customerForm.address" rows="2" placeholder="Số nhà, đường, phường/xã..." class="form-input-pms resize-none w-full custom-scrollbar"></textarea>
                            <p v-if="customerForm.errors.address" class="text-[10px] font-bold text-rose-500 px-1">{{ customerForm.errors.address }}</p>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-6 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-dark-border flex flex-col sm:flex-row gap-3 shrink-0">
                    <button @click="showCustomerModal = false" class="w-full sm:w-1/3 py-3.5 rounded-xl font-bold text-slate-600 bg-white dark:bg-dark-card border border-slate-200 dark:border-dark-border hover:bg-slate-100 dark:hover:bg-slate-800 dark:text-slate-300 text-sm transition-colors shadow-sm">
                        Hủy bỏ
                    </button>
                    <button @click="submitNewCustomer" :disabled="isSavingCustomer" class="w-full sm:w-2/3 flex-1 btn-primary !py-3.5 !rounded-xl !text-xs !uppercase !tracking-widest shadow-xl shadow-primary-500/20 active:scale-95 transition-all flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                        <svg v-if="isSavingCustomer" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ isSavingCustomer ? 'Đang lưu hệ thống...' : 'Lưu & Chọn khách này' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="showRoomPicker" class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6 bg-slate-900/80 backdrop-blur-md">
            <div class="bg-white dark:bg-dark-card rounded-[2.5rem] w-full max-w-5xl max-h-[90vh] flex flex-col shadow-2xl animate-in zoom-in-95 duration-300 relative overflow-hidden border border-slate-100 dark:border-dark-border">
                <div class="absolute top-0 right-0 w-64 h-64 bg-primary-500/5 rounded-full -mr-24 -mt-24 blur-3xl pointer-events-none"></div>

                <div class="px-8 py-6 border-b border-slate-100 dark:border-dark-border flex justify-between items-center relative z-10 shrink-0">
                    <div>
                        <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.3em] mb-1 block">Kho phòng hệ thống</span>
                        <h3 class="text-2xl font-black text-main-text dark:text-white uppercase tracking-tight">Chọn Phòng Trống</h3>
                    </div>
                    <button @click="showRoomPicker = false" class="w-10 h-10 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/20 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="p-6 bg-slate-50 dark:bg-dark-bg/50 border-b border-slate-100 dark:border-dark-border relative z-10 shrink-0 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="relative">
                        <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1 block mb-1.5">Số phòng</label>
                        <input v-model="roomSearchQuery" type="text" placeholder="Ví dụ: 101..." class="form-input-pms form-input-pms-compact w-full !pl-10">
                        <svg class="w-4 h-4 absolute left-3.5 bottom-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>

                    <div class="relative">
                        <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1 block mb-1.5">Hạng phòng</label>
                        <select v-model="filterDefinition" class="form-input-pms form-input-pms-compact w-full cursor-pointer">
                            <option value="">Tất cả hạng phòng</option>
                            <option v-for="def in roomDefinitions" :key="def.id" :value="def.id">{{ def.name }}</option>
                        </select>
                    </div>

                    <div class="relative">
                        <label class="text-[10px] font-black text-muted-text uppercase tracking-widest px-1 block mb-1.5">Sức chứa tối thiểu</label>
                        <input v-model="filterCapacity" type="number" placeholder="Số người ở..." class="form-input-pms form-input-pms-compact w-full input-number-clean" min="1">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6 md:p-8 custom-scrollbar relative z-10">
                    <div v-if="filteredAvailableRooms.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                        <div v-for="room in filteredAvailableRooms" :key="room.id" @click="addRoomToCart(room)"
                             class="bg-white dark:bg-dark-card border-2 border-emerald-100 dark:border-emerald-500/30 p-5 rounded-2xl hover:border-primary-500 dark:hover:border-primary-500 cursor-pointer transition-all shadow-sm hover:shadow-md group relative overflow-hidden flex flex-col justify-between h-[120px] bg-emerald-50/30 dark:bg-emerald-900/10">
                            <div>
                                <h4 class="text-xl font-black text-main-text dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors tracking-tight">P.{{ room.room_number }}</h4>
                                <p class="text-[10px] font-bold text-muted-text uppercase mt-0.5 tracking-widest truncate" :title="room.definition?.name">{{ room.definition?.name }}</p>
                            </div>
                            <div class="flex justify-between items-end">
                                <div class="flex flex-col gap-1">
                                    <p class="text-sm font-black text-primary-500">{{ formatCurrency(room.definition?.base_price) }}</p>
                                    <span class="inline-block px-2 py-0.5 rounded-lg text-[8px] font-black uppercase tracking-widest bg-emerald-500/20 text-emerald-600 dark:bg-emerald-500/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/50 w-max">
                                        ✓ Trống
                                    </span>
                                </div>
                                <span class="w-6 h-6 rounded-full bg-primary-50 text-primary-500 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="w-full h-full flex flex-col items-center justify-center py-20">
                        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center text-slate-300 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        </div>
                        <p class="text-sm font-black text-main-text dark:text-white">Không có phòng trống!</p>
                        <p class="text-xs text-muted-text mt-1 text-center max-w-sm">Tất cả phòng đang được chiếm, bảo trì hoặc dọn dẹp. Thử thay đổi ngày hoặc quay lại sau.</p>
                    </div>
                </div>

                <div class="p-6 border-t border-slate-100 dark:border-dark-border bg-slate-50 dark:bg-dark-bg/50 text-center relative z-10 shrink-0">
                    <button @click="showRoomPicker = false" class="px-10 md:px-16 py-3.5 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 rounded-xl font-black uppercase text-xs tracking-widest shadow-lg active:scale-95 transition-all">
                        Hoàn tất (Đang chọn {{ form.rooms.length }} phòng)
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
