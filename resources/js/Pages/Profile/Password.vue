<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Đổi mật khẩu" />

    <AdminLayout>
        <div class="admin-form-page max-w-6xl">
            <section class="app-card overflow-hidden !p-0">
                <div class="grid gap-0 lg:grid-cols-[1.15fr_0.85fr]">
                    <div class="p-8 md:p-10">
                        <span class="admin-index-subtitle">Bảo mật tài khoản</span>
                        <h2 class="admin-index-title mt-3 !text-4xl">Đổi Mật Khẩu</h2>
                        <p class="text-desc mt-4 max-w-2xl leading-7">
                            Cập nhật mật khẩu mới để tăng an toàn cho tài khoản. Nên chọn mật khẩu khác với các hệ thống bạn đang dùng.
                        </p>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <Link :href="route('profile.edit')" class="admin-index-secondary-btn">
                                Quay lại hồ sơ
                            </Link>
                            <span class="rounded-full bg-slate-100 px-4 py-2 text-[11px] font-black uppercase tracking-[0.22em] text-slate-600 dark:bg-dark-bg dark:text-slate-300">
                                Mật khẩu mạnh hơn, tài khoản an toàn hơn
                            </span>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50 p-8 dark:border-dark-border dark:bg-dark-bg md:p-10 lg:border-t-0 lg:border-l">
                        <div class="rounded-[1.75rem] bg-white p-5 shadow-sm dark:bg-dark-card">
                            <p class="admin-index-subtitle mb-2">Mẹo nhanh</p>
                            <div class="space-y-4 text-sm text-desc leading-7">
                                <p>Mật khẩu nên có chữ hoa, chữ thường, số và ký tự đặc biệt.</p>
                                <p>Không nên dùng lại mật khẩu cũ hoặc mật khẩu dễ đoán.</p>
                                <p>Sau khi đổi xong, nhớ đăng nhập lại ở các thiết bị khác nếu cần.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="admin-form-card !space-y-0">
                <UpdatePasswordForm />
            </div>

            <div class="admin-form-card !space-y-0">
                <DeleteUserForm />
            </div>
        </div>
    </AdminLayout>
</template>
