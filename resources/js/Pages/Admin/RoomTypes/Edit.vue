<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

// Nhận dữ liệu roomType từ Controller gửi qua
const props = defineProps({ roomType: Object });

// Khởi tạo Form với dữ liệu cũ (props.roomType)
const form = useForm({
    name: props.roomType.name,
    capacity_adult: props.roomType.capacity_adult,
    capacity_child: props.roomType.capacity_child,
});

// Hàm gửi dữ liệu cập nhật
const submit = () => {
    // Dùng patch và gửi kèm ID của loại phòng cần sửa
    form.patch(route('admin.room-types.update', props.roomType.id), {
        onSuccess: () => {
            // Khi lưu xong, Inertia tự chuyển về Index và hiện Toast
        }
    });
};
</script>

<template>
    <Head title="Cập nhật Loại Phòng" />

    <AdminLayout>
        <div class="max-w-2xl mx-auto space-y-8">
            
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="text-[10px] font-black text-amber-500 uppercase tracking-[0.3em] mb-2 block">Hiệu chỉnh thực thể</span>
                    <h2 class="text-3xl font-black text-main-text dark:text-white tracking-tight">Sửa Loại Phòng</h2>
                    <p class="text-xs text-muted-text mt-1 italic">Đang chỉnh sửa: {{ props.roomType.name }} (ID: {{ props.roomType.id }})</p>
                </div>
                <Link :href="route('admin.room-types.index')" 
                      class="text-sm font-bold text-muted-text hover:text-primary-500 transition-colors flex items-center gap-2 mb-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                    Hủy & Quay lại
                </Link>
            </div>

            <form @submit.prevent="submit" class="app-card !p-8 md:!p-10 !rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-dark-border space-y-8 relative overflow-hidden">
                
                <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>

                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">
                        Tên định danh mới
                    </label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        placeholder="Nhập tên mới..." 
                        class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-amber-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-amber-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-bold transition-all outline-none placeholder:text-slate-300 placeholder:font-medium"
                        :class="{'!border-rose-500/50 !bg-rose-50/30': form.errors.name}"
                    >
                    <p v-if="form.errors.name" class="text-rose-500 text-xs font-bold px-1animate-pulse">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Cập nhật số người lớn</label>
                        <div class="relative group">
                            <input 
                                v-model="form.capacity_adult" 
                                type="number" 
                                class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-amber-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-amber-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-black transition-all outline-none"
                            >
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black uppercase text-slate-300 group-focus-within:text-amber-600 transition-colors">Người lớn</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Cập nhật số trẻ em</label>
                        <div class="relative group">
                            <input 
                                v-model="form.capacity_child" 
                                type="number" 
                                class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-amber-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-amber-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-black transition-all outline-none"
                            >
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black uppercase text-slate-300 group-focus-within:text-amber-600 transition-colors">Trẻ em</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex items-center gap-3">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="flex-1 bg-amber-500 hover:bg-amber-600 text-white !py-5 !rounded-2xl shadow-lg shadow-amber-500/20 flex items-center justify-center gap-3 group transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span v-else class="font-black uppercase tracking-widest text-sm">Lưu thay đổi</span>
                        <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 group-hover:translate-x-1 transition-transform">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                    <Link :href="route('admin.room-types.index')" class="px-6 py-5 rounded-2xl font-bold text-sm text-muted-text hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        Hủy
                    </Link>
                </div>
            </form>

            <div class="flex items-start gap-4 p-6 bg-slate-50 dark:bg-dark-border/30 rounded-[2rem] border border-slate-100 dark:border-dark-border">
                <div class="w-10 h-10 rounded-full bg-white dark:bg-dark-bg flex items-center justify-center shadow-sm flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-amber-500"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" /></svg>
                </div>
                <p class="text-xs text-muted-text font-medium leading-relaxed">
                    <span class="text-main-text dark:text-white font-bold">Ghi chú Edit:</span> 
                    Mọi thay đổi về tên sẽ tự động cập nhật lại <span class="font-mono text-amber-600">slug</span> (đường dẫn) thông qua Service. 
                    Bạn hãy đảm bảo các con số sức chứa là hợp lý để không ảnh hưởng đến việc kết hợp với Hạng phòng ở bước sau.
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