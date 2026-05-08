<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import RoomGalleryCard from '@/Components/Client/RoomGalleryCard.vue';
import { CLIENT_ROOMS_LABELS as L } from '@/Config/clientRoomsLabels';

const props = defineProps({
    rooms: { type: Array, default: () => [] },
    filters: { type: [Object, Array], default: () => ({}) },
    roomTypes: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    meta: { type: Object, default: () => ({}) },
});

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);

const getFilter = (key, defaultValue) => ((props.filters && props.filters[key]) ? props.filters[key] : defaultValue);

const searchQuery = ref('');
const searchForm = ref({
    check_in: getFilter('check_in', ''),
    check_out: getFilter('check_out', ''),
    guests: getFilter('guests', 1),
    category_id: getFilter('category_id', ''),
    room_type_id: getFilter('room_type_id', ''),
    sort: getFilter('sort', 'price_asc'),
});

const filteredRooms = computed(() => {
    let result = props.rooms || [];

    if (searchQuery.value) {
        const keyword = searchQuery.value.toLowerCase();
        result = result.filter((room) => (room.name || '').toLowerCase().includes(keyword) || (room.description || '').toLowerCase().includes(keyword));
    }

    if (searchForm.value.sort === 'price_asc') {
        result = [...result].sort((a, b) => (a.base_price || 0) - (b.base_price || 0));
    }

    if (searchForm.value.sort === 'price_desc') {
        result = [...result].sort((a, b) => (b.base_price || 0) - (a.base_price || 0));
    }

    return result;
});

const reloadSearch = () => {
    router.get(route('client.rooms.index'), searchForm.value, { preserveState: true, preserveScroll: true, replace: true });
};

const resetFilters = () => {
    searchQuery.value = '';
    searchForm.value = {
        check_in: '',
        check_out: '',
        guests: 1,
        category_id: '',
        room_type_id: '',
        sort: 'price_asc',
    };
    reloadSearch();
};
</script>

<template>
    <Head :title="`${L.index.pageTitle} - ${L.brandName}`" />

    <ClientLayout>
        <section class="relative bg-slate-900 pb-32 pt-32 lg:pt-40 lg:pb-40">
            <div class="absolute inset-0">
                <img src="https://images.pexels.com/photos/164595/pexels-photo-164595.jpeg?auto=compress&cs=tinysrgb&w=2200" alt="Rooms Banner" class="h-full w-full object-cover opacity-40" />
                <div class="absolute inset-0 bg-slate-900/60"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-white backdrop-blur mb-4">
                    {{ L.index.heroKicker }}
                </span>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl mb-6">
                    {{ meta.total_definitions || 0 }} {{ L.index.heroTitleSuffix }}
                </h1>
                <p class="text-lg text-slate-300">
                    {{ L.index.heroDesc }}
                </p>
            </div>
        </section>

        <section class="relative z-20 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 -mt-20">
            <div class="app-card !p-6 md:!p-8 !rounded-[2rem] shadow-2xl border border-slate-100 dark:border-dark-border bg-white dark:bg-dark-card">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 items-end">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">{{ L.index.filters.checkIn }}</label>
                        <input v-model="searchForm.check_in" @change="reloadSearch" type="date" class="form-input-pms form-input-pms-compact w-full">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">{{ L.index.filters.checkOut }}</label>
                        <input v-model="searchForm.check_out" @change="reloadSearch" type="date" class="form-input-pms form-input-pms-compact w-full">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">{{ L.index.filters.guests }}</label>
                        <input v-model="searchForm.guests" @change="reloadSearch" type="number" min="1" class="form-input-pms form-input-pms-compact input-number-clean w-full" placeholder="1">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">{{ L.index.filters.category }}</label>
                        <select v-model="searchForm.category_id" @change="reloadSearch" class="form-input-pms form-input-pms-compact w-full">
                            <option value="">{{ L.index.filters.allCategories }}</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">{{ L.index.filters.roomType }}</label>
                        <select v-model="searchForm.room_type_id" @change="reloadSearch" class="form-input-pms form-input-pms-compact w-full">
                            <option value="">{{ L.index.filters.allRoomTypes }}</option>
                            <option v-for="roomType in roomTypes" :key="roomType.id" :value="roomType.id">{{ roomType.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">{{ L.index.filters.quickSearch }}</label>
                        <input v-model="searchQuery" type="text" :placeholder="L.index.filters.quickSearchPlaceholder" class="form-input-pms form-input-pms-compact w-full">
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-dark-border flex flex-col gap-4 sm:flex-row sm:items-center justify-between">
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 text-xs font-bold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">{{ meta.total_definitions || 0 }} {{ L.index.summary.roomDefinitions }}</span>
                        <span class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-xs font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30">{{ meta.total_rooms || 0 }} {{ L.index.summary.totalRooms }}</span>
                        <span class="px-3 py-1.5 rounded-lg bg-primary-50 dark:bg-primary-500/10 text-xs font-bold text-primary-600 dark:text-primary-400 border border-primary-200 dark:border-primary-500/30">{{ formatCurrency(meta.lowest_price || 0) }} - {{ formatCurrency(meta.highest_price || 0) }}</span>
                    </div>

                    <div class="w-full sm:w-56 shrink-0">
                        <select v-model="searchForm.sort" @change="reloadSearch" class="form-input-pms form-input-pms-compact w-full cursor-pointer bg-white dark:bg-dark-bg text-xs font-bold">
                            <option value="price_asc">{{ L.index.filters.sortPriceAsc }}</option>
                            <option value="price_desc">{{ L.index.filters.sortPriceDesc }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">

            <div class="mb-10 text-center sm:text-left">
                <span class="text-sm font-black uppercase tracking-widest text-primary-500 block mb-2">{{ L.index.summary.resultKicker }}</span>
                <h2 class="text-3xl sm:text-4xl font-black text-main-text dark:text-white">{{ filteredRooms.length }} {{ L.index.summary.resultSuffix }}</h2>
                <p class="text-desc mt-2">{{ L.index.summary.resultDesc }}</p>
            </div>

            <div v-if="filteredRooms.length === 0" class="py-24 text-center bg-white dark:bg-dark-card rounded-[2rem] border-2 border-dashed border-slate-200 dark:border-dark-border">
                <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
                <p class="text-2xl font-black text-main-text dark:text-white">{{ L.index.empty.title }}</p>
                <p class="text-desc mt-2 max-w-md mx-auto">{{ L.index.empty.desc }}</p>
                <button @click="resetFilters" class="mt-6 btn-primary !px-6 !py-3">Xóa bộ lọc</button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <RoomGalleryCard
                    v-for="room in filteredRooms"
                    :key="room.id"
                    :room="room"
                    :searchForm="searchForm"
                />
            </div>

        </section>
    </ClientLayout>
</template>
