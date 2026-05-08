<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ amenity: Object });

const form = useForm({
    _method: 'PUT',
    name: props.amenity.name,
    icon_file: null,
});

const handleFileSelect = (e) => {
    form.icon_file = e.target.files[0];
};

const submit = () => {
    form.post(route('admin.amenities.update', props.amenity.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Sửa Tiện ích" />
    <AdminLayout>
        <div class="admin-form-page">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="admin-index-subtitle">Cấu hình vận hành</span>
                    <h2 class="admin-index-title">Sửa: {{ amenity.name }}</h2>
                </div>
                <Link :href="route('admin.amenities.index')" class="admin-form-back-link italic">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    Hủy bỏ
                </Link>
            </div>

            <form @submit.prevent="submit" class="admin-form-card">

                <div class="space-y-4">
                    <h3 class="text-[11px] font-black text-primary-500 uppercase tracking-widest px-1 italic border-l-4 border-primary-500 pl-3">Icon hiện hành</h3>
                    <div class="flex items-center gap-6 p-6 bg-slate-50 dark:bg-dark-bg rounded-[2rem] border border-slate-100 dark:border-dark-border">
                        <div class="w-24 h-24 rounded-[1.5rem] overflow-hidden border-4 border-white dark:border-dark-card shadow-sm shrink-0">
                            <img :src="amenity.icon_url" class="w-full h-full object-cover">
                        </div>
                        <div class="space-y-1">
                            <p class="text-[10px] font-black uppercase text-slate-400 italic">Trạng thái: Đang hiển thị</p>
                            <p class="text-[11px] text-main-text dark:text-white font-bold italic uppercase tracking-tighter">{{ amenity.name }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 relative z-10">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Tên hiển thị mới</label>
                    <input v-model="form.name" type="text" class="form-input-pms">
                </div>

                <div class="space-y-4 pt-6 border-t border-slate-100 dark:border-dark-border relative z-10">
                    <label class="text-[11px] font-black text-primary-500 uppercase tracking-widest px-1 italic">Thay đổi Icon nhận diện</label>
                    <div class="relative group cursor-pointer">
                        <input type="file" @change="handleFileSelect" class="absolute inset-0 opacity-0 cursor-pointer z-20">
                        <div class="border-2 border-dashed border-slate-200 dark:border-dark-border p-10 rounded-[2rem] flex flex-col items-center justify-center bg-slate-50 dark:bg-dark-bg group-hover:bg-white dark:group-hover:bg-dark-card group-hover:border-primary-500 transition-all">

                            <svg class="w-8 h-8 text-slate-300 mb-3 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                            </svg>
                            <span class="text-[10px] font-black uppercase text-slate-400 group-hover:text-primary-500 transition-colors italic">Nhấp để chọn tệp icon mới</span>
                            <span v-if="form.icon_file" class="text-xs font-black text-primary-500 mt-2 italic uppercase">✓ {{ form.icon_file.name }}</span>
                        </div>
                    </div>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full btn-primary !py-6 !rounded-[2rem] font-black uppercase tracking-[0.2em] text-sm">
                    {{ form.processing ? 'Hệ thống đang cập nhật...' : 'Xác nhận thay đổi tiện ích' }}
                </button>
            </form>
        </div>
    </AdminLayout>
</template>
