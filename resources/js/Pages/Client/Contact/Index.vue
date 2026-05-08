<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

const props = defineProps({
    contact: { type: Object, default: () => ({}) },
    prefill: { type: Object, default: () => ({}) },
});

const page = usePage();
const flashSuccess = computed(() => page.props.flash?.success || '');
const localSuccess = ref('');
const successNotice = computed(() => localSuccess.value || flashSuccess.value);
const showSuccessModal = ref(false);

const contactForm = useForm({
    name: props.prefill?.name || '',
    phone: props.prefill?.phone || '',
    email: props.prefill?.email || '',
    subject: '',
    message: '',
});

const sanitizePhone = (value) => value.replace(/\D/g, '');

const submitSupportRequest = () => {
    contactForm.post(route('client.contact.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessModal.value = true;
            localSuccess.value = 'Yêu cầu hỗ trợ đã gửi thành công. Chúng tôi sẽ phản hồi sớm nhất.';
            contactForm.reset('subject', 'message');
        },
    });
};
</script>

<template>
    <Head title="Liên hệ - Dasher Hotel" />

    <ClientLayout>
        <section class="relative bg-slate-900 pb-32 pt-32 lg:pt-40 lg:pb-40">
            <div class="absolute inset-0">
                <img src="https://images.pexels.com/photos/338504/pexels-photo-338504.jpeg?auto=compress&cs=tinysrgb&w=2200" alt="Liên hệ Dasher Hotel" class="h-full w-full object-cover opacity-40" />
                <div class="absolute inset-0 bg-slate-900/60"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-white backdrop-blur mb-4">
                    Dasher Support
                </span>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl mb-6">
                    Kết nối với <span class="text-primary-400">Dasher</span>
                </h1>
                <p class="text-lg text-slate-300 max-w-2xl mx-auto">
                    Bạn cần hỗ trợ đặt phòng, tư vấn dịch vụ hay có yêu cầu đặc biệt? Đội ngũ của chúng tôi luôn sẵn sàng lắng nghe và giải quyết nhanh chóng.
                </p>
            </div>
        </section>

        <section class="relative z-20 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 -mt-20 pb-24">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <div class="lg:col-span-5 relative overflow-hidden rounded-[2.5rem] bg-slate-900 p-8 sm:p-10 text-white shadow-2xl border border-slate-800 h-full">
                    <div class="absolute inset-0 pointer-events-none">
                        <div class="absolute -left-20 top-10 h-64 w-64 rounded-full bg-primary-500/20 blur-3xl"></div>
                        <div class="absolute -right-20 -bottom-20 h-64 w-64 rounded-full bg-emerald-500/20 blur-3xl"></div>
                    </div>

                    <div class="relative z-10 flex flex-col h-full">
                        <div class="mb-12">
                            <h2 class="text-3xl font-black tracking-tight mb-4">Thông tin trực tiếp</h2>
                            <p class="text-sm font-medium leading-relaxed text-slate-400">
                                Bộ phận Lễ tân hoạt động 24/7. Vui lòng liên hệ qua các kênh dưới đây nếu bạn cần hỗ trợ khẩn cấp.
                            </p>
                        </div>

                        <div class="space-y-8 flex-1">
                            <div class="flex items-start gap-5">
                                <div class="w-12 h-12 rounded-2xl bg-white/10 text-primary-400 flex items-center justify-center shrink-0 border border-white/10 backdrop-blur-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Hotline Hỗ Trợ</p>
                                    <a :href="`tel:${contact.phone || '0792008096'}`" class="text-xl font-black hover:text-primary-400 transition-colors">{{ contact.phone || '0792 008 096' }}</a>
                                </div>
                            </div>

                            <div class="flex items-start gap-5">
                                <div class="w-12 h-12 rounded-2xl bg-white/10 text-primary-400 flex items-center justify-center shrink-0 border border-white/10 backdrop-blur-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Email Liên Hệ</p>
                                    <a :href="`mailto:${contact.email || 'contact@dasherhotel.vn'}`" class="text-base font-bold text-slate-300 hover:text-primary-400 transition-colors break-all">{{ contact.email || 'contact@dasherhotel.vn' }}</a>
                                </div>
                            </div>

                            <div class="flex items-start gap-5">
                                <div class="w-12 h-12 rounded-2xl bg-white/10 text-primary-400 flex items-center justify-center shrink-0 border border-white/10 backdrop-blur-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Địa Chỉ Khách Sạn</p>
                                    <p class="text-sm font-bold text-slate-300 leading-relaxed">{{ contact.address || 'Khu đô thị mới, TP. Long Xuyên, An Giang' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-12 pt-8 border-t border-slate-800">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 text-center">Dasher Hotel © 2026</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 app-card !p-8 md:!p-10 !rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-dark-border bg-white dark:bg-dark-card">

                    <div class="mb-8">
                        <span class="admin-index-subtitle block mb-2">Gửi yêu cầu trực tuyến</span>
                        <h2 class="admin-index-title !text-3xl">Gửi yêu cầu hỗ trợ</h2>
                        <p class="text-desc mt-2 text-sm leading-relaxed">
                            Vui lòng điền đầy đủ thông tin để hệ thống ghi nhận yêu cầu và chuyển cho bộ phận lễ tân xử lý.
                        </p>
                    </div>

                    <form @submit.prevent="submitSupportRequest" class="space-y-6">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2 md:col-span-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">Họ và Tên</label>
                                <input v-model="contactForm.name" type="text" class="form-input-pms w-full" placeholder="Nhập tên của bạn">
                                <p v-if="contactForm.errors.name" class="text-[11px] font-bold text-rose-500 px-1">{{ contactForm.errors.name }}</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">Số điện thoại</label>
                                <input v-model="contactForm.phone" type="tel" inputmode="numeric" pattern="[0-9]*" class="form-input-pms w-full input-number-clean" placeholder="09xx xxx xxx" @input="contactForm.phone = sanitizePhone(contactForm.phone)">
                                <p v-if="contactForm.errors.phone" class="text-[11px] font-bold text-rose-500 px-1">{{ contactForm.errors.phone }}</p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">Email liên lạc</label>
                                <input v-model="contactForm.email" type="email" class="form-input-pms w-full" placeholder="email@domain.com">
                                <p v-if="contactForm.errors.email" class="text-[11px] font-bold text-rose-500 px-1">{{ contactForm.errors.email }}</p>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">Chủ đề hỗ trợ</label>
                                <input v-model="contactForm.subject" type="text" class="form-input-pms w-full" placeholder="Ví dụ: Tư vấn đặt phòng cho gia đình">
                                <p class="text-[11px] font-medium text-slate-400 px-1">Bạn có thể bỏ trống, hệ thống sẽ tự gán chủ đề mặc định.</p>
                                <p v-if="contactForm.errors.subject" class="text-[11px] font-bold text-rose-500 px-1">{{ contactForm.errors.subject }}</p>
                            </div>

                            <div class="space-y-2 md:col-span-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">Nội dung chi tiết</label>
                                <textarea v-model="contactForm.message" rows="5" class="form-input-pms w-full resize-none custom-scrollbar" placeholder="Bạn cần hỗ trợ về vấn đề gì? Hãy mô tả chi tiết..."></textarea>
                                <p v-if="contactForm.errors.message" class="text-[11px] font-bold text-rose-500 px-1">{{ contactForm.errors.message }}</p>
                            </div>
                        </div>

                        <div class="pt-4 flex flex-col sm:flex-row items-center gap-4">
                            <button type="submit" :disabled="contactForm.processing" class="btn-primary !w-full sm:!w-auto !px-8 !py-4 shadow-lg shadow-primary-500/30 flex justify-center items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                {{ contactForm.processing ? 'Đang gửi...' : 'Gửi yêu cầu hỗ trợ' }}
                            </button>
                        </div>

                        <div class="mt-6 rounded-2xl bg-amber-50 p-4 border border-amber-100 dark:bg-amber-900/10 dark:border-amber-500/20">
                            <p class="text-xs font-bold text-amber-700 dark:text-amber-400 flex items-start gap-2 leading-relaxed">
                                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span><strong class="font-black text-amber-800 dark:text-amber-300">Lưu ý:</strong> Nếu bạn đang cần hỗ trợ về đơn đặt phòng đã tạo, vui lòng ghi rõ mã đơn (Ví dụ: #BK-12345) trong nội dung để Lễ tân kiểm tra nhanh nhất.</span>
                            </p>
                        </div>
                    </form>
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
                        Cảm ơn bạn đã liên hệ. Bộ phận Lễ tân đã nhận được yêu cầu hỗ trợ và sẽ liên hệ sớm nhất.
                    </p>
                    <button type="button" @click="showSuccessModal = false" class="btn-primary !w-full !py-4 shadow-lg shadow-primary-500/30">
                        Quay lại liên hệ
                    </button>
                </div>
            </div>
        </transition>
    </ClientLayout>
</template>
