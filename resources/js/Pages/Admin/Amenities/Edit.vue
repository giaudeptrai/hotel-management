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
        <div class="max-w-2xl mx-auto space-y-10 pb-20">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.3em] mb-2 block italic tracking-tighter">Cấu hình vận hành</span>
                    <h2 class="text-3xl font-black text-main-text dark:text-white tracking-tight italic uppercase">Sửa: {{ amenity.name }}</h2>
                </div>
                <Link :href="route('admin.amenities.index')" class="text-sm font-bold text-muted-text hover:text-rose-500 transition-colors flex items-center gap-2 mb-1 italic">
                    ✕ Hủy bỏ
                </Link>
            </div>

            <form @submit.prevent="submit" class="app-card !p-10 !rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-white/5 space-y-10 relative overflow-hidden bg-white dark:bg-dark-card">
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>

                <div class="space-y-4">
                    <h3 class="text-[11px] font-black text-primary-500 uppercase tracking-widest px-1 italic border-l-4 border-primary-500 pl-3">Icon hiện hành</h3>
                    <div class="flex items-center gap-6 p-6 bg-slate-50/50 dark:bg-[#111c31] rounded-[2rem] border border-slate-100 dark:border-white/5 shadow-inner">
                        <div class="w-24 h-24 rounded-[1.5rem] overflow-hidden border-4 border-white dark:border-[#1a2741] shadow-lg shrink-0">
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
                    <input v-model="form.name" type="text" class="w-full bg-slate-50/50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border rounded-2xl py-5 px-6 text-main-text dark:text-white font-bold transition-all outline-none italic shadow-inner">
                </div>

                <div class="space-y-4 pt-6 border-t border-slate-50 dark:border-white/5 relative z-10">
                    <label class="text-[11px] font-black text-primary-500 uppercase tracking-widest px-1 italic">Thay đổi Icon nhận diện</label>
                    <div class="relative group cursor-pointer">
                        <input type="file" @change="handleFileSelect" class="absolute inset-0 opacity-0 cursor-pointer z-20">
                        <div class="border-2 border-dashed border-slate-200 dark:border-white/10 p-10 rounded-[2.5rem] flex flex-col items-center justify-center 
                                    bg-slate-50/50 dark:bg-[#111c31] 
                                    group-hover:bg-primary-50/50 dark:group-hover:bg-[#16223b] 
                                    group-hover:border-primary-500 transition-all shadow-inner">
                            
                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-500 mb-3 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                            </svg>
                            <span class="text-[10px] font-black uppercase text-slate-400 group-hover:text-primary-500 dark:group-hover:text-white transition-colors italic">Nhấp để chọn tệp icon mới</span>
                            <span v-if="form.icon_file" class="text-xs font-black text-primary-500 mt-2 italic uppercase animate-pulse">✓ {{ form.icon_file.name }}</span>
                        </div>
                    </div>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full btn-primary !py-6 !rounded-[2.5rem] shadow-2xl shadow-primary-500/30 font-black uppercase tracking-[0.2em] text-sm italic transition-all active:scale-95">
                    {{ form.processing ? 'Hệ thống đang cập nhật...' : 'Xác nhận thay đổi tiện ích' }}
                </button>
            </form>
        </div>
    </AdminLayout>
</template>