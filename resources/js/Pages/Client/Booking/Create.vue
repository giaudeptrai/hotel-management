<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps({
    room: { type: Object, default: null },
    roomAvailability: { type: Object, default: null },
    contact: {
        type: Object,
        default: () => ({
            full_name: '',
            phone: '',
            email: '',
        }),
    },
    selection: {
        type: Object,
        default: () => ({
            check_in: '',
            check_out: '',
            guests: 1,
            nights: 1,
        }),
    },
});

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);

const stayNights = computed(() => Number(props.selection?.nights || 1));
const roomPrice = computed(() => Number(props.room?.base_price || 0));
const subtotal = computed(() => roomPrice.value * stayNights.value);
const isBookable = computed(() => Boolean(props.room?.is_bookable));
const availableRoomNumbers = computed(() => props.roomAvailability?.available_room_numbers || []);
const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success || '');
const roomId = computed(() => props.room?.id || null);
const roomHeroImage = computed(() => props.room?.image_urls?.[0] || 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&cs=tinysrgb&w=2000');

const selection = computed(() => ({
    check_in: props.selection?.check_in || '',
    check_out: props.selection?.check_out || '',
    guests: Number(props.selection?.guests || 1),
}));

const contact = computed(() => ({
    full_name: props.contact?.full_name || '',
    phone: props.contact?.phone || '',
    email: props.contact?.email || '',
}));

const bookingForm = useForm({
    room_id: roomId.value,
    check_in: selection.value.check_in,
    check_out: selection.value.check_out,
    guests: selection.value.guests,
    full_name: contact.value.full_name,
    phone: contact.value.phone,
    email: contact.value.email,
    special_requests: '',
});

const showSuccessModal = ref(false);

const submitBookingRequest = () => {
    if (!isBookable.value) {
        return;
    }

    bookingForm.room_id = roomId.value;
    bookingForm.check_in = selection.value.check_in;
    bookingForm.check_out = selection.value.check_out;
    bookingForm.guests = selection.value.guests;

    bookingForm.post(route('client.booking.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessModal.value = true;
        }
    });
};

const steps = [
    {
        title: '1. Xác nhận thông tin',
        desc: 'Kiểm tra lại ngày ở, số khách và loại phòng trước khi đặt cọc.',
    },
    {
        title: '2. Gửi đơn online',
        desc: 'Hệ thống sẽ gửi yêu cầu về Lễ tân để xếp phòng và duyệt đơn.',
    },
    {
        title: '3. Chờ xác nhận',
        desc: 'Lễ tân sẽ kiểm tra tình trạng phòng thực tế để tránh trùng lịch.',
    },
];
</script>

<template>
    <Head :title="room ? `Đặt phòng ${room.name} - Dasher Hotel` : 'Đặt phòng - Dasher Hotel'" />

    <ClientLayout>
        <section class="relative overflow-hidden bg-slate-950 pt-28 pb-16 border-b border-slate-800">
            <div class="absolute inset-0 opacity-35">
                <img
                    :src="roomHeroImage"
                    :alt="room?.name || 'Đặt phòng Dasher Hotel'"
                    class="h-full w-full object-cover"
                >
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/80 to-slate-900/40"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(245,158,11,0.22),transparent_28%),radial-gradient(circle_at_bottom_left,rgba(15,23,42,0.85),transparent_35%)]"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl space-y-5">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary-400 block mb-2">Thủ tục đặt phòng</span>
                    <h1 class="text-4xl md:text-5xl font-black tracking-tight text-white">
                        {{ room ? `Hoàn tất đặt ${room.name}` : 'Hoàn tất đặt phòng' }}
                    </h1>
                    <p class="text-base leading-8 text-slate-300">
                        Vui lòng điền thông tin liên hệ bên dưới. Yêu cầu của bạn sẽ được gửi trực tiếp đến bộ phận Lễ tân để xác nhận.
                    </p>
                </div>
            </div>
        </section>

        <section class="mx-auto -mt-8 max-w-7xl px-4 pb-24 sm:px-6 lg:px-8 relative z-20">
            <div v-if="flashSuccess" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700 flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ flashSuccess }}
            </div>

            <div class="grid gap-8 xl:grid-cols-[1.05fr_0.95fr]">

                <div class="space-y-8">

                    <div v-if="room && roomAvailability" class="app-card !rounded-[2.5rem] !p-6 sm:!p-8 border-2 border-emerald-500/20 shadow-lg shadow-emerald-500/5">
                        <div class="mb-6">
                            <span class="admin-index-subtitle block mb-2">Tra cứu tự động</span>
                            <h3 class="text-title text-2xl">Tình trạng khả dụng</h3>
                            <p class="text-sm font-bold text-slate-500 mt-1">Dựa trên khoảng thời gian từ {{ selection.check_in }} đến {{ selection.check_out }}</p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3 mb-6">
                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800/60 text-center border border-slate-100 dark:border-slate-700">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Tổng phòng</p>
                                <p class="text-2xl font-black text-main-text dark:text-white">{{ roomAvailability.total_rooms || 0 }}</p>
                            </div>
                            <div class="rounded-2xl bg-amber-50 p-4 dark:bg-amber-500/10 text-center border border-amber-100 dark:border-amber-500/20">
                                <p class="text-[10px] font-black uppercase tracking-widest text-amber-600 mb-1">Đã có đơn</p>
                                <p class="text-2xl font-black text-amber-600">{{ roomAvailability.occupied_rooms_count || 0 }}</p>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 p-4 dark:bg-emerald-500/10 text-center border border-emerald-200 dark:border-emerald-500/30 shadow-sm relative overflow-hidden">
                                <div class="absolute -right-4 -top-4 w-12 h-12 bg-emerald-500/20 rounded-full animate-ping"></div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-1 relative z-10">Còn trống</p>
                                <p class="text-2xl font-black text-emerald-600 relative z-10">{{ roomAvailability.available_rooms_count || 0 }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 p-4 bg-white dark:bg-dark-card dark:border-dark-border flex items-center justify-between">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Phòng có thể xếp ngay</p>
                                <p class="text-sm font-bold text-main-text dark:text-white">
                                    {{ availableRoomNumbers.length ? availableRoomNumbers.map((value) => `P.${value}`).join(', ') : 'Hiện tại không còn phòng trống.' }}
                                </p>
                            </div>
                            <div v-if="availableRoomNumbers.length" class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div v-for="step in steps" :key="step.title" class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm dark:border-dark-border dark:bg-dark-card">
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary-500 block mb-2">{{ step.title }}</span>
                            <p class="text-xs font-bold text-slate-500 leading-relaxed">{{ step.desc }}</p>
                        </div>
                    </div>

                    <div class="app-card !rounded-[2.5rem] !p-6 sm:!p-8">
                        <span class="admin-index-subtitle block mb-2">Hồ sơ khách hàng</span>
                        <h3 class="text-title text-2xl mb-6">Thông tin liên hệ</h3>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="space-y-2 md:col-span-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">Họ và tên <span class="text-rose-500">*</span></label>
                                <input v-model="bookingForm.full_name" type="text" required class="form-input-pms w-full" placeholder="Ví dụ: Nguyễn Văn A">
                                <p v-if="bookingForm.errors.full_name" class="text-[10px] font-bold text-rose-500 px-1">{{ bookingForm.errors.full_name }}</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">Số điện thoại <span class="text-rose-500">*</span></label>
                                <input v-model="bookingForm.phone" type="tel" required class="form-input-pms w-full" placeholder="09xx xxx xxx">
                                <p v-if="bookingForm.errors.phone" class="text-[10px] font-bold text-rose-500 px-1">{{ bookingForm.errors.phone }}</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">Email <span class="text-rose-500">*</span></label>
                                <input v-model="bookingForm.email" type="email" required class="form-input-pms w-full" placeholder="email@domain.com">
                                <p v-if="bookingForm.errors.email" class="text-[10px] font-bold text-rose-500 px-1">{{ bookingForm.errors.email }}</p>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">Yêu cầu đặc biệt (Tùy chọn)</label>
                                <textarea v-model="bookingForm.special_requests" rows="3" class="form-input-pms w-full resize-none custom-scrollbar" placeholder="Bạn có cần hỗ trợ gì thêm không? (Ví dụ: Nhận phòng muộn, tầng cao...)"></textarea>
                                <p v-if="bookingForm.errors.special_requests" class="text-[10px] font-bold text-rose-500 px-1">{{ bookingForm.errors.special_requests }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-8 lg:sticky lg:top-28 self-start">
                    <div class="app-card !rounded-[2.5rem] !p-6 sm:!p-8">
                        <span class="admin-index-subtitle block mb-2">Hóa đơn dự kiến</span>
                        <h2 class="text-title text-3xl mb-6">Tóm tắt đơn</h2>

                        <div v-if="room" class="space-y-4">
                            <div class="overflow-hidden rounded-2xl h-48 relative">
                                <img :src="room?.image_urls?.[0] || 'https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&cs=tinysrgb&w=800'" :alt="room.name" class="h-full w-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                                <div class="absolute bottom-3 left-4 right-4 flex justify-between items-end">
                                    <span class="bg-primary-500 text-white px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest">{{ room.category?.name || 'Phòng' }}</span>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Loại phòng</p>
                                    <p class="text-sm font-black text-main-text dark:text-white truncate">{{ room.type?.name || 'Chưa gán' }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Số đêm</p>
                                    <p class="text-sm font-black text-main-text dark:text-white">{{ stayNights }} đêm</p>
                                </div>
                            </div>

                            <div class="rounded-2xl bg-amber-50 p-5 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-500/20">
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ formatCurrency(roomPrice) }} x {{ stayNights }} đêm</span>
                                    <span class="text-sm font-black text-main-text dark:text-white">{{ formatCurrency(subtotal) }}</span>
                                </div>
                                <div class="flex justify-between items-end pt-3 border-t border-amber-200 dark:border-amber-500/30">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-amber-600 dark:text-amber-500">Tổng tạm tính</span>
                                    <span class="text-2xl font-black italic text-primary-500">{{ formatCurrency(subtotal) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-slate-200 border-dashed bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800 text-center">
                            <p class="text-xs font-bold text-slate-500 leading-relaxed">
                                Đơn đặt phòng sẽ được đối chiếu với lịch để tránh trùng. <strong class="text-main-text dark:text-white">Bạn không cần thanh toán lúc này.</strong>
                            </p>
                        </div>

                        <div class="mt-6 flex flex-col gap-3">
                            <button
                                :disabled="!isBookable || bookingForm.processing"
                                class="btn-primary !w-full !py-4 text-center text-sm uppercase tracking-widest shadow-xl shadow-primary-500/20 disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2"
                                @click="submitBookingRequest"
                            >
                                <svg v-if="bookingForm.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ isBookable ? (bookingForm.processing ? 'Đang gửi...' : 'Xác nhận & Gửi yêu cầu') : 'Đã hết phòng trống' }}
                            </button>

                            <Link :href="route('client.rooms.show', room?.id)" class="px-6 py-3.5 rounded-xl bg-white dark:bg-dark-card border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs uppercase tracking-widest text-center hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                                Quay lại phòng
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4">
            <div v-if="showSuccessModal" class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md">
                <div class="bg-white dark:bg-dark-card rounded-[3rem] p-10 md:p-12 max-w-md w-full text-center shadow-2xl relative overflow-hidden border border-emerald-100 dark:border-emerald-900/30">
                    <div class="w-24 h-24 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 border-[6px] border-white dark:border-dark-card shadow-inner">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-3xl font-black italic text-main-text dark:text-white mb-3">Gửi thành công!</h3>
                    <p class="text-sm font-bold text-slate-500 mb-8 leading-relaxed">
                        Yêu cầu đặt phòng đã được ghi nhận. Nhân viên khách sạn sẽ liên hệ với quý khách qua Số điện thoại để xác thực và hướng dẫn đặt cọc.
                    </p>
                    <Link :href="route('client.rooms.index')" class="btn-primary !w-full block text-center !py-4 shadow-lg shadow-primary-500/30">
                        Về trang chủ
                    </Link>
                </div>
            </div>
        </transition>

    </ClientLayout>
</template>
