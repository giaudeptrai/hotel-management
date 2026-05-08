<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({
    staffs: Object,
    filters: Object,
    roles: Array,
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status ?? '');
const roleFilter = ref(props.filters?.role_id || '');
const { flashSuccess } = useAdminFlash();

const totalStaffs = computed(() => props.staffs?.total || props.staffs?.data?.length || 0);

watch([search, statusFilter, roleFilter], debounce(() => {
    router.get(route('admin.staffs.index'), {
        search: search.value,
        status: statusFilter.value,
        role_id: roleFilter.value,
    }, { preserveState: true, replace: true });
}, 300));

const deleteStaff = (id) => {
    if (confirm('Ní có chắc muốn cho nhân viên này nghỉ việc (Xóa)?')) {
        router.delete(route('admin.staffs.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Quản lý Nhân viên" />
    <AdminLayout>
        <SuccessToast :message="flashSuccess" />

        <div class="max-w-7xl mx-auto space-y-6 pb-12 animate-in fade-in duration-500">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end justify-between gap-4 px-2">
                <div>
                    <span class="admin-index-subtitle mb-2 block">Hệ thống nhân sự</span>
                    <h2 class="admin-index-title !text-3xl">Danh sách Nhân viên</h2>
                    <p class="text-desc mt-2">Tổng {{ totalStaffs }} hồ sơ nhân sự trong hệ thống.</p>
                </div>

                <Link :href="route('admin.staffs.create')" class="admin-index-create-btn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Thêm Nhân Viên
                </Link>
            </div>

            <div class="app-card !p-5 md:!p-6 !rounded-[2rem] flex flex-col xl:flex-row xl:items-end gap-4 px-2">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Tìm kiếm</label>
                        <input v-model="search" type="text" placeholder="Tìm tên, CCCD, SĐT..." class="admin-index-search !w-full">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Vai trò</label>
                        <select v-model="roleFilter" class="admin-index-search !w-full">
                            <option value="">Tất cả vai trò</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Trạng thái</label>
                        <select v-model="statusFilter" class="admin-index-search !w-full">
                            <option value="">Tất cả</option>
                            <option :value="1">Hoạt động</option>
                            <option :value="0">Đã khóa</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="index-table-card mt-8">
                <div class="overflow-x-auto custom-scrollbar dark:custom-scrollbar-dark">
                    <table class="index-table">
                        <thead class="index-table-head">
                            <tr class="index-table-head-row">
                                <th class="index-table-th text-center w-16">STT</th>
                                <th class="index-table-th">Mã NV</th>
                                <th class="index-table-th">Họ & Tên</th>
                                <th class="index-table-th">Thông tin liên hệ</th>
                                <th class="index-table-th">Vai trò / Chức vụ</th>
                                <th class="index-table-th text-center">Trạng thái</th>
                                <th class="index-table-th-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="index-table-body">
                            <tr v-for="(staff, index) in staffs.data" :key="staff.id" class="index-table-row">
                                <td class="index-table-th text-center text-muted-text font-black">{{ ((staffs.current_page - 1) * staffs.per_page) + index + 1 }}</td>
                                <td class="index-table-th font-black text-main-text dark:text-white uppercase tracking-widest">{{ staff.staff_code }}</td>
                                <td class="index-table-th font-bold text-main-text dark:text-white">{{ staff.full_name }}</td>
                                <td class="index-table-th">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-primary-500">{{ staff.phone || 'Chưa cập nhật' }}</span>
                                        <span class="text-desc !text-[10px] uppercase">{{ staff.user?.email }}</span>
                                    </div>
                                </td>
                                <td class="index-table-th">
                                    <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                        {{ staff.user?.role?.name || 'Chưa cấp quyền' }}
                                    </span>
                                </td>
                                <td class="index-table-th text-center">
                                    <span v-if="staff.is_active && staff.user?.is_active" class="px-2 py-1 bg-emerald-100 text-emerald-600 rounded text-[10px] font-black uppercase tracking-widest">Đang làm</span>
                                    <span v-else class="px-2 py-1 bg-rose-100 text-rose-600 rounded text-[10px] font-black uppercase tracking-widest">Đã nghỉ</span>
                                </td>
                                <td class="index-table-th index-actions index-table-th-right">
                                    <Link :href="route('admin.staffs.edit', staff.id)" class="index-action-btn index-action-btn-edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                                    </Link>
                                    <button @click="deleteStaff(staff.id)" class="index-action-btn index-action-btn-delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!staffs.data.length">
                                <td colspan="7" class="index-empty-cell">
                                    <p class="index-empty-text">Chưa có nhân viên nào trong hệ thống.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="index-pagination">
                    <Pagination :links="staffs.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
