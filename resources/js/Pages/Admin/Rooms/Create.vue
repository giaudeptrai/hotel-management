<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ roomDefinitions: Array });

const form = useForm({
    room_number: '',
    floor: '',
    room_definition_id: '',
    status: 'available',
    is_active: true,
});

const submit = () => form.post(route('admin.rooms.store'));
</script>

<template>
    <Head title="Thêm Phòng Vật Lý" />
    <AdminLayout>
        <div class="max-w-2xl mx-auto space-y-8">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.3em] mb-2 block italic">Quản lý hạ tầng</span>
                    <h2 class="text-3xl font-black text-main-text dark:text-white tracking-tight uppercase italic">Thêm Phòng Mới</h2>
                </div>
                <Link :href="route('admin.rooms.index')" class="text-sm font-bold text-muted-text hover:text-primary-500 transition-colors flex items-center gap-2 mb-1">
                    Quay lại sơ đồ
                </Link>
            </div>

            <form @submit.prevent="submit" class="app-card !p-8 md:!p-10 !rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-dark-border space-y-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>

                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Số phòng (Ví dụ: 101, P.502) *</label>
                    <input v-model="form.room_number" type="text" placeholder="Nhập số phòng..." 
                        class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-primary-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-black italic transition-all outline-none">
                    <p v-if="form.errors.room_number" class="text-rose-500 text-[10px] font-black px-1 uppercase">{{ form.errors.room_number }}</p>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Vị trí tầng *</label>
                        <input v-model="form.floor" type="number" 
                            class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-primary-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-black italic outline-none transition-all">
                    </div>
                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Hạng phòng định nghĩa *</label>
                        <select v-model="form.room_definition_id" 
                            class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-primary-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-black italic outline-none appearance-none transition-all">
                            <option value="">-- Lựa chọn --</option>
                            <option v-for="df in roomDefinitions" :key="df.id" :value="df.id">
                                {{ df.name }} ({{ df.category?.name }})
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Trạng thái khởi tạo</label>
                        <select v-model="form.status" 
                            class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-primary-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-black italic outline-none transition-all">
                            <option value="available">Trống (Available)</option>
                            <option value="occupied">Có khách (Occupied)</option>
                            <option value="cleaning">Dọn dẹp (Cleaning)</option>
                            <option value="maintenance">Bảo trì (Maintenance)</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-8">
                        <label class="flex items-center gap-4 cursor-pointer group">
                            <div class="relative">
                                <input v-model="form.is_active" type="checkbox" class="sr-only peer">
                                <div class="w-12 h-6 bg-slate-200 rounded-full peer peer-checked:bg-primary-500 transition-all"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all peer-checked:left-7"></div>
                            </div>
                            <span class="text-[11px] font-black uppercase italic text-main-text tracking-wider">Kích hoạt kinh doanh</span>
                        </label>
                    </div>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full btn-primary !py-5 !rounded-2xl shadow-lg shadow-primary-500/20 font-black uppercase tracking-[0.2em] text-sm italic">
                    {{ form.processing ? 'Đang khởi tạo...' : 'Xác nhận khởi tạo phòng' }}
                </button>
            </form>
        </div>
    </AdminLayout>
</template>