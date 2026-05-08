<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    staff: Object,
    roles: Array
});

const form = useForm({
    full_name: props.staff.full_name,
    phone: props.staff.phone || '',
    cccd: props.staff.cccd || '',
    email: props.staff.user.email,
    password: '',
    password_confirmation: '',
    role_id: props.staff.user.role_id,
    is_active: props.staff.is_active ? true : false
});

const sanitizePhone = (value) => value.replace(/\D/g, '');

const submit = () => {
    // 🎯 Đã fix thành admin.staffs.update
    form.put(route('admin.staffs.update', props.staff.id));
};
</script>

<template>
    <Head :title="`Sửa HS: ${staff.staff_code}`" />
    <AdminLayout>
        <div class="max-w-4xl mx-auto space-y-6 pb-12 animate-in fade-in duration-500">

            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="admin-index-subtitle mb-2 block">Hệ thống nhân sự</span>
                    <h2 class="admin-index-title !text-3xl">Cập nhật: {{ staff.staff_code }}</h2>
                </div>
                <Link :href="route('admin.staffs.index')" class="text-desc hover:text-primary-500 font-bold transition-colors mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Quay lại
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-8 mt-8">
                <div class="app-card space-y-8">
                    <div class="flex justify-between items-center border-b border-slate-100 dark:border-dark-border pb-4">
                        <h3 class="admin-index-subtitle">1. Hồ Sơ Cá Nhân</h3>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <span class="admin-index-subtitle !tracking-widest" :class="form.is_active ? 'text-emerald-500' : 'text-rose-500'">
                                {{ form.is_active ? 'Đang làm việc' : 'Đã nghỉ việc' }}
                            </span>
                            <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded border-slate-300 text-primary-500 focus:ring-primary-500/20">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2 md:col-span-2">
                            <label class="admin-index-subtitle px-1">Họ & Tên <span class="text-rose-500">*</span></label>
                            <input type="text" v-model="form.full_name" class="form-input-pms" required>
                            <p v-if="form.errors.full_name" class="text-rose-500 text-xs font-bold mt-1 px-1">{{ form.errors.full_name }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="admin-index-subtitle px-1">Số điện thoại</label>
                            <input type="text" v-model="form.phone" inputmode="numeric" pattern="[0-9]*" class="form-input-pms input-number-clean" @input="form.phone = sanitizePhone(form.phone)">
                            <p v-if="form.errors.phone" class="text-rose-500 text-xs font-bold mt-1 px-1">{{ form.errors.phone }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="admin-index-subtitle px-1">Số CCCD / CMND</label>
                            <input type="text" v-model="form.cccd" class="form-input-pms">
                            <p v-if="form.errors.cccd" class="text-rose-500 text-xs font-bold mt-1 px-1">{{ form.errors.cccd }}</p>
                        </div>
                    </div>
                </div>

                <div class="app-card space-y-8">
                    <h3 class="admin-index-subtitle border-b border-slate-100 dark:border-dark-border pb-4">2. Tài Khoản Đăng Nhập & Quyền</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="admin-index-subtitle px-1">Email đăng nhập <span class="text-rose-500">*</span></label>
                            <input type="email" v-model="form.email" class="form-input-pms" required>
                            <p v-if="form.errors.email" class="text-rose-500 text-xs font-bold mt-1 px-1">{{ form.errors.email }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="admin-index-subtitle px-1">Vai trò / Chức vụ <span class="text-rose-500">*</span></label>
                            <select v-model="form.role_id" class="form-input-pms cursor-pointer" required>
                                <option value="" disabled>-- Chọn quyền hạn --</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                            </select>
                            <p v-if="form.errors.role_id" class="text-rose-500 text-xs font-bold mt-1 px-1">{{ form.errors.role_id }}</p>
                        </div>
                        <div class="space-y-2 md:col-span-2">
                            <div class="p-4 bg-slate-50 dark:bg-dark-bg rounded-xl border border-slate-100 dark:border-dark-border">
                                <p class="text-xs font-bold text-amber-600 mb-4 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    Bỏ trống nếu không muốn thay đổi mật khẩu
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="admin-index-subtitle px-1">Mật khẩu mới</label>
                                        <input type="password" v-model="form.password" class="form-input-pms" placeholder="Nhập mật khẩu mới...">
                                        <p v-if="form.errors.password" class="text-rose-500 text-xs font-bold mt-1 px-1">{{ form.errors.password }}</p>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="admin-index-subtitle px-1">Xác nhận mật khẩu</label>
                                        <input type="password" v-model="form.password_confirmation" class="form-input-pms" placeholder="Nhập lại mật khẩu mới...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4">
                    <button type="submit" :disabled="form.processing" class="btn-primary w-full sm:w-auto !bg-amber-500 hover:!bg-amber-600 shadow-amber-500/20">
                        {{ form.processing ? 'Đang cập nhật...' : 'Cập nhật Hệ Thống' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
