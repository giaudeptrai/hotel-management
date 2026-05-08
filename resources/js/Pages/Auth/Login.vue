<script setup>
import { ref, computed } from 'vue'; // 🎯 Nhớ import ref
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

// 🎯 Biến quản lý trạng thái ẩn/hiện mật khẩu
const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const normalizeErrorMessage = (rawMessage, field = '') => {
    if (!rawMessage) return '';

    const message = String(rawMessage).trim();
    const lower = message.toLowerCase();

    if (
        lower.includes('auth.failed') ||
        lower.includes('credentials') ||
        lower.includes('thông tin đăng nhập') ||
        lower.includes('không khớp')
    ) {
        return 'Email hoặc mật khẩu không đúng. Vui lòng kiểm tra lại.';
    }

    if (lower.includes('too many attempts') || lower.includes('throttle')) {
        return 'Bạn đăng nhập sai quá nhiều lần. Vui lòng thử lại sau ít phút.';
    }

    if (field === 'email' && (lower.includes('validation.required') || lower === 'required')) {
        return 'Vui lòng nhập email.';
    }

    if (field === 'password' && (lower.includes('validation.required') || lower === 'required')) {
        return 'Vui lòng nhập mật khẩu.';
    }

    if (field === 'email' && (lower.includes('validation.email') || lower.includes('valid email'))) {
        return 'Email không đúng định dạng.';
    }

    return message.replace(/^validation\./i, '');
};

const rawEmailError = computed(() => form.errors.email || '');
const rawPasswordError = computed(() => form.errors.password || '');

const isCredentialError = computed(() => {
    const raw = `${rawEmailError.value} ${rawPasswordError.value}`.toLowerCase();
    return (
        raw.includes('auth.failed') ||
        raw.includes('credentials') ||
        raw.includes('thông tin đăng nhập') ||
        raw.includes('không khớp')
    );
});

const authErrorMessage = computed(() => {
    if (!isCredentialError.value) return '';
    return 'Email hoặc mật khẩu không đúng. Vui lòng thử lại.';
});

const emailErrorMessage = computed(() => {
    if (isCredentialError.value) return '';
    return normalizeErrorMessage(rawEmailError.value, 'email');
});

const passwordErrorMessage = computed(() => {
    if (isCredentialError.value) return '';
    return normalizeErrorMessage(rawPasswordError.value, 'password');
});

// Chuyển hướng sang Google Login
const loginWithGoogle = () => {
    window.location.href = route('auth.google');
};
</script>

<template>
    <Head title="Đăng Nhập - Dasher Hotel" />

    <div class="min-h-screen flex items-center justify-center p-4 sm:p-8">

        <div class="app-card !p-0 w-full max-w-5xl flex flex-col md:flex-row overflow-hidden shadow-2xl">

            <div class="w-full md:w-1/2 bg-slate-900 relative p-12 flex flex-col justify-between hidden md:flex overflow-hidden">
               <div class="absolute inset-0 opacity-50 transition-transform duration-1000 hover:scale-105">
                    <img src="https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1200" alt="Khách sạn Dasher" class="w-full h-full object-cover" />
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>

                <div class="relative z-10">
                    <Link href="/" class="text-3xl font-black text-white tracking-tighter italic hover:text-primary-500 transition-colors inline-block">
                        DASHER<span class="text-primary-500">HOTEL</span>
                    </Link>
                    <p class="admin-index-subtitle !text-slate-300 mt-2">Trải Nghiệm Lưu Trú Đẳng Cấp</p>
                </div>

                <div class="relative z-10 space-y-4">
                    <h1 class="text-4xl font-black text-white leading-tight">Tận hưởng kỳ nghỉ,<br><span class="text-primary-400">Trọn vẹn từng phút giây.</span></h1>
                    <p class="text-sm text-slate-300 font-medium">Đăng nhập để quản lý lịch trình đặt phòng, nhận ưu đãi độc quyền và trải nghiệm dịch vụ tuyệt vời nhất.</p>
                </div>
            </div>

            <div class="w-full md:w-1/2 p-10 sm:p-14 flex flex-col justify-center relative z-10">

                <div class="mb-10">
                    <span class="admin-index-subtitle text-primary-500 block mb-2">Chào mừng trở lại 👋</span>
                    <h2 class="text-title !text-4xl">Đăng Nhập</h2>
                </div>

                <div v-if="status" class="mb-4 font-medium text-sm text-emerald-600 bg-emerald-50 p-4 rounded-xl border border-emerald-100">
                    {{ status }}
                </div>

                <div v-if="authErrorMessage" class="mb-4 text-sm font-bold text-rose-600 bg-rose-50 p-4 rounded-xl border border-rose-100">
                    {{ authErrorMessage }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">

                    <div class="space-y-2">
                        <label class="admin-index-subtitle px-1 block">Email / Tài khoản</label>
                        <input v-model="form.email" type="email" required autofocus autocomplete="username"
                               class="app-input"
                               placeholder="hoten@email.com">
                        <p v-if="emailErrorMessage" class="text-[10px] font-bold text-rose-500 uppercase mt-1 px-1">{{ emailErrorMessage }}</p>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center px-1">
                            <label class="admin-index-subtitle block">Mật khẩu</label>
                        </div>

                        <div class="relative">
                            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required autocomplete="current-password"
                                   class="app-input !pr-12"
                                   placeholder="••••••••">

                            <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary-500 transition-colors focus:outline-none">
                                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>

                        <p v-if="passwordErrorMessage" class="text-[10px] font-bold text-rose-500 uppercase mt-1 px-1">{{ passwordErrorMessage }}</p>

                        <div class="flex justify-end pt-1">
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-[10px] font-black text-primary-500 hover:text-primary-600 uppercase tracking-widest transition-colors">
                                Quên mật khẩu?
                            </Link>
                        </div>
                    </div>

                    <div class="flex items-center px-1">
                        <label class="flex items-center cursor-pointer group">
                            <input v-model="form.remember" type="checkbox" class="w-5 h-5 rounded-md border-slate-300 text-primary-500 shadow-sm focus:ring-primary-500">
                            <span class="ml-3 text-desc group-hover:text-main-text dark:group-hover:text-white transition-colors font-bold">Ghi nhớ đăng nhập</span>
                        </label>
                    </div>

                    <button :disabled="form.processing" class="btn-primary !w-full !py-4 !uppercase !tracking-widest disabled:opacity-50 mt-2">
                        {{ form.processing ? 'Đang xác thực...' : 'Đăng nhập' }}
                    </button>

                </form>

                <div class="mt-8 mb-6 flex items-center justify-between">
                    <div class="h-px bg-slate-100 dark:bg-dark-border flex-1"></div>
                    <span class="px-4 text-[10px] font-black text-muted-text uppercase tracking-widest">Hoặc đăng nhập nhanh</span>
                    <div class="h-px bg-slate-100 dark:bg-dark-border flex-1"></div>
                </div>

                <button @click="loginWithGoogle" type="button" class="w-full flex items-center justify-center gap-3 bg-slate-50 hover:bg-slate-100 dark:bg-dark-bg dark:hover:bg-slate-800 border-2 border-transparent hover:border-slate-200 dark:hover:border-dark-border py-3.5 rounded-[1.25rem] text-[11px] font-black text-main-text dark:text-white uppercase tracking-widest transition-all active:scale-95 shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Google
                </button>

                <div class="mt-8 text-center">
                    <p class="text-xs font-bold text-muted-text">
                        Chưa có tài khoản?
                        <Link :href="route('register')" class="text-primary-500 hover:text-primary-600 transition-colors uppercase tracking-widest ml-1">
                            Đăng ký ngay
                        </Link>
                    </p>
                </div>

            </div>
        </div>
    </div>
</template>
