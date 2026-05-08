<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import RatingStars from '@/Components/Client/RatingStars.vue';
import { CLIENT_ROOMS_LABELS as L } from '@/Config/clientRoomsLabels';

const props = defineProps({
    room: { type: Object, required: true },
    searchForm: { type: Object, default: () => ({}) },
});

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);

const getCapacityText = () => {
    const adult = Number(props.room?.type?.capacity_adult ?? 0);
    const child = Number(props.room?.type?.capacity_child ?? 0);
    const total = adult + child;

    if (!total) return L.unknown.capacity;
    if (child > 0) return `${adult} người lớn + ${child} trẻ em`;
    return `${adult} người lớn`;
};

const roomImages = computed(() => {
    const imageUrls = Array.isArray(props.room?.image_urls) ? props.room.image_urls : [];
    if (imageUrls.length > 0) {
        return imageUrls;
    }

    const rawImages = Array.isArray(props.room?.images) ? props.room.images : [];
    const normalizedRawImages = rawImages
        .map((path) => {
            if (!path) {
                return null;
            }

            if (String(path).startsWith('http')) {
                return path;
            }

            if (String(path).startsWith('/storage/')) {
                return path;
            }

            return `/storage/${path}`;
        })
        .filter(Boolean);

    if (normalizedRawImages.length > 0) {
        return normalizedRawImages;
    }

    return ['https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=1200&auto=format&fit=crop'];
});
const primaryImage = computed(() => roomImages.value[0] || '');

const isRoomBookable = () => Boolean(props.room?.is_bookable);
const getRoomTypeLabel = () => props.room?.type?.name || L.unknown.roomType;
const getRoomCategoryLabel = () => props.room?.category?.name || L.unknown.category;
const getRatingSummary = () => props.room?.rating_summary || { average: 0, count: 0 };
</script>

<template>
    <article
        class="bg-white dark:bg-dark-card rounded-[2rem] border border-slate-100 dark:border-dark-border shadow-sm transition-all duration-300 flex flex-col group"
        :class="isRoomBookable() ? 'hover:shadow-xl hover:-translate-y-2' : 'opacity-60 saturate-50 grayscale-[30%]'"
    >
        <!-- Index only shows the first image; full gallery is in Room Show page. -->
        <div class="relative h-96 overflow-hidden rounded-t-[2rem] bg-slate-100 dark:bg-slate-800">
            <img
                v-if="primaryImage"
                :src="primaryImage"
                :alt="room.name"
                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
            >

            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>

            <!-- Badges -->
            <div class="absolute top-4 left-4 flex flex-col gap-2 items-start">
                <span class="bg-white/90 backdrop-blur text-slate-900 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm">{{ getRoomTypeLabel() }}</span>
                <span class="bg-slate-900/80 backdrop-blur text-white px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest shadow-sm">{{ getRoomCategoryLabel() }}</span>
            </div>

            <!-- Availability Badge -->
            <div class="absolute bottom-4 right-4">
                <span
                    class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest shadow-sm backdrop-blur"
                    :class="isRoomBookable() ? 'bg-emerald-500/90 text-white' : 'bg-rose-500/90 text-white'"
                >
                    {{ isRoomBookable() ? `${L.index.card.availablePrefix} ${room.available_rooms_count || 0} ${L.index.card.availableSuffix}` : L.index.card.unavailable }}
                </span>
            </div>

            <!-- Price Badge -->
            <div class="absolute bottom-4 left-4 text-left">
                <span class="text-[10px] font-black uppercase text-white/80 block mb-0.5">{{ L.index.card.pricePerNight }}</span>
                <span class="text-2xl font-black italic text-white">{{ formatCurrency(room.base_price) }}</span>
            </div>
        </div>

        <!-- Content Section -->
        <div class="p-6 flex flex-col flex-1">
            <div class="mb-3">
                <h3 class="text-2xl font-black text-main-text dark:text-white mb-1 line-clamp-2" :title="room.name">{{ room.name }}</h3>
                <p class="text-[11px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ getCapacityText() }}</p>
            </div>

            <p class="text-desc line-clamp-2 mb-6 flex-1">
                {{ room.description || L.unknown.roomDescription }}
            </p>

            <!-- Rating -->
            <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 mb-6 border border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <RatingStars :value="Number(getRatingSummary().average || 0)" />
                    <span class="text-sm font-black text-main-text dark:text-white">{{ Number(getRatingSummary().average || 0).toFixed(1) }}</span>
                </div>
                <span class="text-[10px] font-black uppercase tracking-widest text-muted-text">{{ getRatingSummary().count }} đánh giá</span>
            </div>

            <!-- Action Button -->
            <div class="mt-auto">
                <Link
                    v-if="isRoomBookable()"
                    :href="route('client.rooms.show', { room: room.id, check_in: searchForm.check_in, check_out: searchForm.check_out, guests: searchForm.guests })"
                    class="btn-primary w-full text-center !py-3.5 block"
                >
                    {{ L.index.card.viewDetail }}
                </Link>
                <button
                    v-else
                    type="button"
                    disabled
                    class="w-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-black uppercase tracking-widest text-xs py-3.5 rounded-xl cursor-not-allowed border border-slate-200 dark:border-slate-700"
                >
                    {{ L.index.card.soldOut }}
                </button>
            </div>
        </div>
    </article>
</template>
