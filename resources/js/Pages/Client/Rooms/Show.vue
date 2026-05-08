<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import RatingStars from '@/Components/Client/RatingStars.vue';
import { CLIENT_ROOMS_LABELS as L } from '@/Config/clientRoomsLabels';

const props = defineProps({
    room: { type: Object, default: () => ({}) },
    filters: { type: [Object, Array], default: () => ({}) },
    relatedRooms: { type: Array, default: () => [] },
    reviewSummary: { type: Object, default: () => ({ average: 0, count: 0 }) },
    recentReviews: { type: Object, default: () => ({ data: [], links: [] }) },
});

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);

const getFilter = (key, defaultValue) => ((props.filters && props.filters[key]) ? props.filters[key] : defaultValue);

const getCapacityData = (room = props.room) => {
    const adult = Number(room?.type?.capacity_adult ?? 0);
    const child = Number(room?.type?.capacity_child ?? 0);
    const total = adult + child;

    return {
        adult,
        child,
        total: total > 0 ? total : null,
    };
};

const getCapacityText = (room = props.room) => {
    const capacity = getCapacityData(room);

    if (!capacity.total) {
        return L.unknown.capacity;
    }

    if (capacity.child > 0) {
        return `${capacity.adult} người lớn + ${capacity.child} trẻ em`;
    }

    return `${capacity.adult} người lớn`;
};

const getTotalCapacity = (room = props.room) => getCapacityData(room).total;
const getRoomImages = (room = props.room) => {
    const imageUrls = Array.isArray(room?.image_urls) ? room.image_urls : [];
    if (imageUrls.length > 0) {
        return imageUrls;
    }

    const rawImages = Array.isArray(room?.images) ? room.images : [];
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

    return [];
};
const getRoomImage = (room = props.room) => getRoomImages(room)[0] || '';
const isRoomBookable = (room = props.room) => Boolean(room?.is_bookable);

const currentImageIndex = ref(0);
const roomImages = computed(() => getRoomImages());
const roomImage = computed(() => roomImages.value[currentImageIndex.value] || roomImages.value[0] || '');
const maxGuests = computed(() => getTotalCapacity() || 1);
const roomBookable = computed(() => isRoomBookable());
const roomCapacityText = computed(() => getCapacityText());
const reviewsData = computed(() => props.recentReviews?.data || []);
const formError = ref('');
const page = usePage();
const user = computed(() => page.props.auth?.user);

watch(roomImages, () => {
    if (currentImageIndex.value >= roomImages.value.length) {
        currentImageIndex.value = 0;
    }
}, { immediate: true });

const previousImage = () => {
    const total = roomImages.value.length;
    if (total <= 1) {
        return;
    }

    currentImageIndex.value = (currentImageIndex.value - 1 + total) % total;
};

const nextImage = () => {
    const total = roomImages.value.length;
    if (total <= 1) {
        return;
    }

    currentImageIndex.value = (currentImageIndex.value + 1) % total;
};

const selectImage = (index) => {
    currentImageIndex.value = index;
};

const bookingForm = useForm({
    room_id: props.room?.id || '',
    check_in: getFilter('check_in', ''),
    check_out: getFilter('check_out', ''),
    guests: getFilter('guests', 1),
});

const totalNights = computed(() => {
    if (!bookingForm.check_in || !bookingForm.check_out) {
        return 0;
    }

    const start = new Date(bookingForm.check_in);
    const end = new Date(bookingForm.check_out);
    const diffDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24));

    return diffDays > 0 ? diffDays : 0;
});

const totalPrice = computed(() => totalNights.value * (props.room?.base_price || 0));

const buildCheckoutParams = () => ({
    room: props.room.id,
    check_in: bookingForm.check_in,
    check_out: bookingForm.check_out,
    guests: bookingForm.guests,
});

const proceedToCheckout = () => {
    if (!roomBookable.value) {
        formError.value = L.validation.noAvailability;
        return;
    }

    if (totalNights.value <= 0) {
        formError.value = L.validation.invalidDates;
        return;
    }

    if (Number(bookingForm.guests) > maxGuests.value) {
        formError.value = `${L.validation.guestsExceededPrefix} ${maxGuests.value} ${L.validation.guestsExceededSuffix}`;
        return;
    }

    if (!user.value) {
        formError.value = 'Vui lòng đăng nhập để tiếp tục đặt phòng.';
        bookingForm.get(route('client.booking.create', buildCheckoutParams()));
        return;
    }

    formError.value = '';
    bookingForm.get(route('client.booking.create', buildCheckoutParams()));
};
</script>

<template>
    <Head :title="`${room.name} - ${L.brandName}`" />

    <ClientLayout>
        <div class="bg-slate-50 dark:bg-slate-900/50 pt-28 pb-10 border-b border-slate-100 dark:border-dark-border">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center gap-2 text-sm font-bold text-slate-500">
                    <Link :href="route('client.rooms.index')" class="hover:text-primary-500 transition-colors flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Tất cả phòng
                    </Link>
                    <span>/</span>
                    <span class="text-slate-400">{{ room.category?.name || L.unknown.category }}</span>
                </div>

                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <span class="px-3 py-1 rounded-lg bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 text-[10px] font-black uppercase tracking-widest border border-primary-100 dark:border-primary-500/20">{{ room.type?.name || L.unknown.roomType }}</span>
                            <span v-if="room.area" class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-black uppercase tracking-widest border border-slate-200 dark:border-slate-700">{{ room.area }} {{ L.show.tags.areaUnit }}</span>
                        </div>
                        <h1 class="text-3xl md:text-5xl font-black text-main-text dark:text-white tracking-tight">{{ room.name }}</h1>
                        <div class="flex items-center gap-4 mt-4">
                            <div class="flex items-center gap-1.5 bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 rounded-lg border border-amber-100 dark:border-amber-500/20">
                                <RatingStars :value="Number(reviewSummary.average || 0)" size-class="w-3.5 h-3.5" />
                                <span class="text-xs font-black text-amber-600 dark:text-amber-400">{{ reviewSummary.average ? reviewSummary.average.toFixed(1) : '0.0' }}</span>
                            </div>
                            <span class="text-xs font-bold text-slate-500">{{ reviewSummary.count || 0 }} lượt đánh giá</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-10 items-start">

                <div class="space-y-10">
                    <div class="w-full h-[300px] sm:h-[450px] rounded-[2.5rem] overflow-hidden shadow-lg border border-slate-100 dark:border-dark-border relative group">
                        <img v-if="roomImage" :src="roomImage" :alt="room.name" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        <div v-else class="w-full h-full bg-slate-100 dark:bg-slate-800 flex flex-col items-center justify-center text-slate-400">
                            <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                            <span class="text-xs font-black uppercase tracking-widest">{{ L.show.labels.noImage }}</span>
                        </div>

                        <button
                            v-if="roomImages.length > 1"
                            type="button"
                            class="absolute left-4 top-1/2 -translate-y-1/2 z-10 rounded-full bg-white/85 text-slate-900 p-2.5 shadow-lg transition hover:bg-white"
                            @click="previousImage"
                            aria-label="Ảnh trước"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </button>

                        <button
                            v-if="roomImages.length > 1"
                            type="button"
                            class="absolute right-4 top-1/2 -translate-y-1/2 z-10 rounded-full bg-white/85 text-slate-900 p-2.5 shadow-lg transition hover:bg-white"
                            @click="nextImage"
                            aria-label="Ảnh tiếp theo"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>

                        <div v-if="roomImages.length > 1" class="absolute top-4 right-4 rounded-lg bg-slate-900/70 px-3 py-1.5 text-xs font-black text-white backdrop-blur">
                            {{ currentImageIndex + 1 }} / {{ roomImages.length }}
                        </div>
                    </div>

                    <div v-if="roomImages.length > 1" class="flex gap-3 overflow-x-auto py-2">
                        <button
                            v-for="(image, index) in roomImages"
                            :key="`${room.id || 'room'}-${index}`"
                            type="button"
                            class="h-20 w-28 shrink-0 overflow-hidden rounded-2xl border-2 transition"
                            :class="currentImageIndex === index ? 'border-primary-500 shadow-md' : 'border-slate-200 dark:border-slate-700 opacity-80 hover:opacity-100'"
                            @click="selectImage(index)"
                        >
                            <img :src="image" :alt="`${room.name} - ảnh ${index + 1}`" class="h-full w-full object-cover" />
                        </button>
                    </div>

                    <div class="app-card !p-8 !rounded-[2rem]">
                        <h2 class="admin-index-title !text-2xl mb-4">{{ L.show.sections.roomDescription }}</h2>
                        <p class="text-desc leading-relaxed text-justify whitespace-pre-line">
                            {{ room.description || L.show.fallbackDescription }}
                        </p>
                    </div>

                    <div class="app-card !p-8 !rounded-[2rem]">
                        <span class="admin-index-subtitle block mb-2">{{ L.show.sections.amenitiesKicker }}</span>
                        <h2 class="admin-index-title !text-2xl mb-6">{{ L.show.sections.amenitiesTitle }}</h2>

                        <div v-if="room.amenities && room.amenities.length" class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div v-for="amenity in room.amenities" :key="amenity.id" class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-dark-border">
                                <img v-if="amenity.icon_url" :src="amenity.icon_url" :alt="amenity.name" class="h-6 w-6 object-contain">
                                <svg v-else class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span class="text-sm font-bold text-main-text dark:text-white">{{ amenity.name }}</span>
                            </div>
                        </div>
                        <div v-else class="py-10 text-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl">
                            <p class="text-sm font-bold text-slate-400">{{ L.show.labels.noAmenities }}</p>
                        </div>
                    </div>

                    <div class="app-card !p-8 !rounded-[2rem]">
                        <h2 class="admin-index-title !text-2xl mb-6">Đánh giá từ khách lưu trú</h2>

                        <div class="grid gap-4 sm:grid-cols-2 mb-6">
                            <div v-for="review in reviewsData" :key="review.id" class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-dark-border">
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <p class="font-black text-main-text dark:text-white">{{ review.customer_name }}</p>
                                    <div class="flex items-center gap-1">
                                        <RatingStars :value="Number(review.rating)" size-class="w-3 h-3" />
                                    </div>
                                </div>
                                <p class="text-sm text-desc leading-relaxed line-clamp-3 italic">"{{ review.comment || 'Khách không để lại nhận xét.' }}"</p>
                                <p class="mt-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ review.created_at }}</p>
                            </div>
                        </div>

                        <div v-if="!reviewsData.length" class="py-10 text-center border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl">
                            <p class="text-sm font-bold text-slate-400">Chưa có đánh giá nào cho hạng phòng này.</p>
                        </div>

                        <div v-if="recentReviews.links?.length" class="index-pagination">
                            <Pagination :links="recentReviews.links" />
                        </div>
                    </div>

                    <div v-if="relatedRooms.length" class="pt-8 border-t border-slate-100 dark:border-dark-border">
                        <span class="admin-index-subtitle block mb-2">{{ L.show.sections.relatedKicker }}</span>
                        <h2 class="admin-index-title !text-2xl mb-6">{{ L.show.sections.relatedTitle }}</h2>

                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <Link
                                v-for="item in relatedRooms"
                                :key="item.id"
                                :href="item.is_bookable ? route('client.rooms.show', { room: item.id, check_in: bookingForm.check_in, check_out: bookingForm.check_out, guests: bookingForm.guests }) : '#'"
                                class="group block"
                                :class="item.is_bookable ? '' : 'opacity-50 saturate-50 cursor-not-allowed pointer-events-none'"
                            >
                                <div class="h-40 rounded-2xl overflow-hidden mb-3 relative">
                                    <img v-if="getRoomImage(item)" :src="getRoomImage(item)" :alt="item.name" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                                    <div class="absolute bottom-3 left-3 right-3 flex justify-between items-end">
                                        <span class="text-white font-black text-sm line-clamp-1">{{ item.name }}</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-xs font-bold text-slate-500">{{ item.type?.name }}</span>
                                    <span class="text-sm font-black text-primary-500">{{ formatCurrency(item.base_price) }}</span>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>

                <div class="lg:sticky lg:top-28">
                    <div class="app-card !p-6 md:!p-8 !rounded-[2rem] border-2 border-primary-500/20 shadow-2xl shadow-primary-500/10">
                        <div class="mb-6 pb-6 border-b border-slate-100 dark:border-dark-border">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ L.show.sections.bookingKicker }}</p>
                            <div class="flex items-end gap-1">
                                <p class="text-3xl font-black text-primary-500">{{ formatCurrency(room.base_price) }}</p>
                                <p class="text-xs font-bold text-slate-400 mb-1.5">/đêm</p>
                            </div>

                            <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-black uppercase tracking-widest"
                                :class="roomBookable ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'">
                                <span class="w-2 h-2 rounded-full" :class="roomBookable ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                                {{ roomBookable ? `Còn ${room.available_rooms_count || 0} phòng trống` : L.show.status.unavailableByDate }}
                            </div>
                        </div>

                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1 mb-1 block">{{ L.show.labels.checkIn }}</label>
                                <input v-model="bookingForm.check_in" type="date" class="form-input-pms w-full bg-slate-50 dark:bg-slate-800">
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1 mb-1 block">{{ L.show.labels.checkOut }}</label>
                                <input v-model="bookingForm.check_out" type="date" class="form-input-pms w-full bg-slate-50 dark:bg-slate-800">
                            </div>
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1 mb-1 block">{{ L.show.labels.guests }}</label>
                                <input v-model="bookingForm.guests" type="number" min="1" :max="maxGuests" class="form-input-pms w-full input-number-clean bg-slate-50 dark:bg-slate-800">
                                <p class="text-[10px] font-bold text-slate-400 mt-2 px-1">Sức chứa tối đa: {{ roomCapacityText }}</p>
                            </div>
                        </div>

                        <div v-if="totalNights > 0" class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-5 mb-6 border border-slate-100 dark:border-slate-700">
                            <div class="flex justify-between text-sm mb-3 font-bold text-slate-600 dark:text-slate-300">
                                <span>{{ formatCurrency(room.base_price) }} × {{ totalNights }} đêm</span>
                                <span>{{ formatCurrency(totalPrice) }}</span>
                            </div>
                            <div class="flex justify-between pt-3 border-t border-slate-200 dark:border-slate-700">
                                <span class="font-black text-main-text dark:text-white uppercase tracking-widest text-xs mt-1">Tổng cộng</span>
                                <span class="font-black text-xl text-primary-500">{{ formatCurrency(totalPrice) }}</span>
                            </div>
                        </div>

                        <div v-if="formError" class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-bold text-rose-600 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-400 flex items-start gap-2">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            {{ formError }}
                        </div>

                        <button
                            @click="proceedToCheckout"
                            :disabled="!roomBookable"
                            class="btn-primary !w-full !py-4 !text-sm uppercase tracking-widest shadow-xl shadow-primary-500/30 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:shadow-none"
                        >
                            {{ roomBookable ? L.show.labels.proceed : L.show.labels.temporarySoldOut }}
                        </button>

                        <p class="text-center text-[10px] font-bold text-slate-400 mt-4">Bạn sẽ không bị trừ tiền ngay lúc này.</p>
                    </div>
                </div>

            </div>
        </section>
    </ClientLayout>
</template>
