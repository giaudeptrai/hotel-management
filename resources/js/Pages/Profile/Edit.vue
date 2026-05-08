<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

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

const customerStatus = computed(() => {
    if (!props.isCustomerProfile) {
        return 'Tài khoản nhân sự / nội bộ';
    }

    return props.customerProfile?.phone ? 'Hồ sơ khách hàng đã sẵn sàng cho đặt phòng' : 'Hồ sơ khách hàng chưa hoàn chỉnh';
});
</script>

<template>
    <Head title="Hồ sơ tài khoản" />

    <AdminLayout>
        <div class="admin-form-page max-w-6xl">
            <section class="app-card overflow-hidden !p-0">
                <div class="grid gap-0 lg:grid-cols-[1.15fr_0.85fr]">
                    <div class="p-8 md:p-10">
                        <span class="admin-index-subtitle">Tài khoản cá nhân</span>
                        <h2 class="admin-index-title mt-3 !text-4xl">Hồ Sơ Tài Khoản</h2>
                        <p class="text-desc mt-4 max-w-2xl leading-7">
                            Quản lý thông tin hồ sơ cá nhân.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <Link :href="route('profile.password.edit')" class="admin-index-secondary-btn">
                                Đến trang đổi mật khẩu
                            </Link>
                            <span class="rounded-full bg-slate-100 px-4 py-2 text-[11px] font-black uppercase tracking-[0.22em] text-slate-600 dark:bg-dark-bg dark:text-slate-300">
                                {{ customerStatus }}
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50 p-8 dark:border-dark-border dark:bg-dark-bg md:p-10 lg:border-t-0 lg:border-l">
                        <div class="flex items-start gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-primary-500 text-xl font-black text-white shadow-sm">
                                {{ (user?.name || 'U').slice(0, 1).toUpperCase() }}
                            </div>
                            <div class="min-w-0">
                                <p class="admin-index-subtitle mb-2">Người dùng hiện tại</p>
                                <h3 class="text-title text-2xl truncate">{{ user?.name }}</h3>
                                <p class="text-desc mt-1 truncate">{{ user?.email }}</p>
                            </div>
                        </div>

                        <div class="mt-8 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-3xl bg-white p-4 shadow-sm dark:bg-dark-card">
                                <p class="admin-index-subtitle mb-2">Trạng thái xác minh</p>
                                <p class="text-sm font-black text-main-text dark:text-white">{{ mustVerifyEmail && user?.email_verified_at === null ? 'Chưa xác minh email' : 'Email đã sẵn sàng' }}</p>
                            </div>
                            <div class="rounded-3xl bg-white p-4 shadow-sm dark:bg-dark-card">
                                <p class="admin-index-subtitle mb-2">Dùng cho đặt phòng</p>
                                <p class="text-sm font-black text-main-text dark:text-white">{{ isCustomerProfile ? 'Có' : 'Không' }}</p>
                            </div>
                        </div>

                        <div class="mt-6 rounded-[1.75rem] border border-slate-100 bg-white p-5 dark:border-dark-border dark:bg-dark-card">
                            <p class="admin-index-subtitle mb-2">Ghi chú</p>
                            <p class="text-desc leading-7">
                                Những thông tin như SĐT, CCCD, ngày sinh, giới tính và địa chỉ sẽ được dùng lại cho các đơn đặt phòng sau.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="admin-form-card !space-y-0">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    :is-customer-profile="isCustomerProfile"
                    :customer-profile="customerProfile"
                />
            </div>
        </div>
    </AdminLayout>
</template>
