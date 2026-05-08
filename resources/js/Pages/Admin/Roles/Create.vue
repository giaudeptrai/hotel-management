<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const permissionGroups = [
	{
		key: 'dashboard',
		title: 'Báo cáo và điều hành',
		desc: 'Quyền theo dõi tình hình vận hành và doanh thu của khách sạn.',
		permissions: [
			{ key: 'dashboard.view', label: 'Xem dashboard tổng quan' },
			{ key: 'report.revenue.view', label: 'Xem báo cáo doanh thu' },
			{ key: 'report.export', label: 'Xuất dữ liệu báo cáo' },
		],
	},
	{
		key: 'booking',
		title: 'Đặt phòng và khách lưu trú',
		desc: 'Quyền xử lý vòng đời booking, check-in, check-out và lịch sử đơn.',
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
		desc: 'Quyền truy cập và quản lý thông tin khách lưu trú.',
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
		desc: 'Quyền kiểm soát thu chi, xuất hóa đơn và xác nhận thanh toán.',
		permissions: [
			{ key: 'invoice.view', label: 'Xem hóa đơn' },
			{ key: 'invoice.generate', label: 'Xuất hóa đơn' },
			{ key: 'invoice.pay', label: 'Xác nhận thanh toán' },
		],
	},
	{
		key: 'system',
		title: 'Cấu hình hệ thống',
		desc: 'Quyền thay đổi cấu hình nhạy cảm và quản trị người dùng.',
		permissions: [
			{ key: 'staff.view', label: 'Xem danh sách nhân sự' },
			{ key: 'staff.manage', label: 'Thêm / sửa / khóa tài khoản nhân sự' },
			{ key: 'roles.manage', label: 'Quản lý nhóm quyền (roles)' },
			{ key: 'rooms.manage', label: 'Quản lý phòng và hạng phòng' },
		],
	},
];

const roleTemplates = {
	manager: [
		'dashboard.view',
		'report.revenue.view',
		'report.export',
		'booking.view',
		'booking.create',
		'booking.update',
		'booking.cancel',
		'booking.checkin_checkout',
		'customer.view',
		'customer.create',
		'customer.update',
		'invoice.view',
		'invoice.generate',
		'invoice.pay',
		'staff.view',
	],
	reception: [
		'booking.view',
		'booking.create',
		'booking.update',
		'booking.checkin_checkout',
		'customer.view',
		'customer.create',
		'customer.update',
		'invoice.view',
		'invoice.generate',
		'invoice.pay',
	],
	housekeeping: [
		'booking.view',
		'booking.checkin_checkout',
		'customer.view',
	],
};

const allPermissions = permissionGroups.flatMap((group) => group.permissions.map((permission) => permission.key));

const form = useForm({
	name: '',
	slug: '',
	description: '',
	level: 'normal',
	is_active: true,
	permissions: [],
});

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
	const groupPermissionKeys = group.permissions.map((item) => item.key);
	const groupFullySelected = groupPermissionKeys.every((permissionKey) => form.permissions.includes(permissionKey));

	if (groupFullySelected) {
		form.permissions = form.permissions.filter((permissionKey) => !groupPermissionKeys.includes(permissionKey));
		return;
	}

	const merged = new Set([...form.permissions, ...groupPermissionKeys]);
	form.permissions = Array.from(merged);
};

const isGroupSelected = (group) => group.permissions.every((permission) => form.permissions.includes(permission.key));

const applyTemplate = (templateName) => {
	form.permissions = [...(roleTemplates[templateName] || [])];
};

const toggleAllPermissions = () => {
	if (form.permissions.length === allPermissions.length) {
		form.permissions = [];
		return;
	}

	form.permissions = [...allPermissions];
};

const submit = () => {
	form.post(route('admin.roles.store'));
};
</script>

<template>
	<Head title="Thiết lập nhóm quyền" />

	<AdminLayout>
		<div class="max-w-7xl mx-auto space-y-8 pb-12 animate-in fade-in duration-500">
			<div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between px-1">
				<div class="max-w-3xl">
					<span class="admin-index-subtitle mb-2 block">Phân quyền hệ thống</span>
					<h1 class="admin-index-title !text-3xl">Thiết Kế Nhóm Quyền Và Kiểm Soát Truy Cập</h1>
					<p class="text-desc mt-3 leading-7">
						Xây dựng role theo nguyên tắc đặc quyền tối thiểu: tách rõ phạm vi truy cập và quyền thao tác chi tiết
						để hệ thống vận hành minh bạch, an toàn.
					</p>
				</div>

				<Link :href="route('admin.roles.index')" class="admin-form-back-link self-start md:self-auto">
					<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
					Quay lại danh sách vai trò
				</Link>
			</div>

			<div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
				<form @submit.prevent="submit" class="space-y-6">
					<section class="admin-form-card !space-y-6">
						<div class="flex items-center justify-between border-b border-slate-100 dark:border-dark-border pb-4">
							<h2 class="admin-index-subtitle !text-primary-500">1. Quản lý nhóm quyền hạn (Roles)</h2>
							<span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Role Profile</span>
						</div>

						<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
							<div class="space-y-2 md:col-span-2">
								<label class="admin-index-subtitle px-1">Tên nhóm quyền <span class="text-rose-500">*</span></label>
								<input
									v-model="form.name"
									type="text"
									class="form-input-pms"
									placeholder="VD: Trưởng ca lễ tân"
									@input="syncSlug"
									required
								>
								<p v-if="form.errors.name" class="text-rose-500 text-xs font-bold px-1">{{ form.errors.name }}</p>
							</div>

							<div class="space-y-2">
								<label class="admin-index-subtitle px-1">Mã quyền (slug)</label>
								<input v-model="form.slug" type="text" class="form-input-pms" placeholder="truong-ca-le-tan">
								<p v-if="form.errors.slug" class="text-rose-500 text-xs font-bold px-1">{{ form.errors.slug }}</p>
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
								<label class="admin-index-subtitle px-1">Mô tả vai trò nghiệp vụ</label>
								<textarea
									v-model="form.description"
									rows="3"
									class="form-input-pms custom-scrollbar"
									placeholder="Mô tả phạm vi trách nhiệm và luồng thao tác của nhóm quyền..."
								/>
								<p v-if="form.errors.description" class="text-rose-500 text-xs font-bold px-1">{{ form.errors.description }}</p>
							</div>
						</div>
					</section>

					<section class="admin-form-card !space-y-6">
						<div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between border-b border-slate-100 dark:border-dark-border pb-4">
							<h2 class="admin-index-subtitle !text-primary-500">2. Quản lý chi tiết quyền (Permissions)</h2>

							<div class="flex flex-wrap items-center gap-2">
								<button type="button" class="admin-index-secondary-btn !px-4 !py-2 !text-xs" @click="applyTemplate('manager')">Preset Quản lý</button>
								<button type="button" class="admin-index-secondary-btn !px-4 !py-2 !text-xs" @click="applyTemplate('reception')">Preset Lễ tân</button>
								<button type="button" class="admin-index-secondary-btn !px-4 !py-2 !text-xs" @click="applyTemplate('housekeeping')">Preset Buồng phòng</button>
								<button type="button" class="admin-index-create-btn !px-4 !py-2 !text-xs" @click="toggleAllPermissions">
									{{ form.permissions.length === allPermissions.length ? 'Bỏ chọn tất cả' : 'Chọn tất cả' }}
								</button>
							</div>
						</div>

						<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
							<article v-for="group in permissionGroups" :key="group.key" class="rounded-3xl border border-slate-100 dark:border-dark-border p-5 bg-slate-50/50 dark:bg-slate-800/40 space-y-4">
								<div class="flex items-start justify-between gap-3">
									<div>
										<h3 class="text-sm font-black text-main-text dark:text-white">{{ group.title }}</h3>
										<p class="text-xs font-semibold text-slate-500 mt-1">{{ group.desc }}</p>
									</div>

									<button
										type="button"
										class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full border transition-colors"
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
										class="w-full text-left rounded-2xl px-4 py-3 border text-sm font-bold transition-all flex items-center justify-between gap-3"
										:class="form.permissions.includes(permission.key)
											? 'bg-primary-500/10 border-primary-500/30 text-primary-600 dark:text-primary-400'
											: 'bg-white border-slate-100 text-slate-600 dark:bg-dark-card dark:border-dark-border dark:text-slate-300'"
										@click="togglePermission(permission.key)"
									>
										<span>{{ permission.label }}</span>
										<span class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
											:class="form.permissions.includes(permission.key) ? 'border-primary-500 bg-primary-500 text-white' : 'border-slate-300'">
											<svg v-if="form.permissions.includes(permission.key)" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
										</span>
									</button>
								</div>
							</article>
						</div>

						<p v-if="form.errors.permissions" class="text-rose-500 text-xs font-bold px-1">{{ form.errors.permissions }}</p>
					</section>

					<div class="flex justify-end gap-3">
						<button type="submit" :disabled="form.processing" class="btn-primary !px-8 !py-4">
							{{ form.processing ? 'Đang lưu phân quyền...' : 'Lưu nhóm quyền' }}
						</button>
					</div>
				</form>

				<aside class="space-y-6">
					<section class="app-card space-y-4 sticky top-24">
						<h3 class="admin-index-subtitle !text-primary-500">Tóm tắt cấu hình</h3>

						<div class="rounded-2xl bg-slate-50 dark:bg-slate-800/50 p-4">
							<p class="text-[10px] font-black uppercase tracking-widest text-muted-text">Quyền đã chọn</p>
							<p class="text-2xl font-black text-primary-500 mt-2">{{ selectedPermissionCount }}</p>
						</div>

						<div class="rounded-2xl border border-amber-200 bg-amber-50 dark:bg-amber-500/10 dark:border-amber-500/30 p-4">
							<p class="text-[10px] font-black uppercase tracking-widest text-amber-600">Nguyên tắc bảo mật</p>
							<p class="text-xs font-semibold text-amber-700 dark:text-amber-300 mt-2 leading-6">
								Chỉ cấp đúng quyền cần thiết cho từng vai trò. Hạn chế cấp quyền xóa dữ liệu hoặc truy cập báo cáo nhạy cảm
								nếu không phục vụ trực tiếp nghiệp vụ.
							</p>
						</div>

						<div class="rounded-2xl border border-slate-100 dark:border-dark-border p-4 space-y-2">
							<p class="text-[10px] font-black uppercase tracking-widest text-muted-text">Mô tả chức năng</p>
							<p class="text-xs text-slate-500 leading-6">
								Hệ thống hỗ trợ khởi tạo và chuẩn hóa nhóm quyền để ban quản lý kiểm soát truy cập theo cấp bậc.
							</p>
							<p class="text-xs text-slate-500 leading-6">
								Quyền được tách nhỏ theo từng thao tác nghiệp vụ, đáp ứng nguyên tắc đặc quyền tối thiểu.
							</p>
							<p class="text-xs text-slate-500 leading-6">
								Khi tài khoản đăng nhập, hệ thống đối chiếu permissions của role để hiển thị đúng module và khóa các thao tác vượt thẩm quyền.
							</p>
						</div>
					</section>
				</aside>
			</div>
		</div>
	</AdminLayout>
</template>
