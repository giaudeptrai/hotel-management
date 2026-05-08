<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import ErrorToast from '@/Components/Admin/ErrorToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({
    backups: {
        type: Array,
        default: () => [],
    },
});

const { flashSuccess, flashError } = useAdminFlash();

const totalBackups = computed(() => props.backups.length);
const latestBackupDate = computed(() => props.backups[0]?.date || 'Chưa có dữ liệu');

const createBackup = () => {
    if (confirm('Quá trình sao lưu cơ sở dữ liệu có thể mất vài giây. Bạn có muốn bắt đầu?')) {
        router.post(route('admin.backups.create'), {}, { preserveScroll: true });
    }
};

const deleteBackup = (path) => {
    if (confirm('Bạn có chắc chắn muốn xóa vĩnh viễn bản sao lưu này?')) {
        router.delete(route('admin.backups.destroy'), {
            data: { path },
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Quản lý sao lưu dữ liệu" />
    <AdminLayout>
        <SuccessToast :message="flashSuccess" />
        <ErrorToast :message="flashError" />

        <div class="max-w-7xl mx-auto space-y-6 pb-12 animate-in fade-in duration-500">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between px-2">
                <div>
                    <span class="admin-index-subtitle">Vận hành hệ thống</span>
                    <h1 class="admin-index-title">Sao lưu cơ sở dữ liệu</h1>
                    <p class="text-desc mt-2">Quản lý các bản sao lưu DB để đảm bảo an toàn dữ liệu khi vận hành.</p>
                </div>

                <button @click="createBackup" class="admin-index-create-btn w-fit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tạo bản sao lưu mới
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-2">
                <article class="app-card !p-5 border-l-4 border-l-primary-500">
                    <p class="admin-index-subtitle">Tổng số bản sao lưu</p>
                    <p class="text-3xl font-black text-main-text dark:text-white mt-2">{{ totalBackups }}</p>
                </article>
                <article class="app-card !p-5 border-l-4 border-l-emerald-500">
                    <p class="admin-index-subtitle !text-emerald-500">Bản sao lưu gần nhất</p>
                    <p class="text-sm font-black text-main-text dark:text-white mt-2">{{ latestBackupDate }}</p>
                </article>
            </div>

            <div class="index-table-card">
                <div class="overflow-x-auto custom-scrollbar dark:custom-scrollbar-dark">
                    <table class="index-table">
                        <thead class="index-table-head">
                            <tr class="index-table-head-row">
                                <th class="index-table-th">Tên tập tin</th>
                                <th class="index-table-th">Dung lượng</th>
                                <th class="index-table-th">Ngày tạo</th>
                                <th class="index-table-th index-table-th-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="index-table-body">
                            <tr v-for="backup in backups" :key="backup.path" class="index-table-row">
                                <td class="index-table-th font-black text-main-text dark:text-white">{{ backup.name }}</td>
                                <td class="index-table-th text-muted-text font-bold">{{ backup.size }}</td>
                                <td class="index-table-th text-muted-text font-bold">{{ backup.date }}</td>
                                <td class="index-table-th index-table-th-right">
                                    <div class="index-actions">
                                        <a
                                            :href="route('admin.backups.download', { path: backup.path })"
                                            class="index-action-btn index-action-btn-edit"
                                            title="Tải về"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V4.5m0 12l4.5-4.5M12 16.5l-4.5-4.5M4.5 19.5h15" />
                                            </svg>
                                        </a>
                                        <button
                                            @click="deleteBackup(backup.path)"
                                            class="index-action-btn index-action-btn-delete"
                                            title="Xóa bản sao lưu"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="backups.length === 0">
                                <td colspan="4" class="index-empty-cell">
                                    <p class="index-empty-text">Hệ thống chưa có bản sao lưu nào. Hãy tạo bản sao lưu đầu tiên.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
