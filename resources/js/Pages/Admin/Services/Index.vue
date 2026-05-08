<script setup>
import { ref, watch, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({
    services: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const statusFilter = ref(props.filters.status ?? '');
let searchTimeout = null;
const { flashSuccess } = useAdminFlash();

const totalServices = computed(() => props.services?.total || props.services?.data?.length || 0);
const activeServices = computed(() => (props.services?.data || []).filter((item) => item.is_active).length);

watch([search, typeFilter, statusFilter], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.services.index'), {
            search: search.value,
            type: typeFilter.value,
            status: statusFilter.value,
        }, { preserveState: true, replace: true });
    }, 300);
});

const formatCurrency = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v);
</script>

<template>
    <Head title="Menu Dịch Vụ" />
    <AdminLayout>
        <SuccessToast :message="flashSuccess" />

        <div class="space-y-6 pb-12 animate-in fade-in duration-500">
            <div class="flex flex-col gap-4 px-2 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <span class="admin-index-subtitle">Dịch vụ nội bộ</span>
                    <h1 class="admin-index-title">Menu Dịch Vụ</h1>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-600 dark:border-dark-border dark:bg-dark-bg dark:text-slate-300">Tổng {{ totalServices }} món</span>
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">Đang bán {{ activeServices }}</span>
                    </div>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <Link :href="route('admin.services.create')" class="admin-index-create-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-4 h-4"><path d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Thêm món mới
                    </Link>
                </div>
            </div>

            <div class="app-card !p-5 md:!p-6 !rounded-[2rem] flex flex-col xl:flex-row xl:items-end gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Tìm kiếm</label>
                        <div class="relative">
                            <input v-model="search" type="text" placeholder="Tên dịch vụ, món hàng..." class="admin-index-search !w-full !pl-10">
                            <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Loại dịch vụ</label>
                        <select v-model="typeFilter" class="admin-index-search !w-full">
                            <option value="">Tất cả loại</option>
                            <option value="food">Đồ ăn</option>
                            <option value="drink">Thức uống</option>
                            <option value="spa">Spa</option>
                            <option value="laundry">Giặt ủi</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Trạng thái</label>
                        <select v-model="statusFilter" class="admin-index-search !w-full">
                            <option value="">Tất cả</option>
                            <option :value="1">Đang bán</option>
                            <option :value="0">Ngừng bán</option>
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
                            <th class="index-table-th !px-8 !py-5">Dịch vụ / Món hàng</th>
                            <th class="index-table-th !px-8 !py-5">Đơn vị</th>
                            <th class="index-table-th !px-8 !py-5">Đơn Giá</th>
                            <th class="index-table-th !px-8 !py-5 text-center">Trạng thái</th>
                            <th class="index-table-th index-table-th-right !px-10 !py-5">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="index-table-body !text-sm !font-bold">
                        <tr v-for="(service, index) in services.data" :key="service.id" class="index-table-row">
                            <td class="index-table-th text-center text-muted-text font-black">{{ ((services.current_page - 1) * services.per_page) + index + 1 }}</td>
                            <td class="px-8 py-5">
                                <div class="flex flex-col items-start gap-1.5">
                                    <span class="text-main-text dark:text-white uppercase tracking-tighter">{{ service.name }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-widest shadow-sm"
                                          :class="{
                                              'bg-orange-100 text-orange-600': service.type === 'food',
                                              'bg-blue-100 text-blue-600': service.type === 'drink',
                                              'bg-purple-100 text-purple-600': service.type === 'spa',
                                              'bg-teal-100 text-teal-600': service.type === 'laundry',
                                              'bg-slate-200 text-slate-600': !['food','drink','spa','laundry'].includes(service.type)
                                          }">
                                        {{ service.type === 'food' ? '🍔 Đồ ăn' : service.type === 'drink' ? '🥤 Thức uống' : service.type === 'spa' ? '💆‍♀️ Spa' : service.type === 'laundry' ? '🧺 Giặt ủi' : '📦 Khác' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-[10px] text-muted-text uppercase tracking-widest border border-slate-200 dark:border-dark-border">
                                    {{ service.unit }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-primary-500 font-black">
                                {{ formatCurrency(service.price) }}
                            </td>
                            <td class="px-8 py-5 text-center">
                                <span :class="service.is_active ? 'text-emerald-500' : 'text-rose-500'" class="text-[9px] font-black uppercase tracking-widest">
                                    {{ service.is_active ? '● Đang bán' : '○ Ngừng' }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right px-10">
                                <div class="index-actions">
                                    <Link :href="route('admin.services.edit', service.id)" class="index-action-btn index-action-btn-edit !w-9 !h-9">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </Link>
                                    <button @click="router.delete(route('admin.services.destroy', service.id))" class="index-action-btn index-action-btn-delete !w-9 !h-9">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="services.data.length === 0">
                            <td colspan="6" class="index-empty-cell">
                                <p class="index-empty-text">Không tìm thấy món nào phù hợp bộ lọc hiện tại.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <div class="index-pagination !p-8">
                    <Pagination :links="services.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
