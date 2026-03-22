<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    capacity_adult: 2,
    capacity_child: 1,
});

const submit = () => {
    form.post(route('admin.room-types.store'), {
        onSuccess: () => {
            // Có thể thêm thông báo thành công ở đây
        }
    });
};
</script>

<template>
    <Head title="Khởi tạo Loại Phòng" />

    <AdminLayout>
        <div class="max-w-2xl mx-auto space-y-8">
            
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.3em] mb-2 block">Thiết lập thực thể</span>
                    <h2 class="text-3xl font-black text-main-text dark:text-white tracking-tight">Thêm Loại Phòng</h2>
                </div>
                <Link :href="route('admin.room-types.index')" 
                      class="text-sm font-bold text-muted-text hover:text-primary-500 transition-colors flex items-center gap-2 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                    Quay lại danh sách
                </Link>
            </div>

            <form @submit.prevent="submit" class="app-card !p-8 md:!p-10 !rounded-[2.5rem] shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-dark-border space-y-8 relative overflow-hidden">
                
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>

                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">
                        Tên định danh (Ví dụ: Single, Double, VIP Twin...)
                    </label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        placeholder="Nhập tên loại phòng..." 
                        class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-primary-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-bold transition-all outline-none placeholder:text-slate-300 placeholder:font-medium"
                        :class="{'!border-rose-500/50 !bg-rose-50/30': form.errors.name}"
                    >
                    <p v-if="form.errors.name" class="text-rose-500 text-xs font-bold px-1 animate-pulse">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Số người lớn</label>
                        <div class="relative group">
                            <input 
                                v-model="form.capacity_adult" 
                                type="number" 
                                class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-primary-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-black transition-all outline-none"
                            >
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black uppercase text-slate-300 group-focus-within:text-primary-500 transition-colors">Người lớn</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Số trẻ em</label>
                        <div class="relative group">
                            <input 
                                v-model="form.capacity_child" 
                                type="number" 
                                class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-primary-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-black transition-all outline-none"
                            >
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black uppercase text-slate-300 group-focus-within:text-primary-500 transition-colors">Trẻ em</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full btn-primary !py-5 !rounded-2xl shadow-lg shadow-primary-500/20 flex items-center justify-center gap-3 group transition-all active:scale-[0.98]"
                    >
                        <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span v-else class="font-black uppercase tracking-widest text-sm">Khởi tạo loại phòng</span>
                        <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 group-hover:translate-x-1 transition-transform">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </div>
            </form>

            <div class="flex items-start gap-4 p-6 bg-slate-50 dark:bg-dark-border/30 rounded-[2rem] border border-slate-100 dark:border-dark-border">
                <div class="w-10 h-10 rounded-full bg-white dark:bg-dark-bg flex items-center justify-center shadow-sm flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-primary-500"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" /></svg>
                </div>
                <p class="text-xs text-muted-text font-medium leading-relaxed">
                    <span class="text-main-text dark:text-white font-bold">Lưu ý hệ thống:</span> 
                    Loại phòng là đơn vị cơ bản dùng để phân loại sức chứa. Sau bước này, bạn sẽ kết hợp nó với các 
                    <span class="text-primary-500 font-bold">Hạng phòng (Deluxe, Suite)</span> để tạo nên các gói phòng kinh doanh.
                </p>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Xóa spinner của input number để sạch sẽ hơn */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>