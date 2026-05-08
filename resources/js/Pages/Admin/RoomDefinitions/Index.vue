<script setup>
import { computed, ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({
    definitions: Object,
    filters: Object,
    categories: Array,
    roomTypes: Array,
});

const { flashSuccess } = useAdminFlash();

const search = ref(props.filters?.search || '');
const categoryFilter = ref(props.filters?.room_category_id || '');
const typeFilter = ref(props.filters?.room_type_id || '');
let searchTimeout = null;

const totalDefinitions = computed(() => props.definitions?.total || props.definitions?.data?.length || 0);

watch([search, categoryFilter, typeFilter], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.room-definitions.index'), {
            search: search.value,
            room_category_id: categoryFilter.value,
            room_type_id: typeFilter.value,
        }, { preserveState: true, replace: true });
    }, 300);
});

const deleteItem = (id) => {
    if (confirm('Xóa hạng phòng này sẽ ảnh hưởng đến các phòng vật lý. Bạn chắc chứ?')) {
        router.delete(route('admin.room-definitions.destroy', id));
    }
};
</script>

<template>
    <Head title="Quản lý Hạng phòng" />
    <AdminLayout>
        <SuccessToast :message="flashSuccess" />

        <div class="space-y-6 pb-12 animate-in fade-in duration-500">
            <div class="flex flex-col gap-4 px-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="admin-index-subtitle">Danh mục cấu hình phòng</span>
                    <h1 class="admin-index-title uppercase">Hạng Phòng</h1>
                    <p class="text-desc mt-2">Tổng {{ totalDefinitions }} định nghĩa phòng đang được quản lý.</p>
                </div>
                <Link :href="route('admin.room-definitions.create')" class="admin-index-create-btn">
                    <span class="text-xl">+</span> Tạo hạng phòng
                </Link>
            </div>

            <div class="app-card !p-5 md:!p-6 !rounded-[2rem] flex flex-col xl:flex-row xl:items-end gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
                    <div class="space-y-2 sm:col-span-1">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Tìm kiếm</label>
                        <input v-model="search" type="text" placeholder="Tên hạng phòng..." class="admin-index-search !w-full">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Danh mục</label>
                        <select v-model="categoryFilter" class="admin-index-search !w-full">
                            <option value="">Tất cả danh mục</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Loại phòng</label>
                        <select v-model="typeFilter" class="admin-index-search !w-full">
                            <option value="">Tất cả loại</option>
                            <option v-for="roomType in roomTypes" :key="roomType.id" :value="roomType.id">{{ roomType.name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="index-table-card">
                <div class="overflow-x-auto custom-scrollbar dark:custom-scrollbar-dark">
                <table class="index-table">
                    <thead class="index-table-head">
                        <tr class="index-table-head-row">
                            <th class="index-table-th text-center w-16">STT</th>
                            <th class="index-table-th w-40 text-center">Hình ảnh</th>
                            <th class="index-table-th">Thông tin hạng phòng</th>
                            <th class="index-table-th">Giá & Diện tích</th>
                            <th class="index-table-th index-table-th-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="index-table-body">
                        <tr v-for="(item, index) in definitions.data" :key="item.id" class="index-table-row font-bold text-main-text dark:text-white">
                            <td class="index-table-th text-center text-muted-text font-black">{{ ((definitions.current_page - 1) * definitions.per_page) + index + 1 }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="index-thumb-md">
                                    <img v-if="item.image_urls?.length"
                                        :src="item.image_urls[0]"
                                        class="index-thumb-image object-contain">

                                    <div v-else class="text-[8px] uppercase text-slate-400 font-black italic">No Image</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-base tracking-tight uppercase font-black">{{ item.name }}</div>
                                <div class="text-[10px] text-slate-400 uppercase mt-1">
                                    {{ item.category?.name }} • {{ item.type?.name }}
                                </div>
                                <div class="flex gap-1 mt-2">
                                    <img v-for="amenity in item.amenities.slice(0, 5)" :key="amenity.id" :src="amenity.icon_url" class="w-5 h-5 rounded-md border border-slate-100 shadow-sm" :title="amenity.name">
                                    <span v-if="item.amenities.length > 5" class="text-[10px] text-slate-400 flex items-center ml-1">+{{ item.amenities.length - 5 }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-primary-500 font-black">{{ new Intl.NumberFormat().format(item.base_price) }}đ</div>
                                <div class="text-[10px] text-muted-text uppercase font-bold tracking-widest">{{ item.area }} m² • {{ item.view }}</div>
                            </td>
                            <td class="px-6 py-4 text-right px-10">
                                <div class="index-actions">
                                    <Link :href="route('admin.room-definitions.edit', item.id)" class="index-action-btn index-action-btn-edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </Link>
                                    <button @click="deleteItem(item.id)" class="index-action-btn index-action-btn-delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="definitions.data.length === 0">
                            <td colspan="5" class="index-empty-cell">
                                <p class="index-empty-text">Chưa có hạng phòng nào.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <div class="index-pagination">
                    <Pagination :links="definitions.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
