<script setup>
import { ref, watch, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({
    contactRequests: Object,
    filters: Object,
    stats: Object,
});

const { flashSuccess, flashError } = useAdminFlash();
const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
let searchTimeout = null;
const page = usePage();
const permissions = computed(() => page.props.auth?.permissions || []);

const hasPermission = (permission) => {
    if (!Array.isArray(permissions.value)) return false;
    return permissions.value.includes('*') || permissions.value.includes(permission);
};

const totalRequests = computed(() => props.stats?.total || props.contactRequests?.total || 0);

const statusMeta = {
    new: { label: 'Mới', badge: 'bg-blue-50 text-blue-600 border-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:border-blue-500/20', dot: 'bg-blue-500' },
    in_progress: { label: 'Đang xử lý', badge: 'bg-amber-50 text-amber-600 border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20', dot: 'bg-amber-500' },
    resolved: { label: 'Đã giải quyết', badge: 'bg-emerald-50 text-emerald-600 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20', dot: 'bg-emerald-500' },
    closed: { label: 'Đã đóng', badge: 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:border-slate-500/20', dot: 'bg-slate-500' },
};

const updateFilters = () => {
    router.get(route('admin.contact-requests.index'), {
        search: search.value,
        status: statusFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch([search, statusFilter], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(updateFilters, 300);
});

const updateStatus = (id, status) => {
    router.patch(route('admin.contact-requests.update-status', id), { status }, {
        preserveScroll: true,
    });
};

const formatDateTime = (value) => value ? new Intl.DateTimeFormat('vi-VN', {
    dateStyle: 'medium',
    timeStyle: 'short',
}).format(new Date(value)) : '---';

const snippet = (value, limit = 90) => {
    const text = String(value || '').trim();
    if (!text) return '---';
    return text.length > limit ? `${text.slice(0, limit)}...` : text;
};
</script>

<template>
    <Head title="Yêu cầu hỗ trợ" />

    <AdminLayout>
        <SuccessToast :message="flashSuccess" />

        <div class="space-y-6 pb-12 animate-in fade-in duration-500">
            <section class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-2">
                    <span class="admin-index-subtitle">Chăm sóc khách hàng</span>
                    <h1 class="admin-index-title !text-3xl">Yêu cầu hỗ trợ</h1>
                    <p class="text-desc max-w-2xl mt-1">Theo dõi toàn bộ yêu cầu liên hệ từ client, lọc nhanh theo trạng thái và cập nhật xử lý.</p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-black uppercase tracking-widest text-slate-600 shadow-sm dark:border-dark-border dark:bg-dark-card dark:text-slate-300">Tổng: {{ totalRequests }}</span>
                        <span class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-widest text-blue-600 shadow-sm dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-400">Mới: {{ props.stats?.new || 0 }}</span>
                        <span class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-widest text-amber-600 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">Đang xử lý: {{ props.stats?.in_progress || 0 }}</span>
                        <span class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-[11px] font-black uppercase tracking-widest text-emerald-600 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">Hoàn thành: {{ props.stats?.resolved || 0 }}</span>
                    </div>
                </div>
            </section>

            <section class="app-card !p-5 md:!p-6 !rounded-[2rem] flex flex-col sm:flex-row gap-4">
                <div class="space-y-2 flex-1">
                    <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Tìm kiếm</label>
                    <div class="relative">
                        <input v-model="search" type="text" placeholder="Tên, email, SĐT, chủ đề..." class="form-input-pms form-input-pms-compact w-full pl-10">
                        <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>
                <div class="space-y-2 w-full sm:w-64 shrink-0">
                    <label class="text-[10px] font-black uppercase text-muted-text px-1 tracking-widest">Lọc theo trạng thái</label>
                    <select v-model="statusFilter" class="form-input-pms form-input-pms-compact w-full">
                        <option value="">Tất cả trạng thái</option>
                        <option value="new">Mới</option>
                        <option value="in_progress">Đang xử lý</option>
                        <option value="resolved">Đã giải quyết</option>
                        <option value="closed">Đã đóng</option>
                    </select>
                </div>
            </section>

            <div v-if="flashError" class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-bold text-rose-600 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                {{ flashError }}
            </div>

            <article class="index-table-card !rounded-[2.5rem]">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="index-table w-full min-w-[1000px]">
                        <thead class="index-table-head">
                            <tr class="index-table-head-row">
                                <th class="index-table-th text-center w-12">#</th>
                                <th class="index-table-th w-56">Khách hàng</th>
                                <th class="index-table-th w-64">Chủ đề</th>
                                <th class="index-table-th">Nội dung</th>
                                <th class="index-table-th w-40 text-center">Trạng thái</th>
                                <th class="index-table-th w-32">Ngày gửi</th>
                                <th class="index-table-th text-right w-44">Thao tác xử lý</th>
                            </tr>
                        </thead>
                        <tbody class="index-table-body !text-sm">
                            <tr v-for="(request, index) in contactRequests.data" :key="request.id" class="index-table-row align-top">
                                <td class="index-table-th text-center text-muted-text font-black text-xs">
                                    {{ ((contactRequests.current_page - 1) * contactRequests.per_page) + index + 1 }}
                                </td>

                                <td class="index-table-th">
                                    <p class="text-main-text dark:text-white font-black tracking-tight mb-0.5">{{ request.name }}</p>
                                    <p class="text-[11px] font-bold text-muted-text">{{ request.phone }}</p>
                                    <p class="text-[11px] font-bold text-muted-text truncate">{{ request.email }}</p>
                                </td>

                                <td class="index-table-th">
                                    <p class="text-main-text dark:text-white font-bold leading-snug line-clamp-2" :title="request.subject">{{ request.subject }}</p>
                                    <p v-if="request.user" class="text-[10px] font-black uppercase tracking-widest text-primary-500 mt-1">Khách có tài khoản</p>
                                    <p v-else class="text-[10px] font-black uppercase tracking-widest text-slate-400 mt-1">Khách vãng lai</p>
                                </td>

                                <td class="index-table-th">
                                    <p class="text-slate-600 dark:text-slate-400 font-medium leading-relaxed line-clamp-3" :title="request.message">{{ snippet(request.message, 120) }}</p>
                                </td>

                                <td class="index-table-th text-center">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-widest" :class="statusMeta[request.status]?.badge || statusMeta.new.badge">
                                        <span class="h-1.5 w-1.5 rounded-full" :class="statusMeta[request.status]?.dot || statusMeta.new.dot"></span>
                                        {{ statusMeta[request.status]?.label || 'Mới' }}
                                    </span>
                                </td>

                                <td class="index-table-th text-slate-500 font-medium text-xs">
                                    {{ formatDateTime(request.created_at) }}
                                </td>

                                <td class="index-table-th text-right">
                                    <div class="flex flex-col gap-1.5 items-end">
                                        <button v-if="request.status !== 'in_progress' && hasPermission('contact_requests.update')" @click="updateStatus(request.id, 'in_progress')" class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20 transition-colors w-[110px] text-center border border-amber-200 dark:border-amber-500/30">
                                            Xử lý ngay
                                        </button>
                                        <button v-if="request.status !== 'resolved' && request.status !== 'new' && hasPermission('contact_requests.update')" @click="updateStatus(request.id, 'resolved')" class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20 transition-colors w-[110px] text-center border border-emerald-200 dark:border-emerald-500/30">
                                            Hoàn thành
                                        </button>
                                        <button v-if="request.status !== 'closed' && hasPermission('contact_requests.update')" @click="updateStatus(request.id, 'closed')" class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 transition-colors w-[110px] text-center border border-slate-200 dark:border-slate-700">
                                            Đóng đơn
                                        </button>
                                        <button v-if="request.status === 'closed' && hasPermission('contact_requests.update')" @click="updateStatus(request.id, 'new')" class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20 transition-colors w-[110px] text-center border border-blue-200 dark:border-blue-500/30">
                                            Mở lại
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="contactRequests.data.length === 0">
                                <td colspan="7" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-slate-50 dark:bg-slate-800/50 rounded-full flex items-center justify-center mb-3 border border-slate-100 dark:border-dark-border">
                                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                        </div>
                                        <p class="text-xs font-black uppercase tracking-widest italic text-slate-400">Không có yêu cầu hỗ trợ nào</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="contactRequests.links" class="index-pagination flex justify-center">
                    <Pagination :links="contactRequests.links" />
                </div>
            </article>
        </div>
    </AdminLayout>
</template>
