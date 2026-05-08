<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({
	roles: {
		type: Object,
		default: () => ({ data: [], links: [] }),
	},
	filters: {
		type: Object,
		default: () => ({ search: '', status: '' }),
	},
});

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status ?? '');
const { flashSuccess } = useAdminFlash();

watch([search, statusFilter], debounce(() => {
	router.get(route('admin.roles.index'), { search: search.value, status: statusFilter.value }, { preserveState: true, replace: true });
}, 350));

const rolesData = computed(() => props.roles?.data || []);
const totalRoles = computed(() => rolesData.value.length);
const roleWithMostMembers = computed(() => {
	if (!rolesData.value.length) {
		return null;
	}

	return [...rolesData.value].sort((a, b) => (b.users_count || 0) - (a.users_count || 0))[0];
});

const deleteRole = (id, name) => {
	if (!confirm(`Bạn có chắc muốn xóa nhóm quyền "${name}"?`)) {
		return;
	}

	router.delete(route('admin.roles.destroy', id), { preserveScroll: true });
};
</script>

<template>
	<Head title="Quản lý nhóm quyền" />

	<AdminLayout>
		<SuccessToast :message="flashSuccess" />

		<div class="max-w-7xl mx-auto space-y-8 pb-12 animate-in fade-in duration-500">
			<div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
				<div>
					<span class="admin-index-subtitle mb-2 block">Phân quyền hệ thống</span>
					<h1 class="admin-index-title !text-3xl">Danh Sách Nhóm Quyền</h1>
					<p class="text-desc mt-2">Quản lý cấu trúc phân quyền, phạm vi truy cập và giám sát số tài khoản đang được gán vào từng role.</p>
				</div>

				<div class="flex flex-col sm:flex-row gap-3">
					<Link :href="route('admin.roles.create')" class="admin-index-create-btn">
						<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
						Tạo nhóm quyền
					</Link>
				</div>
			</div>

			<div class="app-card !p-5 md:!p-6 !rounded-[2rem] flex flex-col xl:flex-row xl:items-end gap-4">
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
					<div class="space-y-2">
						<label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Tìm kiếm</label>
						<input v-model="search" type="text" class="admin-index-search !w-full" placeholder="Tìm theo tên role hoặc slug...">
					</div>
					<div class="space-y-2">
						<label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Trạng thái</label>
						<select v-model="statusFilter" class="admin-index-search !w-full">
							<option value="">Tất cả</option>
							<option :value="1">Đang hoạt động</option>
							<option :value="0">Tạm khóa</option>
						</select>
					</div>
				</div>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
				<article class="app-card !p-5">
					<p class="admin-index-subtitle">Tổng số role</p>
					<p class="text-3xl font-black text-main-text dark:text-white mt-2">{{ totalRoles }}</p>
				</article>
				<article class="app-card !p-5">
					<p class="admin-index-subtitle">Role nhiều tài khoản nhất</p>
					<p class="text-lg font-black text-primary-500 mt-2">{{ roleWithMostMembers?.name || '---' }}</p>
				</article>
				<article class="app-card !p-5">
					<p class="admin-index-subtitle">Số tài khoản gán cao nhất</p>
					<p class="text-3xl font-black text-emerald-500 mt-2">{{ roleWithMostMembers?.users_count || 0 }}</p>
				</article>
			</div>

			<div class="index-table-card">
				<div class="overflow-x-auto custom-scrollbar dark:custom-scrollbar-dark">
					<table class="index-table">
						<thead class="index-table-head">
							<tr class="index-table-head-row">
									<th class="index-table-th text-center w-16">STT</th>
								<th class="index-table-th">Tên nhóm quyền</th>
								<th class="index-table-th">Mã quyền (slug)</th>
								<th class="index-table-th">Tài khoản đã gán</th>
								<th class="index-table-th">Ngày cập nhật</th>
								<th class="index-table-th-right">Thao tác</th>
							</tr>
						</thead>
						<tbody class="index-table-body">
								<tr v-for="(role, index) in rolesData" :key="role.id" class="index-table-row">
									<td class="index-table-th text-center text-muted-text font-black">{{ ((roles.current_page - 1) * roles.per_page) + index + 1 }}</td>
								<td class="index-table-th">
									<p class="font-black text-main-text dark:text-white">{{ role.name }}</p>
								</td>
								<td class="index-table-th">
									<span class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[11px] font-black tracking-wide">
										{{ role.slug }}
									</span>
								</td>
								<td class="index-table-th">
									<span class="text-sm font-black text-primary-500">{{ role.users_count || role.users?.length || 0 }} tài khoản</span>
								</td>
								<td class="index-table-th text-sm text-slate-500 font-semibold">
									{{ role.updated_at ? new Date(role.updated_at).toLocaleDateString('vi-VN') : '---' }}
								</td>
								<td class="index-table-th-right">
									<div class="index-actions">
										<Link :href="route('admin.roles.edit', role.id)" class="index-action-btn index-action-btn-edit" title="Chỉnh sửa nhóm quyền">
											<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
										</Link>
										<button class="index-action-btn index-action-btn-delete" title="Xóa nhóm quyền" @click="deleteRole(role.id, role.name)">
											<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79" /></svg>
										</button>
									</div>
								</td>
							</tr>

							<tr v-if="rolesData.length === 0">
									<td colspan="6" class="index-empty-cell">
									<p class="index-empty-text">Chưa có nhóm quyền nào phù hợp bộ lọc hiện tại.</p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div v-if="roles.links?.length" class="index-pagination">
					<Pagination :links="roles.links" />
				</div>
			</div>
		</div>
	</AdminLayout>
</template>
