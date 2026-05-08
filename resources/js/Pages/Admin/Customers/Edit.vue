<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    customer: Object,
});

const form = useForm({
    full_name: props.customer.full_name,
    phone: props.customer.phone,
    cccd_number: props.customer.cccd_number,
    email: props.customer.email,
    birthday: props.customer.birthday,
    gender: props.customer.gender,
    address: props.customer.address,
});

const sanitizePhone = (value) => value.replace(/\D/g, '');

const submit = () => {
    form.put(route('admin.customers.update', props.customer.id));
};
</script>

<template>
    <Head title="Sửa Khách Hàng" />
    <AdminLayout>
        <div class="admin-form-page">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="text-[10px] font-black text-amber-500 uppercase tracking-[0.3em] mb-2 block">Hiệu chỉnh thông tin</span>
                    <h2 class="text-3xl font-black text-main-text dark:text-white tracking-tight italic">Hồ Sơ Khách Hàng</h2>
                </div>
                <Link :href="route('admin.customers.index')" class="admin-form-back-link">
                    Quay lại danh sách
                </Link>
            </div>

            <form @submit.prevent="submit" class="admin-form-card">
                <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Họ và Tên</label>
                        <input v-model="form.full_name" type="text" class="form-input-pms form-input-pms-amber">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Số điện thoại</label>
                        <input v-model="form.phone" type="text" inputmode="numeric" pattern="[0-9]*" class="form-input-pms form-input-pms-amber" @input="form.phone = sanitizePhone(form.phone)">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">CCCD / CMND</label>
                        <input v-model="form.cccd_number" type="text" class="form-input-pms form-input-pms-amber">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Email</label>
                        <input v-model="form.email" type="email" class="form-input-pms form-input-pms-amber">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Ngày sinh</label>
                        <input v-model="form.birthday" type="date" class="form-input-pms form-input-pms-amber">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Giới tính</label>
                        <select v-model="form.gender" class="form-input-pms form-input-pms-amber appearance-none">
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Địa chỉ</label>
                    <textarea v-model="form.address" rows="3" class="form-input-pms form-input-pms-amber resize-none"></textarea>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full bg-amber-500 hover:bg-amber-600 text-white !py-5 !rounded-2xl shadow-lg shadow-amber-500/20 font-black uppercase tracking-widest text-sm transition-all active:scale-[0.98]">
                    {{ form.processing ? 'Đang cập nhật...' : 'Cập nhật hồ sơ' }}
                </button>
            </form>
        </div>
    </AdminLayout>
</template>
