<script setup>
import { ref, watch, computed } from 'vue';
import { router, Link, Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({
    users: Object,
    filters: Object,
    roles: Array,
});

const { flashSuccess, flashError } = useAdminFlash();
const search = ref(props.filters?.search || '');
const roleFilter = ref(props.filters?.role_id || '');
const statusFilter = ref(props.filters?.status ?? '');
let searchTimeout = null;

const totalUsers = computed(() => props.users?.total || props.users?.data?.length || 0);
const activeUsers = computed(() => (props.users?.data || []).filter((item) => Boolean(item.is_active)).length);

const getRoleSlug = (user) => user?.role || user?.role_relation?.slug || 'customer';
const getRoleLabel = (user) => {
    const slug = getRoleSlug(user);
    const name = user?.role_relation?.name;

    if (name) {
        return name;
    }

    const map = {
        admin: 'Quản trị viên',
        customer: 'Khách hàng',
        staff: 'Nhân sự',
    };

    return map[slug] || 'Khách hàng';
};

const getRoleClass = (user) => {
    const slug = getRoleSlug(user);

    if (slug === 'admin') {
        return 'bg-indigo-500';
    }

    if (slug === 'customer') {
        return 'bg-emerald-500';
    }

    if (slug === 'staff') {
        return 'bg-sky-500';
    }

    return 'bg-slate-500';
};

watch([search, roleFilter, statusFilter], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.users.index'),
            {
                search: search.value,
                role_id: roleFilter.value,
                status: statusFilter.value,
            },
            { preserveState: true, replace: true }
        );
    }, 300);
});

const deleteUser = (id) => {
    if (confirm('Bạn có chắc chắn muốn thu hồi quyền truy cập của tài khoản này không? Hành động này không thể hoàn tác.')) {
        router.delete(route('admin.users.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Quản lý Tài khoản" />
    <AdminLayout>
        <SuccessToast :message="flashSuccess" />

        <div class="space-y-6 pb-12 animate-in fade-in duration-500">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between px-2">
                <div class="space-y-2">
                    <h1 class="admin-index-title">Tài khoản</h1>
                    <p class="admin-index-subtitle">Quản lý tài khoản và quyền truy cập hệ thống</p>

                    <div class="flex flex-wrap gap-2 pt-1">
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-600 dark:border-dark-border dark:bg-dark-bg dark:text-slate-300">
                            Tổng {{ totalUsers }} tài khoản
                        </span>
                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-600 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                            Đang hoạt động {{ activeUsers }}
                        </span>
                    </div>
                </div>

                <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-center sm:justify-end lg:w-auto">
                    <Link :href="route('admin.users.create')" class="admin-index-create-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Thêm tài khoản
                    </Link>
                </div>
            </div>

            <div class="app-card !p-5 md:!p-6 !rounded-[2rem] flex flex-col xl:flex-row xl:items-end gap-4 mx-2">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 flex-1">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Tìm kiếm</label>
                        <input v-model="search" type="text" placeholder="Tìm theo tên hoặc email..." class="admin-index-search !w-full">
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

            <div v-if="flashError" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-600 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                {{ flashError }}
            </div>

            <div class="index-table-card">
                <div class="overflow-x-auto custom-scrollbar dark:custom-scrollbar-dark">
                <table class="index-table">
                    <thead class="index-table-head">
                        <tr class="index-table-head-row">
                            <th class="index-table-th text-center w-16">STT</th>
                            <th class="index-table-th">Hồ sơ tài khoản</th>
                            <th class="index-table-th text-center">Vai trò</th>
                            <th class="index-table-th text-center">Trạng thái</th>
                            <th class="index-table-th index-table-th-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="index-table-body">
                        <tr v-for="(user, index) in users.data" :key="user.id" class="index-table-row">
                            <td class="index-table-th text-center text-muted-text font-black">{{ ((users.current_page - 1) * users.per_page) + index + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-main-text dark:text-white text-base tracking-tight">{{ user.name }}</div>
                                <div class="text-[10px] text-muted-text font-mono">{{ user.email }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="getRoleClass(user)" class="px-3 py-1 rounded-full text-[9px] font-black text-white uppercase tracking-widest shadow-sm">
                                    {{ getRoleLabel(user) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div :class="user.is_active ? 'bg-emerald-500 shadow-emerald-500/50' : 'bg-rose-500 shadow-rose-500/50'" class="w-2 h-2 rounded-full shadow-lg"></div>
                                    <span class="text-[10px] font-black uppercase tracking-widest">{{ user.is_active ? 'Hoạt động' : 'Đã khóa' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right px-10">
                                <div class="index-actions">
                                    <Link :href="route('admin.users.edit', user.id)" class="index-action-btn index-action-btn-edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </Link>
                                    <button @click="deleteUser(user.id)" class="index-action-btn index-action-btn-delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="users.data.length === 0">
                            <td colspan="5" class="index-empty-cell">
                                <p class="index-empty-text">Không có tài khoản nào phù hợp với từ khóa tìm kiếm.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <div class="index-pagination">
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
