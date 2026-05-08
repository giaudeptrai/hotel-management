<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const form = useForm({
    full_name: '',
    phone: '',
    cccd_number: '',
    email: '',
    birthday: '',
    gender: 'male',
    address: '',
});

const sanitizePhone = (value) => value.replace(/\D/g, '');

const submit = () => {
    form.post(route('admin.customers.store'));
};
</script>

<template>
    <Head title="Thêm Khách Hàng" />
    <AdminLayout>
        <div class="admin-form-page">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.3em] mb-2 block">Quản lý lưu trú</span>
                    <h2 class="text-3xl font-black text-main-text dark:text-white tracking-tight italic">Thêm Khách Hàng</h2>
                </div>
                <Link :href="route('admin.customers.index')" class="admin-form-back-link">
                    Quay lại danh sách
                </Link>
            </div>

            <form @submit.prevent="submit" class="admin-form-card">
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Họ và Tên <span class="text-rose-500">*</span></label>
                        <input v-model="form.full_name" type="text" class="form-input-pms" placeholder="Nguyễn Văn A">
                        <p v-if="form.errors.full_name" class="text-[10px] text-rose-500 font-bold uppercase mt-1 ml-1 italic">{{ form.errors.full_name }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Số điện thoại <span class="text-rose-500">*</span></label>
                        <input v-model="form.phone" type="text" inputmode="numeric" pattern="[0-9]*" class="form-input-pms" placeholder="090x xxx xxx" @input="form.phone = sanitizePhone(form.phone)">
                        <p v-if="form.errors.phone" class="text-[10px] text-rose-500 font-bold uppercase mt-1 ml-1 italic">{{ form.errors.phone }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">CCCD / CMND / Passport</label>
                        <input v-model="form.cccd_number" type="text" class="form-input-pms" placeholder="Nhập số định danh">
                        <p v-if="form.errors.cccd_number" class="text-[10px] text-rose-500 font-bold uppercase mt-1 ml-1 italic">{{ form.errors.cccd_number }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Email</label>
                        <input v-model="form.email" type="email" class="form-input-pms" placeholder="khachhang@example.com">
                        <p v-if="form.errors.email" class="text-[10px] text-rose-500 font-bold uppercase mt-1 ml-1 italic">{{ form.errors.email }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Ngày sinh</label>
                        <input v-model="form.birthday" type="date" class="form-input-pms">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Giới tính</label>
                        <select v-model="form.gender" class="form-input-pms appearance-none">
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Địa chỉ thường trú</label>
                    <textarea v-model="form.address" rows="3" class="form-input-pms resize-none" placeholder="Số nhà, đường, phường/xã..."></textarea>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full bg-primary-500 hover:bg-primary-600 text-white !py-5 !rounded-2xl shadow-lg shadow-primary-500/20 font-black uppercase tracking-widest text-sm transition-all active:scale-[0.98]">
                    {{ form.processing ? 'Đang xử lý...' : 'Lưu hồ sơ khách hàng' }}
                </button>
            </form>
        </div>
    </AdminLayout>
</template>
