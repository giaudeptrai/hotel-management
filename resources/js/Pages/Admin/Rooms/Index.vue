<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import SuccessToast from '@/Components/Admin/SuccessToast.vue';
import useAdminFlash from '@/Composables/useAdminFlash';

const props = defineProps({ rooms: Array });

// Flash message
const { flashSuccess } = useAdminFlash();

const statusMap = {
    available: { label: 'Trống', color: 'bg-emerald-500', text: 'text-emerald-600', bg: 'bg-emerald-50', border: 'border-emerald-100' },
    occupied: { label: 'Có khách', color: 'bg-rose-500', text: 'text-rose-600', bg: 'bg-rose-50', border: 'border-rose-100' },
    cleaning: { label: 'Dọn dẹp', color: 'bg-blue-500', text: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-100' },
    maintenance: { label: 'Bảo trì', color: 'bg-slate-500', text: 'text-slate-600', bg: 'bg-slate-50', border: 'border-slate-100' },
};

const ROOMS_PER_PAGE = 10;
const floorPages = ref({});

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

const getTotalPagesByFloor = (floor) => {
    const totalRooms = getRoomsByFloor(floor).length;
    return Math.max(1, Math.ceil(totalRooms / ROOMS_PER_PAGE));
};

const getCurrentFloorPage = (floor) => {
    const totalPages = getTotalPagesByFloor(floor);
    const current = floorPages.value[floor] || 1;
    return Math.min(Math.max(current, 1), totalPages);
};

const setFloorPage = (floor, page) => {
    const totalPages = getTotalPagesByFloor(floor);
    floorPages.value[floor] = Math.min(Math.max(page, 1), totalPages);
};

const getPaginatedRoomsByFloor = (floor) => {
    const rooms = getRoomsByFloor(floor);
    const currentPage = getCurrentFloorPage(floor);
    const start = (currentPage - 1) * ROOMS_PER_PAGE;
    return rooms.slice(start, start + ROOMS_PER_PAGE);
};

const getFloorPageNumbers = (floor) => {
    return Array.from({ length: getTotalPagesByFloor(floor) }, (_, i) => i + 1);
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
        <SuccessToast :message="flashSuccess" />

        <div class="space-y-8 pb-12">
            <div class="flex justify-between items-end px-2">
                <div>
                    <span class="admin-index-subtitle">Vận hành phòng</span>
                    <h1 class="admin-index-title">Sơ đồ phòng thực tế</h1>
                </div>
                <Link :href="route('admin.rooms.create')" class="admin-index-create-btn !py-3 !px-8">
                    + Thêm phòng
                </Link>
            </div>

            <div v-for="floor in floors" :key="floor" class="space-y-6">
                <div class="flex items-center gap-4 px-2">
                    <span class="text-[11px] font-black uppercase text-primary-500 bg-slate-100 dark:bg-dark-bg px-6 py-2 rounded-full border border-slate-200 dark:border-dark-border tracking-widest">
                        Tầng {{ floor }}
                    </span>
                    <div class="h-[1px] flex-1 bg-slate-100 dark:bg-dark-border"></div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
                    <div v-for="room in getPaginatedRoomsByFloor(floor)" :key="room.id"
                        class="app-card !p-6 !rounded-[2rem] border relative group transition-all duration-300"
                        :class="room.is_active ? 'border-slate-100' : 'opacity-40 grayscale bg-slate-50'">

                        <div class="flex justify-between items-start mb-4">
                            <span class="text-2xl font-black text-main-text dark:text-white leading-none tracking-tighter">{{ room.room_number }}</span>
                            <div :class="`w-2.5 h-2.5 rounded-full ${statusMap[room.status].color} ring-4 ring-white dark:ring-slate-800`"></div>
                        </div>

                        <div class="space-y-2">
                            <div class="text-[11px] font-black text-main-text dark:text-white uppercase truncate tracking-tighter">
                                {{ room.definition?.name || 'Chưa gán hạng' }}
                            </div>

                            <div class="text-[9px] font-bold text-muted-text uppercase">
                                {{ room.definition?.category?.name }} • {{ room.definition?.type?.name }}
                            </div>

                            <div :class="`inline-block text-[9px] font-black uppercase px-2.5 py-1 rounded-lg border ${statusMap[room.status].bg} ${statusMap[room.status].text} ${statusMap[room.status].border}`">
                                {{ statusMap[room.status].label }}
                            </div>
                        </div>

                        <div class="absolute inset-x-0 bottom-4 flex justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <Link :href="route('admin.rooms.edit', room.id)" class="index-action-btn index-action-btn-edit !w-8 !h-8 !rounded-full !bg-white !text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            </Link>
                            <button @click="deleteRoom(room.id)" class="index-action-btn index-action-btn-delete !w-8 !h-8 !rounded-full !bg-white !text-slate-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="getTotalPagesByFloor(floor) > 1" class="flex flex-wrap items-center justify-between gap-3 px-2">
                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-text">
                        Tầng {{ floor }}: Trang {{ getCurrentFloorPage(floor) }} / {{ getTotalPagesByFloor(floor) }}
                    </p>

                    <div class="flex items-center gap-2">
                        <button
                            @click="setFloorPage(floor, getCurrentFloorPage(floor) - 1)"
                            :disabled="getCurrentFloorPage(floor) === 1"
                            class="index-action-btn !w-auto !h-auto !px-4 !py-2 text-[10px] font-black uppercase tracking-widest !bg-white !text-main-text disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            Trước
                        </button>

                        <button
                            v-for="page in getFloorPageNumbers(floor)"
                            :key="`${floor}-${page}`"
                            @click="setFloorPage(floor, page)"
                            class="index-action-btn !w-9 !h-9 text-[10px] font-black border transition-all"
                            :class="page === getCurrentFloorPage(floor)
                                ? 'bg-slate-900 text-white border-slate-900'
                                : 'bg-white text-main-text border-slate-100 hover:border-primary-500 hover:text-primary-500'"
                        >
                            {{ page }}
                        </button>

                        <button
                            @click="setFloorPage(floor, getCurrentFloorPage(floor) + 1)"
                            :disabled="getCurrentFloorPage(floor) === getTotalPagesByFloor(floor)"
                            class="index-action-btn !w-auto !h-auto !px-4 !py-2 text-[10px] font-black uppercase tracking-widest !bg-white !text-main-text disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            Sau
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
