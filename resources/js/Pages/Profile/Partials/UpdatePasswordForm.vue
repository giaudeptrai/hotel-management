<script setup>
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="admin-index-title !text-2xl">
                Đổi Mật Khẩu
            </h2>

            <p class="mt-1 text-desc">
                Sử dụng mật khẩu mạnh để đảm bảo an toàn cho tài khoản của bạn.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div class="rounded-[1.75rem] bg-slate-50 p-5 dark:bg-slate-800/60">
                <label for="current_password" class="admin-index-subtitle px-1">Mật Khẩu Hiện Tại</label>

                <input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="form-input-pms mt-2"
                    autocomplete="current-password"
                />

                <InputError
                    :message="form.errors.current_password"
                    class="mt-2"
                />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-[1.75rem] bg-slate-50 p-5 dark:bg-slate-800/60">
                    <label for="password" class="admin-index-subtitle px-1">Mật Khẩu Mới</label>

                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="form-input-pms mt-2"
                        autocomplete="new-password"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="rounded-[1.75rem] bg-slate-50 p-5 dark:bg-slate-800/60">
                    <label for="password_confirmation" class="admin-index-subtitle px-1">Xác Nhận Mật Khẩu</label>

                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="form-input-pms mt-2"
                        autocomplete="new-password"
                    />

                    <InputError
                        :message="form.errors.password_confirmation"
                        class="mt-2"
                    />
                </div>
            </div>

            <div class="rounded-[1.75rem] border border-slate-100 bg-white p-5 dark:border-dark-border dark:bg-dark-card">
                <p class="admin-index-subtitle mb-2">Lưu ý bảo mật</p>
                <p class="text-desc leading-7">
                    Mật khẩu nên dài, khó đoán và không nên trùng với mật khẩu ở các dịch vụ khác.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="submit" :disabled="form.processing" class="btn-primary !py-3 !px-6">Cập Nhật Mật Khẩu</button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-muted-text"
                    >
                        Đã cập nhật thành công.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
