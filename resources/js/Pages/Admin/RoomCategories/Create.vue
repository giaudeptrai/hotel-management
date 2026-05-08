<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    description: '',
});

const submit = () => {
    form.post(route('admin.room-categories.store'));
};
</script>

<template>
    <Head title="Thêm Hạng Phòng" />
    <AdminLayout>
        <div class="admin-form-page">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="admin-index-subtitle">Cấu hình đẳng cấp</span>
                    <h2 class="admin-index-title">Thêm Hạng Phòng</h2>
                </div>
                <Link :href="route('admin.room-categories.index')" class="admin-form-back-link">
                    Quay lại danh sách
                </Link>
            </div>

            <form @submit.prevent="submit" class="admin-form-card">
                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Tên hạng phòng (VD: VIP, Suite, Deluxe...)</label>
                    <input v-model="form.name" type="text" placeholder="Nhập tên hạng phòng..." class="form-input-pms">
                    <p v-if="form.errors.name" class="text-rose-500 text-xs font-bold px-1">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Mô tả đặc quyền & dịch vụ</label>
                    <textarea v-model="form.description" rows="5" placeholder="Mô tả các tiện ích đi kèm của hạng phòng này..." class="form-input-pms resize-none"></textarea>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full btn-primary !py-5 !rounded-2xl font-black uppercase tracking-widest text-sm">
                    {{ form.processing ? 'Đang khởi tạo...' : 'Khởi tạo hạng phòng' }}
                </button>
            </form>
        </div>
    </AdminLayout>
</template>
