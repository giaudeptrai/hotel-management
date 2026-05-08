<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

// 🎯 Biến quản lý trạng thái ẩn/hiện mật khẩu
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Thiết Lập Lại Mật Khẩu - Dasher Hotel" />

    <div class="min-h-screen flex items-center justify-center p-4 sm:p-8">

        <div class="app-card !p-0 w-full max-w-5xl flex flex-col md:flex-row overflow-hidden shadow-2xl">

            <div class="w-full md:w-1/2 bg-slate-900 relative p-12 flex flex-col justify-between hidden md:flex overflow-hidden">
                <div class="absolute inset-0 opacity-50 transition-transform duration-1000 hover:scale-105">
                    <img src="https://images.pexels.com/photos/271618/pexels-photo-271618.jpeg?auto=compress&cs=tinysrgb&w=1200" alt="Khách sạn Dasher" class="w-full h-full object-cover" />
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>

                <div class="relative z-10">
                    <Link href="/" class="text-3xl font-black text-white tracking-tighter italic hover:text-primary-500 transition-colors inline-block">
                        DASHER<span class="text-primary-500">HOTEL</span>
                    </Link>
                    <p class="admin-index-subtitle !text-slate-300 mt-2">Bảo mật tuyệt đối</p>
                </div>

                <div class="relative z-10 space-y-4">
                    <h1 class="text-4xl font-black text-white leading-tight">Cánh cửa mới,<br><span class="text-primary-400">Trải nghiệm không đổi.</span></h1>
                    <p class="text-sm text-slate-300 font-medium">Vui lòng thiết lập một mật khẩu mới đủ mạnh để bảo vệ tài khoản hội viên và các ưu đãi của bạn.</p>
                </div>
            </div>

            <div class="w-full md:w-1/2 p-10 sm:p-14 flex flex-col justify-center relative z-10">

                <div class="mb-10">
                    <span class="admin-index-subtitle text-primary-500 block mb-2">Bước cuối cùng 🔑</span>
                    <h2 class="text-title !text-4xl">Đổi Mật Khẩu Mới</h2>
                </div>

                <form @submit.prevent="submit" class="space-y-6">

                    <div class="space-y-2">
                        <label class="admin-index-subtitle px-1 block">Email tài khoản</label>
                        <input v-model="form.email" type="email" required autofocus autocomplete="username"
                               class="app-input opacity-70 bg-slate-50 cursor-not-allowed"
                               readonly>
                        <p v-if="form.errors.email" class="text-[10px] font-bold text-rose-500 uppercase mt-1 px-1">{{ form.errors.email }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="admin-index-subtitle px-1 block">Mật khẩu mới</label>
                        <div class="relative">
                            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" required autocomplete="new-password"
                                   class="app-input !pr-12"
                                   placeholder="Nhập mật khẩu mới...">

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
                        <p v-if="form.errors.password" class="text-[10px] font-bold text-rose-500 uppercase mt-1 px-1">{{ form.errors.password }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="admin-index-subtitle px-1 block">Xác nhận mật khẩu mới</label>
                        <div class="relative">
                            <input v-model="form.password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'" required autocomplete="new-password"
                                   class="app-input !pr-12"
                                   placeholder="Nhập lại mật khẩu mới...">

                            <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-primary-500 transition-colors focus:outline-none">
                                <svg v-if="!showPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password_confirmation" class="text-[10px] font-bold text-rose-500 uppercase mt-1 px-1">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <button :disabled="form.processing" class="btn-primary !w-full !py-4 !mt-4 !uppercase !tracking-widest disabled:opacity-50">
                        {{ form.processing ? 'Đang lưu hệ thống...' : 'Cập nhật mật khẩu' }}
                    </button>

                </form>

            </div>
        </div>
    </div>
</template>
