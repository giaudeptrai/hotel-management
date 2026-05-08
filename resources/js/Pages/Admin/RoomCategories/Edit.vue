<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

// SỬA TẠI ĐÂY: Đổi 'categories' thành 'category' (số ít)
const props = defineProps({ category: Object });

const form = useForm({
    // SỬA TẠI ĐÂY: Truy cập qua props.category
    name: props.category?.name || '',
    description: props.category?.description || '',
});

const submit = () => {
    // SỬA TẠI ĐÂY: Sử dụng props.category.id
    form.patch(route('admin.room-categories.update', props.category.id));
};
</script>

<template>
    <Head title="Cập nhật Hạng Phòng" />
    <AdminLayout>
        <div class="admin-form-page">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="admin-index-subtitle">Hiệu chỉnh đẳng cấp</span>
                    <h2 class="admin-index-title">Sửa Hạng Phòng</h2>
                    <p class="text-[10px] text-muted-text mt-1">Đang chỉnh sửa: {{ props.category.name }}</p>
                </div>
                <Link :href="route('admin.room-categories.index')" class="admin-form-back-link">
                    Hủy bỏ
                </Link>
            </div>

            <form @submit.prevent="submit" class="admin-form-card">
                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Tên hạng phòng</label>
                    <input v-model="form.name" type="text" class="form-input-pms">
                    <p v-if="form.errors.name" class="text-rose-500 text-xs font-bold px-1">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Mô tả đặc quyền</label>
                    <textarea v-model="form.description" rows="5" class="form-input-pms resize-none"></textarea>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full btn-primary !py-5 !rounded-2xl font-black uppercase tracking-widest text-sm">
                    {{ form.processing ? 'Đang lưu...' : 'Cập nhật hạng phòng' }}
                </button>
            </form>
        </div>
    </AdminLayout>
</template>
