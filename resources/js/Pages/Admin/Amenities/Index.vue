<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({ amenities: Array });
const page = usePage();
const flashSuccess = computed(() => page.props.flash.success);

const deleteItem = (id) => {
    if (confirm('Bạn có chắc muốn xóa tiện ích này? Hệ thống sẽ tự dọn dẹp ảnh trong Storage luôn đó!')) {
        router.delete(route('admin.amenities.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Quản lý Tiện ích" />
    <AdminLayout>
        <transition enter-active-class="transform transition duration-300" enter-from-class="translate-y-2 opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="flashSuccess" class="fixed bottom-10 right-10 z-[100] bg-white dark:bg-dark-card border-l-4 border-emerald-500 shadow-2xl rounded-2xl p-4 flex items-center gap-4 border border-slate-100 min-w-[320px]">
                <div class="w-10 h-10 bg-emerald-500/10 rounded-full flex items-center justify-center text-emerald-500 font-black italic">!</div>
                <div class="flex-1 text-xs font-bold text-main-text dark:text-white">{{ flashSuccess }}</div>
            </div>
        </transition>

        <div class="space-y-6">
            <div class="flex justify-between items-center px-2">
                <div>
                    <h1 class="text-2xl font-black text-main-text dark:text-white tracking-tight italic">Tiện ích</h1>
                    <p class="text-[11px] text-muted-text uppercase font-bold tracking-[0.2em]">Thumbnail & Dịch vụ đi kèm</p>
                </div>
                <Link :href="route('admin.amenities.create')" class="btn-primary !py-2.5 !px-6 shadow-app-primary flex items-center gap-2">
                    <span class="text-xl">+</span> Thêm tiện ích
                </Link>
            </div>

            <div class="app-card !p-0 overflow-hidden border border-slate-100 dark:border-dark-border shadow-sm">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/50 dark:bg-dark-bg/50 border-b border-slate-100 dark:border-dark-border">
                        <tr class="text-[10px] font-black text-muted-text uppercase tracking-widest">
                            <th class="px-6 py-4 w-28 text-center">Hình ảnh</th>
                            <th class="px-6 py-4">Tên tiện ích</th>
                            <th class="px-6 py-4 text-right px-10">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-dark-border text-sm">
                        <tr v-for="item in amenities" :key="item.id" class="group hover:bg-slate-50/40 transition-all font-bold text-main-text dark:text-white">
                            <td class="px-6 py-4">
                                <div class="w-14 h-14 mx-auto bg-slate-100 dark:bg-slate-800 rounded-2xl overflow-hidden border border-slate-200 dark:border-dark-border shadow-sm group-hover:scale-105 transition-transform">
                                    <img v-if="item.icon_url" :src="item.icon_url" class="w-full h-full object-cover" alt="icon">
                                    <div v-else class="w-full h-full flex items-center justify-center text-[8px] uppercase text-slate-400 font-black">No Image</div>
                                </div>
                            </td>   
                            <td class="px-6 py-4 text-base tracking-tight italic">{{ item.name }}</td>
                            <td class="px-6 py-4 text-right px-10">
                                <div class="flex justify-end gap-3">
                                    <Link :href="route('admin.amenities.edit', item.id)" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-primary-500 border border-slate-100 dark:border-dark-border transition-all">✎</Link>
                                    <button @click="deleteItem(item.id)" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-rose-500 border border-slate-100 dark:border-dark-border transition-all">✕</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>