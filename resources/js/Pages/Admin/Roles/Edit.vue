<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
	role: {
		type: Object,
		required: true,
	},
});

const permissionGroups = [
	{
		key: 'dashboard',
		title: 'Báo cáo và điều hành',
		permissions: [
			{ key: 'dashboard.view', label: 'Xem dashboard tổng quan' },
			{ key: 'report.revenue.view', label: 'Xem báo cáo doanh thu' },
			{ key: 'report.export', label: 'Xuất dữ liệu báo cáo' },
		],
	},
	{
		key: 'booking',
		title: 'Đặt phòng và khách lưu trú',
		permissions: [
			{ key: 'booking.view', label: 'Xem danh sách đặt phòng' },
			{ key: 'booking.create', label: 'Tạo đơn đặt phòng mới' },
			{ key: 'booking.update', label: 'Chỉnh sửa thông tin đơn' },
			{ key: 'booking.cancel', label: 'Hủy đơn đặt phòng' },
			{ key: 'booking.checkin_checkout', label: 'Thao tác check-in / check-out' },
		],
	},
	{
		key: 'customer',
		title: 'Khách hàng và hồ sơ',
		permissions: [
			{ key: 'customer.view', label: 'Xem hồ sơ khách hàng' },
			{ key: 'customer.create', label: 'Tạo hồ sơ khách hàng' },
			{ key: 'customer.update', label: 'Cập nhật hồ sơ khách hàng' },
			{ key: 'customer.delete', label: 'Xóa hồ sơ khách hàng' },
		],
	},
	{
		key: 'invoice',
		title: 'Hóa đơn và thanh toán',
		permissions: [
			{ key: 'invoice.view', label: 'Xem hóa đơn' },
			{ key: 'invoice.generate', label: 'Xuất hóa đơn' },
			{ key: 'invoice.pay', label: 'Xác nhận thanh toán' },
		],
	},
	{
		key: 'contact_requests',
		title: 'Yêu cầu hỗ trợ khách hàng',
		permissions: [
			{ key: 'contact_requests.view', label: 'Xem danh sách yêu cầu hỗ trợ' },
			{ key: 'contact_requests.update', label: 'Cập nhật trạng thái yêu cầu' },
		],
	},
	{
		key: 'system',
		title: 'Cấu hình hệ thống',
		permissions: [
			{ key: 'staff.view', label: 'Xem danh sách nhân sự' },
			{ key: 'staff.manage', label: 'Thêm / sửa / khóa tài khoản nhân sự' },
			{ key: 'roles.manage', label: 'Quản lý nhóm quyền (roles)' },
			{ key: 'rooms.manage', label: 'Quản lý phòng và hạng phòng' },
		],
	},
];

const form = useForm({
	name: props.role?.name || '',
	slug: props.role?.slug || '',
	description: props.role?.description || '',
	level: props.role?.level || 'normal',
	is_active: props.role?.is_active ?? true,
	permissions: Array.isArray(props.role?.permissions)
		? props.role.permissions.map((item) => (typeof item === 'string' ? item : item.key || item.slug)).filter(Boolean)
		: [],
});

const allPermissions = permissionGroups.flatMap((group) => group.permissions.map((permission) => permission.key));
const selectedPermissionCount = computed(() => form.permissions.length);

const syncSlug = () => {
	if (!form.name) {
		form.slug = '';
		return;
	}

	form.slug = form.name
		.normalize('NFD')
		.replace(/[\u0300-\u036f]/g, '')
		.toLowerCase()
		.trim()
		.replace(/[^a-z0-9\s-]/g, '')
		.replace(/\s+/g, '-')
		.replace(/-+/g, '-');
};

const togglePermission = (permissionKey) => {
	if (form.permissions.includes(permissionKey)) {
		form.permissions = form.permissions.filter((item) => item !== permissionKey);
		return;
	}

	form.permissions = [...form.permissions, permissionKey];
};

const toggleGroupPermissions = (group) => {
	const keys = group.permissions.map((item) => item.key);
	const isAllSelected = keys.every((key) => form.permissions.includes(key));

	if (isAllSelected) {
		form.permissions = form.permissions.filter((key) => !keys.includes(key));
		return;
	}

	form.permissions = Array.from(new Set([...form.permissions, ...keys]));
};

const isGroupSelected = (group) => group.permissions.every((permission) => form.permissions.includes(permission.key));

const toggleAllPermissions = () => {
	if (form.permissions.length === allPermissions.length) {
		form.permissions = [];
		return;
	}

	form.permissions = [...allPermissions];
};

const submit = () => {
	form.put(route('admin.roles.update', props.role.id));
};
</script>

<template>
	<Head :title="`Cập nhật nhóm quyền ${role.name}`" />

	<AdminLayout>
		<div class="max-w-7xl mx-auto space-y-8 pb-12 animate-in fade-in duration-500">
			<div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
				<div>
					<span class="admin-index-subtitle mb-2 block">Chỉnh sửa phân quyền</span>
					<h1 class="admin-index-title !text-3xl">Cập Nhật Nhóm Quyền: {{ role.name }}</h1>
					<p class="text-desc mt-2">Điều chỉnh phạm vi thao tác và bộ permissions của role này.</p>
				</div>

				<Link :href="route('admin.roles.index')" class="admin-form-back-link self-start md:self-auto">Quay lại danh sách role</Link>
			</div>

			<div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
				<form @submit.prevent="submit" class="space-y-6">
					<section class="admin-form-card !space-y-6">
						<h2 class="admin-index-subtitle !text-primary-500 border-b border-slate-100 dark:border-dark-border pb-4">Thông tin nhóm quyền</h2>

						<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
							<div class="space-y-2 md:col-span-2">
								<label class="admin-index-subtitle px-1">Tên nhóm quyền</label>
								<input v-model="form.name" type="text" class="form-input-pms" @input="syncSlug" required>
							</div>
							<div class="space-y-2">
								<label class="admin-index-subtitle px-1">Slug</label>
								<input v-model="form.slug" type="text" class="form-input-pms">
							</div>
							<div class="space-y-2">
								<label class="admin-index-subtitle px-1">Mức độ quyền</label>
								<select v-model="form.level" class="form-input-pms cursor-pointer">
									<option value="high">Cao (Quản trị)</option>
									<option value="normal">Chuẩn (Vận hành)</option>
									<option value="restricted">Giới hạn (Tác vụ riêng)</option>
								</select>
							</div>
							<div class="space-y-2 md:col-span-2">
								<label class="admin-index-subtitle px-1">Mô tả</label>
								<textarea v-model="form.description" rows="3" class="form-input-pms custom-scrollbar" />
							</div>
						</div>
					</section>

					<section class="admin-form-card !space-y-6">
						<div class="flex items-center justify-between border-b border-slate-100 dark:border-dark-border pb-4">
							<h2 class="admin-index-subtitle !text-primary-500">Quản lý permissions</h2>
							<button type="button" class="admin-index-create-btn !px-4 !py-2 !text-xs" @click="toggleAllPermissions">
								{{ form.permissions.length === allPermissions.length ? 'Bỏ chọn tất cả' : 'Chọn tất cả' }}
							</button>
						</div>

						<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
							<article v-for="group in permissionGroups" :key="group.key" class="rounded-3xl border border-slate-100 dark:border-dark-border p-5 bg-slate-50/50 dark:bg-slate-800/40 space-y-4">
								<div class="flex items-center justify-between gap-3">
									<h3 class="text-sm font-black text-main-text dark:text-white">{{ group.title }}</h3>
									<button
										type="button"
										class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full border"
										:class="isGroupSelected(group) ? 'bg-primary-500 border-primary-500 text-white' : 'bg-white border-slate-200 text-slate-500 dark:bg-dark-card dark:border-dark-border dark:text-slate-300'"
										@click="toggleGroupPermissions(group)"
									>
										{{ isGroupSelected(group) ? 'Đã chọn' : 'Chọn nhóm' }}
									</button>
								</div>

								<div class="space-y-2">
									<button
										v-for="permission in group.permissions"
										:key="permission.key"
										type="button"
										class="w-full text-left rounded-2xl px-4 py-3 border text-sm font-bold transition-all"
										:class="form.permissions.includes(permission.key)
											? 'bg-primary-500/10 border-primary-500/30 text-primary-600 dark:text-primary-400'
											: 'bg-white border-slate-100 text-slate-600 dark:bg-dark-card dark:border-dark-border dark:text-slate-300'"
										@click="togglePermission(permission.key)"
									>
										{{ permission.label }}
									</button>
								</div>
							</article>
						</div>
					</section>

					<div class="flex justify-end">
						<button type="submit" :disabled="form.processing" class="btn-primary !px-8 !py-4">
							{{ form.processing ? 'Đang cập nhật...' : 'Lưu thay đổi role' }}
						</button>
					</div>
				</form>

				<aside class="space-y-6">
					<section class="app-card sticky top-24 space-y-4">
						<h3 class="admin-index-subtitle !text-primary-500">Tình trạng role</h3>

						<div class="rounded-2xl bg-slate-50 dark:bg-slate-800/50 p-4">
							<p class="text-[10px] font-black uppercase tracking-widest text-muted-text">Permission đã chọn</p>
							<p class="text-3xl font-black text-primary-500 mt-2">{{ selectedPermissionCount }}</p>
						</div>

						<div class="rounded-2xl border border-amber-200 bg-amber-50 dark:bg-amber-500/10 dark:border-amber-500/30 p-4">
							<p class="text-[10px] font-black uppercase tracking-widest text-amber-600">Khuyến nghị</p>
							<p class="text-xs font-semibold text-amber-700 dark:text-amber-300 mt-2 leading-6">
								Khi cập nhật permissions, nên rà lại quyền nhạy cảm để tránh mở rộng phạm vi truy cập ngoài mong muốn.
							</p>
						</div>
					</section>
				</aside>
			</div>
		</div>
	</AdminLayout>
</template>
