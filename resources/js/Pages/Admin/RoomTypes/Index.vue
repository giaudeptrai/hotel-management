<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Nhận dữ liệu roomTypes từ Controller
defineProps({ roomTypes: Array });

// Lấy flash message từ backend (Sửa typo fash -> flash)
const page = usePage();
const flashSuccess = computed(() => page.props.flash.success);

// Hàm xử lý điều hướng sang trang sửa
const editType = (id) => {
    router.get(route('admin.room-types.edit', id));
};

// Hàm xử lý xóa
const deleteType = (id) => {
    if (confirm('bạn có chắc chắn muốn xóa loại phòng này chứ?')) {
        router.delete(route('admin.room-types.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Quản lý Loại phòng" />
    <AdminLayout>
        
        <transition 
            enter-active-class="transform transition duration-300 ease-out"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="flashSuccess" class="fixed bottom-10 right-10 z-[100] bg-white dark:bg-dark-card border-l-4 border-emerald-500 shadow-2xl rounded-2xl p-4 flex items-center gap-4 border border-slate-100 dark:border-dark-border min-w-[320px]">
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-500/10 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Hệ thống</p>
                    <p class="text-xs font-bold text-main-text dark:text-white">{{ flashSuccess }}</p>
                </div>
                <button @click="page.props.flash.success = null" class="text-slate-300 hover:text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </transition>

        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-black text-main-text dark:text-white tracking-tight">Loại phòng</h1>
                    <p class="text-[11px] text-muted-text uppercase font-bold tracking-[0.2em]">Quy mô & Sức chứa</p>
                </div>
                <Link :href="route('admin.room-types.create')" class="btn-primary !py-2.5 !px-6 shadow-app-primary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Thêm loại mới
                </Link>
            </div>

            <div class="app-card !p-0 overflow-hidden border border-slate-100 dark:border-dark-border shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/50 dark:bg-dark-bg/50 border-b border-slate-100 dark:border-dark-border">
                        <tr class="text-[10px] font-black text-muted-text uppercase tracking-widest">
                            <th class="px-6 py-4">Tên loại phòng</th>
                            <th class="px-6 py-4">Sức chứa tiêu chuẩn</th>
                            <th class="px-6 py-4 text-center">Tổng số phòng</th>
                            <th class="px-6 py-4 text-right px-10">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-dark-border text-sm">
                        <tr v-for="type in roomTypes" :key="type.id" class="group hover:bg-slate-50/40 dark:hover:bg-slate-800/30 transition-all">
                            
                            <td class="px-6 py-4">
                                <span class="font-bold text-main-text dark:text-white text-base tracking-tight italic">
                                    {{ type.name }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-xs">
                                <span class="font-black text-primary-500 italic">{{ type.capacity_adult }}</span> Người lớn 
                                <span class="mx-2 text-slate-300">/</span> 
                                <span class="font-bold text-slate-500">{{ type.capacity_child }}</span> Trẻ em
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="font-black text-main-text dark:text-white bg-slate-100 dark:bg-slate-800 px-3.5 py-1.5 rounded-xl text-xs border border-slate-200 dark:border-dark-border">
                                    {{ type.rooms ? type.rooms.length : 0 }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right px-10">
                                <div class="flex justify-end gap-3">
                                    <button @click="editType(type.id)" 
                                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-500/10 border border-slate-100 dark:border-dark-border transition-all shadow-sm active:scale-95"
                                            title="Sửa">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <button @click="deleteType(type.id)" 
                                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 border border-slate-100 dark:border-dark-border transition-all shadow-sm active:scale-95"
                                            title="Xóa">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>