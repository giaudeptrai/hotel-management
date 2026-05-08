<script setup>
import { ref, watch, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({
    customers: Object,
    filters: Object,
});

const { flashSuccess } = useAdminFlash();

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const genderFilter = ref(props.filters.gender || '');
let searchTimeout = null;

const totalCustomers = computed(() => props.customers?.total || props.customers?.data?.length || 0);

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.customers.index'),
            { search: value, type: typeFilter.value, gender: genderFilter.value },
            { preserveState: true, replace: true }
        );
    }, 300);
});

watch([typeFilter, genderFilter], () => {
    router.get(
        route('admin.customers.index'),
        { search: search.value, type: typeFilter.value, gender: genderFilter.value },
        { preserveState: true, replace: true }
    );
});

const clearFilters = () => {
    search.value = '';
    typeFilter.value = '';
    genderFilter.value = '';
    router.get(route('admin.customers.index'), {}, { preserveState: true, replace: true });
};

const getRowNumber = (index) => {
    const currentPage = Number(props.customers?.current_page || 1);
    const perPage = Number(props.customers?.per_page || (props.customers?.data?.length || 0));
    return (currentPage - 1) * perPage + index + 1;
};

const deleteCustomer = (id) => {
    if (confirm('Bạn có chắc chắn muốn xóa hồ sơ khách hàng này không? Dữ liệu lịch sử liên quan có thể bị ảnh hưởng.')) {
        router.delete(route('admin.customers.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Quản lý Khách hàng" />

    <AdminLayout>
        <SuccessToast :message="flashSuccess" />

        <div class="space-y-6 pb-12 animate-in fade-in duration-500">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between px-2">
                <div>
                    <h1 class="admin-index-title">Khách hàng</h1>
                    <p class="admin-index-subtitle">Danh sách đối tác & khách lưu trú</p>
                    <p class="text-desc mt-2">Tổng {{ totalCustomers }} hồ sơ khách hàng trong hệ thống.</p>
                </div>
                <Link :href="route('admin.customers.create')" class="admin-index-create-btn w-fit">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Thêm khách mới
                </Link>
            </div>

            <div class="app-card !p-5 md:!p-6 !rounded-[2rem] flex flex-col xl:flex-row xl:items-end gap-4 mx-2">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Tìm kiếm</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-muted-text">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </span>
                            <input v-model="search" type="text" placeholder="Tên, SĐT, CCCD..." class="admin-index-search !w-full !pl-10">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Nguồn khách</label>
                        <select v-model="typeFilter" class="admin-index-search !w-full">
                            <option value="">Tất cả nguồn</option>
                            <option value="online">Online</option>
                            <option value="walk_in">Tại quầy</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Giới tính</label>
                        <select v-model="genderFilter" class="admin-index-search !w-full">
                            <option value="">Tất cả</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                </div>

                <button v-if="search || typeFilter || genderFilter" @click="clearFilters" class="admin-filter-reset-btn whitespace-nowrap">
                    Bỏ lọc
                </button>
            </div>

            <div class="index-table-card">
                <div class="overflow-x-auto custom-scrollbar dark:custom-scrollbar-dark">
                <table class="index-table">
                    <thead class="index-table-head">
                        <tr class="index-table-head-row">
                            <th class="index-table-th text-center w-16">STT</th>
                            <th class="index-table-th">Thông tin khách hàng</th>
                            <th class="index-table-th">Số điện thoại</th>
                            <th class="index-table-th text-center">Giao dịch</th>
                            <th class="index-table-th text-center">Nguồn</th>
                            <th class="index-table-th text-center">Giới tính</th>
                            <th class="index-table-th index-table-th-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="index-table-body">
                        <tr v-for="(customer, index) in customers.data" :key="customer.id" class="index-table-row">
                            <td class="index-table-th text-center text-muted-text font-black">{{ getRowNumber(index) }}</td>
                            <td class="px-6 py-4 font-bold text-main-text dark:text-white text-base tracking-tight">
                                {{ customer.full_name }}
                                <div class="text-[10px] text-muted-text font-medium tracking-normal mt-0.5">{{ customer.email || 'Chưa cập nhật Email' }}</div>
                            </td>
                            <td class="px-6 py-4 text-muted-text font-bold">
                                {{ customer.phone }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-[10px] font-black text-main-text dark:text-white">{{ customer.total_bookings }} LẦN</span>
                                    <Link :href="route('admin.customers.history', customer.id)" class="text-[9px] font-black uppercase text-primary-500 hover:text-primary-600 tracking-widest underline underline-offset-2 transition-colors">
                                        Xem chi tiết
                                    </Link>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-muted-text">
                                {{ customer.type || '---' }}
                            </td>

                            <td class="px-6 py-4 text-center text-[10px] font-black uppercase tracking-widest text-muted-text">
                                {{ customer.gender || '---' }}
                            </td>

                            <td class="px-6 py-4 text-right px-10">
                                <div class="index-actions">
                                    <Link :href="route('admin.customers.edit', customer.id)" class="index-action-btn index-action-btn-edit hover:shadow-md">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </Link>
                                    <button @click="deleteCustomer(customer.id)" class="index-action-btn index-action-btn-delete hover:shadow-md">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="customers.data.length === 0">
                            <td colspan="6" class="index-empty-cell">
                                <p class="index-empty-text">Không tìm thấy hồ sơ khách hàng nào phù hợp.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <div class="index-pagination">
                    <Pagination :links="customers.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
