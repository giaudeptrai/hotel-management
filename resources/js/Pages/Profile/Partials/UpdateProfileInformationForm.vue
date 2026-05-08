<script setup>
import InputError from '@/Components/InputError.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    isCustomerProfile: {
        type: Boolean,
        default: false,
    },
    customerProfile: {
        type: Object,
        default: null,
    },
});

const user = usePage().props.auth.user;

const sanitizePhone = (value) => value.replace(/\D/g, '');

const form = useForm({
    name: user.name,
    email: user.email,
    phone: props.customerProfile?.phone || '',
    cccd_number: props.customerProfile?.cccd_number || '',
    birthday: props.customerProfile?.birthday || '',
    gender: props.customerProfile?.gender || 'other',
    address: props.customerProfile?.address || '',
});
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="admin-index-title !text-2xl">
                Thông Tin Hồ Sơ
            </h2>

            <p class="mt-1 text-desc">
                Cập nhật thông tin cá nhân và địa chỉ email đăng nhập của bạn.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-[1.75rem] bg-slate-50 p-5 dark:bg-slate-800/60">
                    <label for="name" class="admin-index-subtitle px-1">Họ Và Tên</label>

                    <input
                        id="name"
                        type="text"
                        class="form-input-pms mt-2"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                    />

                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="rounded-[1.75rem] bg-slate-50 p-5 dark:bg-slate-800/60">
                    <label for="email" class="admin-index-subtitle px-1">Email Đăng Nhập</label>

                    <input
                        id="email"
                        type="email"
                        class="form-input-pms mt-2"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
            </div>

            <template v-if="isCustomerProfile">
                <div class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm dark:border-dark-border dark:bg-dark-card">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <span class="admin-index-subtitle block">Thông Tin Khách Hàng</span>
                        </div>
                        <span class="rounded-full bg-primary-50 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-primary-600 dark:bg-primary-500/10">Dùng cho booking</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label for="phone" class="admin-index-subtitle px-1">Số Điện Thoại</label>

                            <input
                                id="phone"
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                class="form-input-pms mt-2"
                                v-model="form.phone"
                                @input="form.phone = sanitizePhone(form.phone)"
                                required
                                autocomplete="tel"
                            />

                            <InputError class="mt-2" :message="form.errors.phone" />
                        </div>

                        <div>
                            <label for="cccd_number" class="admin-index-subtitle px-1">CCCD / CMND</label>

                            <input
                                id="cccd_number"
                                type="text"
                                class="form-input-pms mt-2"
                                v-model="form.cccd_number"
                                autocomplete="off"
                            />

                            <InputError class="mt-2" :message="form.errors.cccd_number" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="birthday" class="admin-index-subtitle px-1">Ngày Sinh</label>

                                <input
                                    id="birthday"
                                    type="date"
                                    class="form-input-pms mt-2"
                                    v-model="form.birthday"
                                />

                                <InputError class="mt-2" :message="form.errors.birthday" />
                            </div>

                            <div>
                                <label for="gender" class="admin-index-subtitle px-1">Giới Tính</label>

                                <select
                                    id="gender"
                                    class="form-input-pms mt-2"
                                    v-model="form.gender"
                                >
                                    <option value="male">Nam</option>
                                    <option value="female">Nữ</option>
                                    <option value="other">Khác</option>
                                </select>

                                <InputError class="mt-2" :message="form.errors.gender" />
                            </div>
                        </div>

                        <div>
                            <label for="address" class="admin-index-subtitle px-1">Địa Chỉ</label>

                            <textarea
                                id="address"
                                class="form-input-pms mt-2"
                                v-model="form.address"
                                rows="3"
                            />

                            <InputError class="mt-2" :message="form.errors.address" />
                        </div>
                    </div>
                </div>
            </template>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="rounded-[1.75rem] border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/20 dark:bg-amber-500/10">
                <p class="admin-index-subtitle mb-2 text-amber-600 dark:text-amber-300">Xác minh email</p>
                <p class="text-sm text-main-text dark:text-white leading-7">
                    Email của bạn chưa được xác minh.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="font-black text-primary-500 underline hover:text-primary-600"
                    >
                        Nhấn vào đây để gửi lại email xác minh.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-3 text-sm font-medium text-emerald-600"
                >
                    Liên kết xác minh mới đã được gửi đến email của bạn.
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="submit" :disabled="form.processing" class="btn-primary !py-3 !px-6">
                    Lưu Thay Đổi
                </button>

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
                        Đã lưu thành công.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
