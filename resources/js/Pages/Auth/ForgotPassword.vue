<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Quên Mật Khẩu - Dasher Hotel" />

    <div class="min-h-screen flex items-center justify-center p-4 sm:p-8">

        <div class="app-card !p-0 w-full max-w-5xl flex flex-col md:flex-row overflow-hidden shadow-2xl">

            <div class="w-full md:w-1/2 bg-slate-900 relative p-12 flex flex-col justify-between hidden md:flex overflow-hidden">
                <div class="absolute inset-0 opacity-50 transition-transform duration-1000 hover:scale-105">
                    <img src="https://images.pexels.com/photos/261394/pexels-photo-261394.jpeg?auto=compress&cs=tinysrgb&w=1200" alt="Khách sạn Dasher" class="w-full h-full object-cover" />
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>

                <div class="relative z-10">
                    <Link href="/" class="text-3xl font-black text-white tracking-tighter italic hover:text-primary-500 transition-colors inline-block">
                        DASHER<span class="text-primary-500">HOTEL</span>
                    </Link>
                    <p class="admin-index-subtitle !text-slate-300 mt-2">Hỗ trợ khách hàng 24/7</p>
                </div>

                <div class="relative z-10 space-y-4">
                    <h1 class="text-4xl font-black text-white leading-tight">Đừng lo lắng,<br><span class="text-primary-400">Chúng tôi ở đây để giúp.</span></h1>
                    <p class="text-sm text-slate-300 font-medium">Chỉ cần cung cấp email đã đăng ký, hệ thống sẽ gửi ngay một liên kết an toàn để bạn thiết lập lại mật khẩu và tiếp tục trải nghiệm.</p>
                </div>
            </div>

            <div class="w-full md:w-1/2 p-10 sm:p-14 flex flex-col justify-center relative z-10">

                <div class="mb-10">
                    <span class="admin-index-subtitle text-primary-500 block mb-2">Khôi phục tài khoản 🔐</span>
                    <h2 class="text-title !text-4xl">Quên Mật Khẩu?</h2>
                </div>

                <div v-if="status" class="mb-8 font-bold text-sm text-emerald-600 bg-emerald-50 p-4 rounded-2xl border border-emerald-100 flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ status }}</span>
                </div>

                <div v-else class="mb-8 text-sm font-bold text-slate-500 leading-relaxed">
                    Nhập địa chỉ email mà bạn đã dùng để tạo tài khoản. Chúng tôi sẽ gửi cho bạn một liên kết bảo mật để thiết lập lại mật khẩu mới.
                </div>

                <form @submit.prevent="submit" class="space-y-6">

                    <div class="space-y-2">
                        <label class="admin-index-subtitle px-1 block">Email xác thực</label>
                        <input v-model="form.email" type="email" required autofocus autocomplete="username"
                               class="app-input"
                               placeholder="Nhập email của bạn...">
                        <p v-if="form.errors.email" class="text-[10px] font-bold text-rose-500 uppercase mt-1 px-1">{{ form.errors.email }}</p>
                    </div>

                    <button :disabled="form.processing" class="btn-primary !w-full !py-4 !mt-2 !uppercase !tracking-widest disabled:opacity-50">
                        {{ form.processing ? 'Đang gửi yêu cầu...' : 'Gửi liên kết khôi phục' }}
                    </button>

                </form>

                <div class="mt-10 text-center">
                    <p class="text-xs font-bold text-muted-text">
                        Đã nhớ lại mật khẩu?
                        <Link :href="route('login')" class="text-primary-500 hover:text-primary-600 transition-colors uppercase tracking-widest ml-1">
                            Quay lại đăng nhập
                        </Link>
                    </p>
                </div>

            </div>
        </div>
    </div>
</template>
