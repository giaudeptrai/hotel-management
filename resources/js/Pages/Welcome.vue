<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import RatingStars from '@/Components/Client/RatingStars.vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    featuredRooms: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    roomTypes: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value || 0);

const getCapacityData = (room) => {
    const adult = Number(room?.type?.capacity_adult ?? 0);
    const child = Number(room?.type?.capacity_child ?? 0);
    const total = adult + child;

    return {
        adult,
        child,
        total: total > 0 ? total : null,
    };
};

const getCapacityText = (room) => {
    const capacity = getCapacityData(room);

    if (!capacity.total) {
        return 'Chưa cấu hình sức chứa';
    }

    if (capacity.child > 0) {
        return `${capacity.adult} người lớn, ${capacity.child} trẻ em`;
    }

    return `${capacity.adult} người lớn`;
};

const getRoomTypeName = (room) => room?.type?.name || 'Chưa gán loại phòng';
const getCategoryName = (room) => room?.category?.name || 'Chưa gán danh mục';

const getRoomImage = (room) => {
    const imageUrls = Array.isArray(room?.image_urls) ? room.image_urls : [];
    if (imageUrls.length > 0) {
        return imageUrls[0];
    }

    const rawImages = Array.isArray(room?.images) ? room.images : [];
    if (rawImages.length > 0) {
        return rawImages[0];
    }

    return 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=1200&auto=format&fit=crop';
};

const searchForm = useForm({
    check_in: '',
    check_out: '',
    guests: 1,
});

const submitSearch = () => {
    router.get(route('client.rooms.index'), {
        check_in: searchForm.check_in,
        check_out: searchForm.check_out,
        guests: searchForm.guests,
    });
};

const getReviewScore = (room) => Number(room?.reviews_avg_rating || 0).toFixed(1);

const trustPoints = [
    { label: 'Xác nhận tức thì', desc: 'Giữ phòng ngay sau khi gửi yêu cầu hợp lệ.' },
    { label: 'Giá minh bạch', desc: 'Hiển thị đầy đủ giá theo đêm và điều kiện lưu trú.' },
    { label: 'Hỗ trợ 24/7', desc: 'Lễ tân luôn sẵn sàng tư vấn trước và trong chuyến đi.' },
];

const steps = [
    { title: 'Chọn ngày lưu trú', desc: 'Nhập ngày nhận, ngày trả và số khách để lọc phòng phù hợp.' },
    { title: 'So sánh và chốt phòng', desc: 'Xem hình ảnh, sức chứa, đánh giá và chọn hạng phòng đúng nhu cầu.' },
    { title: 'Gửi yêu cầu đặt phòng', desc: 'Xác nhận thông tin, nhận phản hồi nhanh từ đội ngũ Dasher Hotel.' },
];
</script>

<template>
    <Head title="Trang Chủ - Dasher Hotel" />

    <ClientLayout>
        <section class="relative overflow-hidden bg-slate-900 pt-32 pb-20 lg:pt-40">
            <div class="absolute inset-0">
                <img src="https://images.pexels.com/photos/271624/pexels-photo-271624.jpeg?auto=compress&cs=tinysrgb&w=2200" alt="Dasher Hotel" class="h-full w-full object-cover opacity-45" />
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-900/70 to-slate-900/45"></div>
            </div>

            <div class="relative z-10 mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8">
                <div class="text-white">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-5 py-2 text-[11px] font-black uppercase tracking-[0.2em] backdrop-blur">
                        Dasher Signature Stay
                    </span>
                    <h1 class="mt-6 max-w-3xl text-4xl font-black leading-tight sm:text-5xl lg:text-6xl">
                        Chốt phòng đẹp trong 60 giây,
                        <span class="text-primary-400">nghỉ dưỡng đúng kỳ vọng.</span>
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200">
                        Không cần nhắn tin lòng vòng. Chọn ngày, lọc đúng nhu cầu, xem phòng thật, đặt ngay với mức giá rõ ràng và hỗ trợ lễ tân 24/7.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <span class="rounded-xl bg-emerald-500/20 px-4 py-2 text-xs font-black uppercase tracking-widest text-emerald-200">Xác nhận nhanh</span>
                        <span class="rounded-xl bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-widest">Miễn phí tư vấn lịch trình</span>
                        <span class="rounded-xl bg-white/10 px-4 py-2 text-xs font-black uppercase tracking-widest">Không phí ẩn</span>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <Link :href="route('client.rooms.index')" class="rounded-xl bg-primary-500 px-8 py-4 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-primary-500/30 transition hover:bg-primary-600">
                            Đặt Phòng Ngay
                        </Link>
                        <a href="tel:0792008096" class="rounded-xl border border-white/30 bg-white/10 px-8 py-4 text-sm font-black uppercase tracking-widest text-white backdrop-blur transition hover:bg-white/20">
                            Hotline 0792 008 096
                        </a>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/15 bg-white/95 p-6 shadow-2xl backdrop-blur dark:border-slate-700 dark:bg-slate-900/95">
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-primary-500">Tìm phòng phù hợp ngay</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-900 dark:text-white">Kiểm tra phòng trống theo lịch của bạn</h2>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Nhập ngày và số khách để nhận danh sách phòng khả dụng nhanh nhất.</p>

                    <div class="mt-6 space-y-4">
                        <div class="space-y-2">
                            <label class="pl-1 text-[10px] font-black uppercase tracking-widest text-slate-500">Ngày nhận phòng</label>
                            <input v-model="searchForm.check_in" type="date" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        </div>
                        <div class="space-y-2">
                            <label class="pl-1 text-[10px] font-black uppercase tracking-widest text-slate-500">Ngày trả phòng</label>
                            <input v-model="searchForm.check_out" type="date" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        </div>
                        <div class="space-y-2">
                            <label class="pl-1 text-[10px] font-black uppercase tracking-widest text-slate-500">Số khách</label>
                            <input v-model="searchForm.guests" type="number" min="1" class="input-number-clean w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-primary-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        </div>
                    </div>

                    <button @click="submitSearch" class="mt-6 w-full rounded-xl bg-slate-900 px-6 py-3.5 text-sm font-black uppercase tracking-widest text-white transition hover:bg-slate-800 dark:bg-primary-500 dark:hover:bg-primary-600">
                        Xem Phòng Trống
                    </button>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm dark:border-dark-border dark:bg-dark-card">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Hạng phòng</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ stats.room_definitions || 0 }}</p>
                </article>
                <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm dark:border-dark-border dark:bg-dark-card">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Phòng đang hoạt động</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ stats.active_rooms || 0 }}</p>
                </article>
                <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm dark:border-dark-border dark:bg-dark-card">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Loại phòng</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ stats.room_types || 0 }}</p>
                </article>
                <article class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm dark:border-dark-border dark:bg-dark-card">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500">Danh mục lưu trú</p>
                    <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ stats.room_categories || 0 }}</p>
                </article>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <span class="text-sm font-black text-primary-500 uppercase tracking-widest block mb-2">Best Seller</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white mb-4">Những phòng được đặt nhanh nhất tuần này</h2>
                    <p class="text-slate-500 dark:text-slate-400">Xem nhanh các lựa chọn được khách yêu thích để tiết kiệm thời gian so sánh và chốt phòng sớm.</p>
                </div>
                <Link :href="route('client.rooms.index')" class="text-primary-500 font-bold hover:text-primary-600 flex items-center gap-1">
                    Xem tất cả phòng <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </Link>
            </div>


            <div v-if="featuredRooms && featuredRooms.length > 0" class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                <article v-for="room in featuredRooms" :key="room.id" class="bg-white dark:bg-dark-card rounded-3xl border border-slate-100 dark:border-dark-border overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group flex flex-col">
                    <div class="relative h-64 overflow-hidden">
                        <img :src="getRoomImage(room)" :alt="room.name" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute top-4 left-4 flex gap-2">
                            <span class="bg-white/90 backdrop-blur text-slate-900 px-3 py-1.5 rounded-lg text-xs font-black shadow-sm">{{ getCategoryName(room) }}</span>
                            <span class="bg-slate-900/85 text-white px-3 py-1.5 rounded-lg text-xs font-black shadow-sm">{{ getRoomTypeName(room) }}</span>
                        </div>
                        <div class="absolute right-4 bottom-4 rounded-lg bg-emerald-500/90 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-white">
                            Còn {{ room.rooms_count || 0 }} phòng
                        </div>
                    </div>

                    <div class="p-6 flex flex-col flex-1">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white line-clamp-1" :title="room.name">{{ room.name }}</h3>
                            <div class="flex items-center gap-1 bg-amber-50 dark:bg-amber-500/10 text-amber-600 px-2 py-1 rounded border border-amber-100 dark:border-amber-500/20">
                                <RatingStars :value="Number(room.reviews_avg_rating || 0)" size-class="h-3 w-3" />
                                <span class="text-xs font-bold">{{ getReviewScore(room) }}</span>
                            </div>
                        </div>

                        <p class="text-sm font-medium text-slate-500 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            {{ getCapacityText(room) }}
                        </p>

                        <p class="text-slate-500 dark:text-slate-400 text-sm line-clamp-2 mb-6 flex-1">
                            {{ room.description || 'Không gian nghỉ dưỡng được thiết kế hoàn hảo để mang lại sự thoải mái tuyệt đối.' }}
                        </p>

                        <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800 mt-auto">
                            <div>
                                <span class="text-xs text-slate-400 block">Giá mỗi đêm từ</span>
                                <span class="text-lg font-black text-primary-500">{{ formatCurrency(room.base_price) }}</span>
                            </div>
                            <Link :href="route('client.rooms.show', { room: room.id, guests: searchForm.guests })" class="px-5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-bold hover:bg-primary-500 hover:text-white transition-colors">
                                Đặt phòng
                            </Link>
                        </div>
                    </div>
                </article>
            </div>

            <div v-else class="py-20 text-center bg-slate-50 dark:bg-dark-card rounded-3xl border border-dashed border-slate-200 dark:border-dark-border">
                <p class="text-xl font-bold text-slate-400">Danh sách phòng nổi bật đang được cập nhật.</p>
            </div>
        </section>

        <section class="bg-slate-50 py-20 border-y border-slate-100 dark:border-dark-border dark:bg-slate-900/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-14">
                    <span class="text-sm font-black text-primary-500 uppercase tracking-widest block mb-2">Cam kết dịch vụ</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white mb-4">Lý do khách quay lại Dasher Hotel</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="point in trustPoints" :key="point.label" class="bg-white dark:bg-dark-card p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-dark-border flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ point.label }}</h3>
                            <p class="text-sm text-slate-500 mt-1">{{ point.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <span class="text-sm font-black text-primary-500 uppercase tracking-widest block mb-2">Quy trình đặt phòng</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white">3 bước để chốt phòng nhanh</h2>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <article v-for="(step, index) in steps" :key="step.title" class="rounded-3xl border border-slate-100 bg-white p-7 shadow-sm dark:border-dark-border dark:bg-dark-card">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-black text-white dark:bg-primary-500">{{ index + 1 }}</span>
                    <h3 class="mt-4 text-xl font-black text-slate-900 dark:text-white">{{ step.title }}</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-500 dark:text-slate-400">{{ step.desc }}</p>
                </article>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-[3rem] bg-slate-900 p-10 text-center shadow-2xl sm:p-16">
                <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
                <div class="relative z-10 max-w-3xl mx-auto space-y-6 text-white">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary-400 block">Ưu đãi đặt sớm</span>
                    <h2 class="text-3xl sm:text-5xl font-extrabold">Giữ giá tốt hôm nay cho kỳ nghỉ của bạn.</h2>
                    <p class="text-lg leading-8 text-slate-300">
                        Phòng đẹp thường được chốt sớm vào cuối tuần và dịp lễ. Đặt ngay để giữ lựa chọn tốt nhất và nhận hỗ trợ xác nhận nhanh từ đội ngũ Dasher Hotel.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4">
                        <Link :href="route('client.rooms.index')" class="rounded-xl bg-primary-500 px-8 py-4 font-bold text-white transition-all shadow-lg shadow-primary-500/30 hover:bg-primary-600">
                            Đặt phòng ngay
                        </Link>
                        <a href="tel:0792008096" class="rounded-xl bg-white px-8 py-4 font-bold text-slate-900 transition-all hover:bg-slate-100">Gọi lễ tân: 0792 008 096</a>
                    </div>
                </div>
            </div>
        </section>
    </ClientLayout>
</template>
