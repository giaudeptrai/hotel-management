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
        <div class="max-w-2xl mx-auto space-y-8">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="text-[10px] font-black text-amber-500 uppercase tracking-[0.3em] mb-2 block">Hiệu chỉnh đẳng cấp</span>
                    <h2 class="text-3xl font-black text-main-text dark:text-white tracking-tight italic">Sửa Hạng Phòng</h2>
                    <p class="text-[10px] text-muted-text mt-1">Đang chỉnh sửa: {{ props.category.name }}</p>
                </div>
                <Link :href="route('admin.room-categories.index')" class="text-sm font-bold text-muted-text hover:text-primary-500 transition-colors flex items-center gap-2 mb-1">
                    Hủy bỏ
                </Link>
            </div>

            <form @submit.prevent="submit" class="app-card !p-8 md:!p-10 !rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-dark-border space-y-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>

                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Tên hạng phòng</label>
                    <input v-model="form.name" type="text" class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-amber-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-amber-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-bold transition-all outline-none">
                    <p v-if="form.errors.name" class="text-rose-500 text-xs font-bold px-1">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Mô tả đặc quyền</label>
                    <textarea v-model="form.description" rows="5" class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-amber-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-amber-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-medium transition-all outline-none resize-none"></textarea>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full bg-amber-500 hover:bg-amber-600 text-white !py-5 !rounded-2xl shadow-lg shadow-amber-500/20 font-black uppercase tracking-widest text-sm transition-all">
                    {{ form.processing ? 'Đang lưu...' : 'Cập nhật hạng phòng' }}
                </button>
            </form>
        </div>
    </AdminLayout>
</template>