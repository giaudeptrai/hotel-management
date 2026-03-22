<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ rooms: Array });

// Flash message
const page = usePage();
const flashSuccess = computed(() => page.props.flash.success);

const statusMap = {
    available: { label: 'Trống', color: 'bg-emerald-500', text: 'text-emerald-600', bg: 'bg-emerald-50', border: 'border-emerald-100' },
    occupied: { label: 'Có khách', color: 'bg-rose-500', text: 'text-rose-600', bg: 'bg-rose-50', border: 'border-rose-100' },
    cleaning: { label: 'Dọn dẹp', color: 'bg-blue-500', text: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-100' },
    maintenance: { label: 'Bảo trì', color: 'bg-slate-500', text: 'text-slate-600', bg: 'bg-slate-50', border: 'border-slate-100' },
};

// Sắp xếp tầng
const floors = computed(() => {
    return [...new Set(props.rooms.map(r => r.floor))].sort((a, b) => a - b);
});

// Lọc phòng theo tầng
const getRoomsByFloor = (floor) => {
    return props.rooms
        .filter(r => r.floor == floor)
        .sort((a, b) => a.room_number.localeCompare(b.room_number, undefined, {numeric: true}));
};

const deleteRoom = (id) => {
    if (confirm('Xóa phòng này khỏi hệ thống?')) {
        router.delete(route('admin.rooms.destroy', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Quản lý Phòng" />
    <AdminLayout>
        <transition enter-active-class="transform transition duration-300" enter-from-class="translate-y-2 opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transition duration-200" leave-to-class="opacity-0">
            <div v-if="flashSuccess" class="fixed bottom-10 right-10 z-[100] bg-white dark:bg-dark-card border-l-4 border-emerald-500 shadow-2xl rounded-2xl p-4 flex items-center gap-4 min-w-[320px]">
                <div class="flex-1">
                    <p class="text-[10px] font-black text-emerald-500 uppercase">Hệ thống</p>
                    <p class="text-xs font-bold text-main-text dark:text-white">{{ flashSuccess }}</p>
                </div>
            </div>
        </transition>

        <div class="space-y-10 pb-20">
            <div class="flex justify-between items-end px-2">
                <div>
                    <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.3em] mb-2 block italic">Dashboard</span>
                    <h1 class="text-3xl font-black text-main-text dark:text-white tracking-tight italic uppercase">Sơ đồ phòng thực tế</h1>
                </div>
                <Link :href="route('admin.rooms.create')" class="btn-primary !py-3 !px-8 !rounded-xl font-black italic uppercase text-[11px]">
                    + Thêm phòng
                </Link>
            </div>

            <div v-for="floor in floors" :key="floor" class="space-y-6">
                <div class="flex items-center gap-4 px-2">
                    <span class="text-[11px] font-black italic uppercase text-primary-500 bg-primary-500/5 px-6 py-2 rounded-full border border-primary-500/10 tracking-widest">
                        Tầng {{ floor }}
                    </span>
                    <div class="h-[1px] flex-1 bg-slate-100 dark:from-dark-border"></div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
                    <div v-for="room in getRoomsByFloor(floor)" :key="room.id" 
                        class="app-card !p-6 !rounded-[2rem] border relative group transition-all duration-500 hover:shadow-2xl"
                        :class="room.is_active ? 'border-slate-100' : 'opacity-40 grayscale bg-slate-50'">
                        
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-2xl font-black italic text-main-text dark:text-white leading-none tracking-tighter">{{ room.room_number }}</span>
                            <div :class="`w-2.5 h-2.5 rounded-full ${statusMap[room.status].color} ring-4 ring-white dark:ring-slate-800`"></div>
                        </div>

                        <div class="space-y-2">
                            <div class="text-[11px] font-black text-main-text dark:text-white uppercase truncate italic tracking-tighter">
                                {{ room.definition?.name || 'Chưa gán hạng' }}
                            </div>
                            
                            <div class="text-[9px] font-bold text-muted-text uppercase italic">
                                {{ room.definition?.category?.name }} • {{ room.definition?.type?.name }}
                            </div>

                            <div :class="`inline-block text-[9px] font-black uppercase px-2.5 py-1 rounded-lg border ${statusMap[room.status].bg} ${statusMap[room.status].text} ${statusMap[room.status].border}`">
                                {{ statusMap[room.status].label }}
                            </div>
                        </div>

                        <div class="absolute inset-x-0 bottom-4 flex justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <Link :href="route('admin.rooms.edit', room.id)" class="w-8 h-8 flex items-center justify-center bg-white shadow-md rounded-full text-slate-600 hover:text-primary-500 border border-slate-50">
                                ✎
                            </Link>
                            <button @click="deleteRoom(room.id)" class="w-8 h-8 flex items-center justify-center bg-white shadow-md rounded-full text-slate-600 hover:text-rose-500 border border-slate-50">
                                ✕
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>