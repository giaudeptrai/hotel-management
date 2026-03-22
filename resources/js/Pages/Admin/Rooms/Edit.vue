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
        <div class="max-w-2xl mx-auto space-y-8">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.3em] mb-2 block italic">Hiệu chỉnh hệ thống</span>
                    <h2 class="text-3xl font-black text-main-text dark:text-white tracking-tight uppercase italic">Cập nhật: {{ room.room_number }}</h2>
                </div>
                <Link :href="route('admin.rooms.index')" class="text-sm font-bold text-muted-text hover:text-primary-500 transition-colors flex items-center gap-2 mb-1">
                    Hủy thay đổi
                </Link>
            </div>

            <form @submit.prevent="submit" class="app-card !p-8 md:!p-10 !rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-dark-border space-y-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>

                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Số hiệu phòng *</label>
                    <input v-model="form.room_number" type="text" 
                        class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-primary-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-black italic transition-all outline-none">
                    <p v-if="form.errors.room_number" class="text-rose-500 text-[10px] font-black px-1 uppercase">{{ form.errors.room_number }}</p>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Tầng hiện tại</label>
                        <input v-model="form.floor" type="number" 
                            class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-primary-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-black italic outline-none transition-all">
                    </div>
                    <div class="space-y-3">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Phân loại hạng phòng</label>
                        <select v-model="form.room_definition_id" 
                            class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-primary-500/10 rounded-2xl py-4 px-6 text-main-text dark:text-white font-black italic outline-none appearance-none transition-all">
                            <option v-for="df in roomDefinitions" :key="df.id" :value="df.id">
                                {{ df.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Trình thái vận hành</label>
                    <select v-model="form.status" 
                        class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white dark:focus:bg-dark-border focus:ring-4 focus:ring-primary-500/10 rounded-2xl py-4 px-6 font-black italic outline-none transition-all"
                        :class="{
                            'text-emerald-500': form.status === 'available',
                            'text-rose-500': form.status === 'occupied',
                            'text-blue-500': form.status === 'cleaning',
                            'text-slate-500': form.status === 'maintenance',
                        }">
                        <option value="available">Trống (Available)</option>
                        <option value="occupied">Có khách (Occupied)</option>
                        <option value="cleaning">Dọn dẹp (Cleaning)</option>
                        <option value="maintenance">Bảo trì (Maintenance)</option>
                    </select>
                </div>

                <div class="flex items-center justify-between p-6 bg-slate-50 dark:bg-dark-bg rounded-[2rem] border border-dashed border-slate-200 dark:border-dark-border">
                    <div class="space-y-1">
                        <span class="text-[11px] font-black uppercase italic text-main-text block">Trạng thái kinh doanh</span>
                        <span class="text-[10px] text-muted-text font-bold uppercase italic">Phòng sẽ hiển thị trên sơ đồ nếu được kích hoạt</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input v-model="form.is_active" type="checkbox" class="sr-only peer">
                        <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-dark-border peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-500"></div>
                    </label>
                </div>

                <div class="flex gap-4 pt-4">
                     <button type="submit" :disabled="form.processing" class="flex-1 btn-primary !py-5 !rounded-2xl shadow-lg shadow-primary-500/20 font-black uppercase tracking-[0.2em] text-sm italic">
                        {{ form.processing ? 'Đang lưu...' : 'Lưu cập nhật thay đổi' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>