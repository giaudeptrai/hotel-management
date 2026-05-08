<script setup>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Đổi mật khẩu" />

    <ClientLayout>
        <section class="client-bookings-shell">
            <section class="app-card overflow-hidden !p-0">
                <div class="grid gap-0 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="p-8 md:p-10">
                        <span class="admin-index-subtitle">Bảo mật tài khoản</span>
                        <h1 class="admin-index-title mt-3 !text-4xl">Đổi Mật Khẩu</h1>
                        <p class="text-desc mt-4 max-w-2xl leading-7">
                            Đặt mật khẩu mới để bảo vệ tài khoản của bạn khi dùng các dịch vụ đặt phòng online.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <Link :href="route('profile.edit')" class="admin-index-secondary-btn">
                                Quay lại hồ sơ
                            </Link>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50 p-8 dark:border-dark-border dark:bg-dark-bg md:p-10 lg:border-t-0 lg:border-l">
                        <div class="rounded-[1.75rem] bg-white p-5 shadow-sm dark:bg-dark-card">
                            <p class="admin-index-subtitle mb-2">Gợi ý nhanh</p>
                            <p class="text-desc leading-7">
                                Nên chọn mật khẩu có chữ hoa, chữ thường, số và ký tự đặc biệt để tăng mức an toàn.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="admin-form-card !space-y-0">
                <UpdatePasswordForm />
            </div>
        </section>
    </ClientLayout>
</template>
