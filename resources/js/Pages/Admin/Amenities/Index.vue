<script setup>
import { ref, watch, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({
    amenities: Object,
    filters: Object,
});
const { flashSuccess } = useAdminFlash();
const search = ref(props.filters?.search || '');
let searchTimeout = null;

const totalAmenities = computed(() => props.amenities?.total || props.amenities?.data?.length || 0);

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.amenities.index'), { search: value }, { preserveState: true, replace: true });
    }, 300);
});

const deleteItem = (id) => {
    if (confirm('Bạn có chắc muốn xóa tiện ích này? Hệ thống sẽ tự dọn dẹp ảnh trong Storage luôn đó!')) {
        router.delete(route('admin.amenities.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Quản lý Tiện ích" />
    <AdminLayout>
        <SuccessToast :message="flashSuccess" />

        <div class="space-y-6 pb-12 animate-in fade-in duration-500">
            <div class="flex flex-col gap-4 px-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="admin-index-subtitle">Danh mục dịch vụ</span>
                    <h1 class="admin-index-title">Tiện ích</h1>
                    <p class="text-desc mt-2">Tổng {{ totalAmenities }} tiện ích đang được sử dụng.</p>
                </div>
                <Link :href="route('admin.amenities.create')" class="admin-index-create-btn">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Thêm tiện ích
                </Link>
            </div>

            <div class="app-card !p-5 md:!p-6 !rounded-[2rem] flex flex-col xl:flex-row xl:items-end gap-4">
                <div class="space-y-2 flex-1">
                    <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Tìm kiếm</label>
                    <input v-model="search" type="text" placeholder="Tên tiện ích..." class="admin-index-search !w-full xl:!w-96">
                </div>
            </div>

            <div class="index-table-card">
                <div class="overflow-x-auto custom-scrollbar dark:custom-scrollbar-dark">
                <table class="index-table">
                    <thead class="index-table-head">
                        <tr class="index-table-head-row">
                            <th class="index-table-th text-center w-16">STT</th>
                            <th class="index-table-th w-28 text-center">Hình ảnh</th>
                            <th class="index-table-th">Tên tiện ích</th>
                            <th class="index-table-th index-table-th-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="index-table-body">
                        <tr v-for="(item, index) in amenities.data" :key="item.id" class="index-table-row font-bold text-main-text dark:text-white">
                            <td class="index-table-th text-center text-muted-text font-black">{{ ((amenities.current_page - 1) * amenities.per_page) + index + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="index-thumb-sm">
                                    <img v-if="item.icon_url" :src="item.icon_url" class="index-thumb-image" alt="icon">
                                    <div v-else class="w-full h-full flex items-center justify-center text-[8px] uppercase text-slate-400 font-black">No Image</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-base tracking-tight">{{ item.name }}</td>
                            <td class="px-6 py-4 text-right px-10">
                                <div class="index-actions">
                                    <Link :href="route('admin.amenities.edit', item.id)" class="index-action-btn index-action-btn-edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </Link>
                                    <button @click="deleteItem(item.id)" class="index-action-btn index-action-btn-delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="amenities.data.length === 0">
                            <td colspan="4" class="index-empty-cell">
                                <p class="index-empty-text">Chưa có tiện ích nào.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <div class="index-pagination">
                    <Pagination :links="amenities.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
