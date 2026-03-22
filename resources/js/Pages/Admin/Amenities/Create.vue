<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    icon_file: null,
});

const handleFileSelect = (e) => {
    form.icon_file = e.target.files[0];
};

const submit = () => {
    form.post(route('admin.amenities.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Thêm Tiện ích" />
    <AdminLayout>
        <div class="max-w-2xl mx-auto space-y-10 pb-20">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.3em] mb-2 block italic">Dịch vụ đặc quyền</span>
                    <h2 class="text-3xl font-black text-main-text dark:text-white tracking-tight italic uppercase">Thêm Tiện ích</h2>
                </div>
                <Link :href="route('admin.amenities.index')" class="text-sm font-bold text-muted-text hover:text-primary-500 transition-colors flex items-center gap-2 mb-1 italic">
                    ✕ Quay lại
                </Link>
            </div>

            <form @submit.prevent="submit" class="app-card !p-8 md:!p-12 !rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-white/5 space-y-10 relative overflow-hidden bg-white dark:bg-dark-card">
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>

                <div class="space-y-4 relative z-10">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Tên tiện ích *</label>
                    <input v-model="form.name" type="text" placeholder="Nhập tên tiện ích..." class="w-full bg-slate-50/50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border rounded-2xl py-5 px-6 text-main-text dark:text-white font-bold transition-all outline-none italic placeholder:text-slate-300">
                    <p v-if="form.errors.name" class="text-rose-500 text-[10px] font-black px-1 italic uppercase mt-1">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-4 pt-6 border-t border-slate-50 dark:border-white/5 relative z-10">
                    <label class="text-[11px] font-black text-primary-500 uppercase tracking-widest px-1 italic">Icon nhận diện dịch vụ</label>
                    <div class="relative group cursor-pointer">
                        <input type="file" @change="handleFileSelect" class="absolute inset-0 opacity-0 cursor-pointer z-20">
                        
                        <div class="border-2 border-dashed border-slate-200 dark:border-white/10 p-12 rounded-[2.5rem] flex flex-col items-center justify-center 
                                    bg-slate-50/50 dark:bg-[#111c31] 
                                    group-hover:bg-primary-50/50 dark:group-hover:bg-[#16223b] 
                                    group-hover:border-primary-500 transition-all relative shadow-inner">
                            
                            <svg class="w-10 h-10 text-slate-300 dark:text-slate-500 mb-3 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                            </svg>
                            <span class="text-[10px] font-black uppercase text-slate-400 group-hover:text-primary-500 dark:group-hover:text-white transition-colors italic">Nhấp hoặc kéo thả icon vào đây</span>
                            <span v-if="form.icon_file" class="text-xs font-black text-primary-500 mt-2 italic uppercase animate-pulse">✓ {{ form.icon_file.name }}</span>
                        </div>
                    </div>
                    <p v-if="form.errors.icon_file" class="text-rose-500 text-[10px] font-black px-1 italic uppercase mt-1">{{ form.errors.icon_file }}</p>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full btn-primary !py-6 !rounded-[2rem] shadow-2xl shadow-primary-500/30 font-black uppercase tracking-[0.2em] text-sm italic transition-all active:scale-95">
                    {{ form.processing ? 'Hệ thống đang lưu...' : 'Lưu tiện ích mới' }}
                </button>
            </form>
        </div>
    </AdminLayout>
</template>