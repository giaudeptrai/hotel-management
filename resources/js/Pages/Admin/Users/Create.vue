<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    roles: {
        type: Array,
        default: () => [],
    },
});

const customerRoleSlug = computed(() => {
    const customerRole = props.roles.find((role) => role.slug === 'customer');
    return customerRole?.slug || props.roles[0]?.slug || 'customer';
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: customerRoleSlug.value,
    is_active: true,
    phone: '',
    cccd_number: '',
    address: '',
    gender: 'male'
});

const sanitizePhone = (value) => value.replace(/\D/g, '');

const submit = () => {
    form.post(route('admin.users.store'), {
        preserveScroll: true,
    });
};

const getRoleLabel = (role) => role?.name || role?.slug;
</script>

<template>
    <Head title="Tạo Tài Khoản" />
    <AdminLayout>
        <div class="admin-form-page space-y-6">
            <div class="flex flex-col gap-4 px-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="admin-index-subtitle">Quản lý tài khoản</span>
                    <h2 class="text-3xl font-black italic tracking-tight text-main-text dark:text-white">Tạo tài khoản mới</h2>
                    <p class="text-[11px] font-bold text-muted-text mt-1">Tài khoản khách hàng sẽ tự động tạo hồ sơ customer đi kèm.</p>
                </div>
                <Link :href="route('admin.users.index')" class="admin-form-back-link">Quay lại danh sách</Link>
            </div>

            <form @submit.prevent="submit" class="admin-form-card !space-y-8">
                <div class="absolute top-0 right-0 h-32 w-32 -mr-16 -mt-16 rounded-full bg-primary-500/5 blur-3xl"></div>

                <div v-if="form.hasErrors" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-bold text-rose-600 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                    Vui lòng kiểm tra lại thông tin, một số trường đang chưa hợp lệ.
                </div>

                <section class="space-y-5">
                    <div class="flex items-center gap-2">
                        <div class="h-4 w-1.5 rounded-full bg-primary-500"></div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-primary-500">Thông tin đăng nhập</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-muted-text px-1">Tên tài khoản</label>
                            <input v-model="form.name" type="text" class="form-input-pms" placeholder="Nhập tên hiển thị...">
                            <p v-if="form.errors.name" class="text-xs font-bold text-rose-500 px-1">{{ form.errors.name }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-muted-text px-1">Email đăng nhập</label>
                            <input v-model="form.email" type="email" class="form-input-pms" placeholder="example@dasher.com">
                            <p v-if="form.errors.email" class="text-xs font-bold text-rose-500 px-1">{{ form.errors.email }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-muted-text px-1">Mật khẩu</label>
                            <input v-model="form.password" type="password" class="form-input-pms" placeholder="Ít nhất 8 ký tự">
                            <p v-if="form.errors.password" class="text-xs font-bold text-rose-500 px-1">{{ form.errors.password }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-muted-text px-1">Xác nhận mật khẩu</label>
                            <input v-model="form.password_confirmation" type="password" class="form-input-pms" placeholder="Nhập lại mật khẩu">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-muted-text px-1">Vai trò</label>
                            <select v-model="form.role" class="form-input-pms appearance-none">
                                <option v-for="roleOption in props.roles" :key="roleOption.id" :value="roleOption.slug">
                                    {{ getRoleLabel(roleOption) }}
                                </option>
                            </select>
                            <p v-if="form.errors.role" class="text-xs font-bold text-rose-500 px-1">{{ form.errors.role }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black uppercase tracking-widest text-muted-text px-1">Trạng thái</label>
                            <select v-model="form.is_active" class="form-input-pms appearance-none">
                                <option :value="true">Đang hoạt động</option>
                                <option :value="false">Tạm khóa</option>
                            </select>
                            <p v-if="form.errors.is_active" class="text-xs font-bold text-rose-500 px-1">{{ form.errors.is_active }}</p>
                        </div>
                    </div>
                </section>

                <transition
                    enter-active-class="transition duration-500 ease-out"
                    enter-from-class="transform scale-95 opacity-0 -translate-y-4"
                    enter-to-class="transform scale-100 opacity-100 translate-y-0"
                >
                    <section v-if="form.role === 'customer'" class="space-y-5 rounded-[2rem] border border-primary-500/10 bg-primary-500/5 p-6">
                        <div class="flex items-center gap-2">
                            <div class="h-4 w-1.5 rounded-full bg-primary-500"></div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-primary-500">Hồ sơ khách hàng</h3>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-text">Số điện thoại</label>
                                <input v-model="form.phone" type="text" inputmode="numeric" pattern="[0-9]*" class="form-input-pms !bg-white dark:!bg-dark-card" placeholder="090x xxx xxx" @input="form.phone = sanitizePhone(form.phone)">
                                <p v-if="form.errors.phone" class="text-[10px] font-bold text-rose-500">{{ form.errors.phone }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-text">Số CCCD / CMND</label>
                                <input v-model="form.cccd_number" type="text" class="form-input-pms !bg-white dark:!bg-dark-card" placeholder="Nhập số định danh">
                                <p v-if="form.errors.cccd_number" class="text-[10px] font-bold text-rose-500">{{ form.errors.cccd_number }}</p>
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-text">Địa chỉ thường trú</label>
                                <textarea v-model="form.address" rows="2" class="form-input-pms !bg-white dark:!bg-dark-card resize-none" placeholder="Địa chỉ khách hàng..."></textarea>
                                <p v-if="form.errors.address" class="text-[10px] font-bold text-rose-500">{{ form.errors.address }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-text">Giới tính</label>
                                <select v-model="form.gender" class="form-input-pms !bg-white dark:!bg-dark-card appearance-none">
                                    <option value="male">Nam</option>
                                    <option value="female">Nữ</option>
                                    <option value="other">Khác</option>
                                </select>
                                <p v-if="form.errors.gender" class="text-[10px] font-bold text-rose-500">{{ form.errors.gender }}</p>
                            </div>
                        </div>
                    </section>
                </transition>

                <button type="submit" :disabled="form.processing" class="w-full rounded-2xl bg-primary-500 !py-5 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-primary-500/20 transition-all hover:bg-primary-600 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60">
                    {{ form.processing ? 'Đang tạo tài khoản...' : 'Tạo tài khoản' }}
                </button>
            </form>
        </div>
    </AdminLayout>
</template>
