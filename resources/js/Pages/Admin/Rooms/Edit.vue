<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ room: Object, roomDefinitions: Array });

const form = useForm({
    room_number: props.room.room_number,
    floor: props.room.floor,
    room_definition_id: props.room.room_definition_id,
    status: props.room.status,
    is_active: props.room.is_active === 1 || props.room.is_active === true,
});

const submit = () => form.put(route('admin.rooms.update', props.room.id));
</script>

<template>
    <Head title="Cập nhật phòng" />
    <AdminLayout>
        <div class="admin-form-page">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="admin-index-subtitle">Hiệu chỉnh hệ thống</span>
                    <h2 class="admin-index-title">Cập nhật: {{ room.room_number }}</h2>
                </div>
                <Link :href="route('admin.rooms.index')" class="admin-form-back-link">
                    Hủy thay đổi
                </Link>
            </div>

            <form @submit.prevent="submit" class="admin-form-card">
                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Số hiệu phòng *</label>
                    <input v-model="form.room_number" type="text"
                        class="form-input-pms">
                    <p v-if="form.errors.room_number" class="text-rose-500 text-[10px] font-black px-1 uppercase">{{ form.errors.room_number }}</p>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Tầng hiện tại</label>
                        <input v-model="form.floor" type="number"
                            class="form-input-pms input-number-clean">
                    </div>
                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Phân loại hạng phòng</label>
                        <select v-model="form.room_definition_id"
                            class="form-input-pms cursor-pointer appearance-none">
                            <option v-for="df in roomDefinitions" :key="df.id" :value="df.id">
                                {{ df.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Trình thái vận hành</label>
                    <select v-model="form.status"
                        class="form-input-pms cursor-pointer appearance-none">
                        <option value="available">Trống (Available)</option>
                        <option value="occupied">Có khách (Occupied)</option>
                        <option value="cleaning">Dọn dẹp (Cleaning)</option>
                        <option value="maintenance">Bảo trì (Maintenance)</option>
                    </select>
                </div>

                <div class="flex items-center justify-between p-6 bg-slate-50 dark:bg-dark-bg rounded-[2rem] border border-slate-200 dark:border-dark-border">
                    <div class="space-y-1">
                        <span class="text-[11px] font-black uppercase text-main-text block">Trạng thái kinh doanh</span>
                        <span class="text-[10px] text-muted-text font-bold uppercase">Phòng sẽ hiển thị trên sơ đồ nếu được kích hoạt</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input v-model="form.is_active" type="checkbox" class="sr-only peer">
                        <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-dark-border peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-500"></div>
                    </label>
                </div>

                <div class="flex gap-4 pt-4">
                     <button type="submit" :disabled="form.processing" class="flex-1 btn-primary !py-5 !rounded-2xl font-black uppercase tracking-[0.2em] text-sm">
                        {{ form.processing ? 'Đang lưu...' : 'Lưu cập nhật thay đổi' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
