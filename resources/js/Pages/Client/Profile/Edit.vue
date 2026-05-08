<script setup>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
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
</script>

<template>
    <Head title="Hồ sơ của tôi" />

    <ClientLayout>
        <section class="client-bookings-shell">
            <section class="app-card overflow-hidden !p-0">
                <div class="grid gap-0 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="p-8 md:p-10">
                        <span class="admin-index-subtitle">Tài khoản khách hàng</span>
                        <h1 class="admin-index-title mt-3 !text-4xl">Hồ Sơ Của Tôi</h1>
                        <p class="text-desc mt-4 max-w-2xl leading-7">
                            Cập nhật thông tin cá nhân để dùng lại khi đặt phòng và nhận thông báo từ khách sạn.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <Link :href="route('profile.password.edit')" class="admin-index-secondary-btn">
                                Đổi mật khẩu
                            </Link>
                            <Link :href="route('client.bookings.index')" class="admin-index-secondary-btn">
                                Xem đặt phòng của tôi
                            </Link>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50 p-8 dark:border-dark-border dark:bg-dark-bg md:p-10 lg:border-t-0 lg:border-l">
                        <div class="rounded-[1.75rem] bg-white p-5 shadow-sm dark:bg-dark-card">
                            <p class="admin-index-subtitle mb-2">Mục đích sử dụng</p>
                            <p class="text-desc leading-7">
                                Thông tin này sẽ được điền sẵn trong form đặt phòng online để bạn không phải nhập lại nhiều lần.
                            </p>
                        </div>

                        <div class="mt-4 rounded-[1.75rem] bg-white p-5 shadow-sm dark:bg-dark-card">
                            <p class="admin-index-subtitle mb-2">Lưu ý</p>
                            <p class="text-desc leading-7">
                                Nếu số điện thoại hoặc CCCD thay đổi, hãy cập nhật ngay để bộ phận lễ tân liên hệ chính xác.
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
        </section>
    </ClientLayout>
</template>
