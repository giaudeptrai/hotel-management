<script setup>
import { ref, watch, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

// Nhận dữ liệu roomTypes từ Controller
const props = defineProps({
    roomTypes: Object,
    filters: Object,
});

// Lấy flash message từ composable dùng chung
const { flashSuccess } = useAdminFlash();
const search = ref(props.filters?.search || '');
let searchTimeout = null;

const totalTypes = computed(() => props.roomTypes?.total || props.roomTypes?.data?.length || 0);

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.room-types.index'), { search: value }, { preserveState: true, replace: true });
    }, 300);
});

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
        <SuccessToast :message="flashSuccess" />

        <div class="space-y-6 pb-12 animate-in fade-in duration-500">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="admin-index-subtitle">Cấu hình sức chứa</span>
                    <h1 class="admin-index-title">Loại phòng</h1>
                    <p class="text-desc mt-2">Tổng {{ totalTypes }} loại phòng trong hệ thống.</p>
                </div>
                <Link :href="route('admin.room-types.create')" class="admin-index-create-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Thêm loại mới
                </Link>
            </div>

            <div class="app-card !p-5 md:!p-6 !rounded-[2rem] flex flex-col xl:flex-row xl:items-end gap-4">
                <div class="space-y-2 flex-1">
                    <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Tìm kiếm</label>
                    <input v-model="search" type="text" placeholder="Tên hoặc slug loại phòng..." class="admin-index-search !w-full xl:!w-96">
                </div>
            </div>

            <div class="index-table-card">
                <div class="overflow-x-auto custom-scrollbar dark:custom-scrollbar-dark">
                <table class="index-table">
                    <thead class="index-table-head">
                        <tr class="index-table-head-row">
                            <th class="index-table-th text-center w-16">STT</th>
                            <th class="index-table-th">Tên loại phòng</th>
                            <th class="index-table-th">Sức chứa tiêu chuẩn</th>
                            <th class="index-table-th text-center">Tổng số phòng</th>
                            <th class="index-table-th index-table-th-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="index-table-body">
                        <tr v-for="(type, index) in roomTypes.data" :key="type.id" class="index-table-row">
                            <td class="index-table-th text-center text-muted-text font-black">{{ ((roomTypes.current_page - 1) * roomTypes.per_page) + index + 1 }}</td>

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
                                <div class="index-actions">
                                    <button @click="editType(type.id)"
                                            class="index-action-btn index-action-btn-edit active:scale-95"
                                            title="Sửa">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <button @click="deleteType(type.id)"
                                            class="index-action-btn index-action-btn-delete active:scale-95"
                                            title="Xóa">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>

                        </tr>
                        <tr v-if="roomTypes.data.length === 0">
                            <td colspan="5" class="index-empty-cell">
                                <p class="index-empty-text">Chưa có loại phòng nào.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <div class="index-pagination">
                    <Pagination :links="roomTypes.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
