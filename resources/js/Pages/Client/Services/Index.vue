<script setup>
import { computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    services: { type: Object, default: () => ({ data: [], links: [] }) },
    types: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    summary: { type: Object, default: () => ({ total: 0, avg_price: 0, types: 0 }) },
});

const filterForm = useForm({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    sort: props.filters?.sort || 'featured',
});

const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(Number(value || 0));

const serviceTypeLabel = (type) => {
    const normalized = String(type || '').toLowerCase();

    if (['food', 'meal', 'restaurant'].includes(normalized)) return 'Ẩm thực';
    if (['drink', 'beverage', 'bar'].includes(normalized)) return 'Thức uống';
    if (['spa', 'wellness'].includes(normalized)) return 'Spa & Wellness';
    if (['transport', 'shuttle'].includes(normalized)) return 'Di chuyển';

    return type || 'Dịch vụ khác';
};

const serviceIcon = (type) => {
    const normalized = String(type || '').toLowerCase();

    if (['food', 'meal', 'restaurant'].includes(normalized)) {
        return 'M12 8c-1.657 0-3 1.343-3 3v8a1 1 0 002 0v-3h2v3a1 1 0 002 0v-8c0-1.657-1.343-3-3-3zM6 5a1 1 0 00-1 1v5a3 3 0 002 2.83V19a1 1 0 002 0v-5.17A3 3 0 0011 11V6a1 1 0 10-2 0v5a1 1 0 11-2 0V6a1 1 0 00-1-1z';
    }

    if (['drink', 'beverage', 'bar'].includes(normalized)) {
        return 'M7 2a1 1 0 00-.894 1.447L9 9.236V19a1 1 0 102 0V9.236l2.894-5.789A1 1 0 0013 2H7zM15 6a1 1 0 000 2h1v11a1 1 0 102 0V8h1a1 1 0 100-2h-4z';
    }

    if (['spa', 'wellness'].includes(normalized)) {
        return 'M12 2c3.866 0 7 3.134 7 7 0 5.25-7 13-7 13S5 14.25 5 9c0-3.866 3.134-7 7-7zm0 4a3 3 0 100 6 3 3 0 000-6z';
    }

    if (['transport', 'shuttle'].includes(normalized)) {
        return 'M4 6a2 2 0 012-2h12a2 2 0 012 2v9a2 2 0 01-2 2h-1a2 2 0 11-4 0H11a2 2 0 11-4 0H6a2 2 0 01-2-2V6zm3 9a1 1 0 100 2 1 1 0 000-2zm10 0a1 1 0 100 2 1 1 0 000-2zM6 8v3h12V8H6z';
    }

    return 'M5 4a2 2 0 00-2 2v12a2 2 0 002 2h14a2 2 0 002-2V9l-5-5H5zm7 1.5L18.5 12H13a1 1 0 01-1-1V5.5z';
};

const hasFilters = computed(() => Boolean(filterForm.search || filterForm.type || (filterForm.sort && filterForm.sort !== 'featured')));

const applyFilters = () => {
    router.get(route('client.services.index'), {
        search: filterForm.search || undefined,
        type: filterForm.type || undefined,
        sort: filterForm.sort || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    filterForm.search = '';
    filterForm.type = '';
    filterForm.sort = 'featured';
    applyFilters();
};
</script>

<template>
    <Head title="Dịch vụ khách sạn - Dasher Hotel" />

    <ClientLayout>
        <section class="relative bg-slate-900 pb-32 pt-32 lg:pt-40 lg:pb-40">
            <div class="absolute inset-0">
                <img src="https://images.pexels.com/photos/338504/pexels-photo-338504.jpeg?auto=compress&cs=tinysrgb&w=2200" alt="Dịch vụ Dasher Hotel" class="h-full w-full object-cover opacity-40" />
                <div class="absolute inset-0 bg-slate-900/60"></div>
            </div>

            <div class="relative z-10 mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
                <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-white backdrop-blur mb-4">
                    Trải nghiệm trọn vẹn tại Dasher
                </span>
                <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl mb-6">
                    Dịch vụ tiện nghi <br class="hidden sm:block" /> cho từng khoảnh khắc
                </h1>
                <p class="text-lg text-slate-300 max-w-2xl mx-auto">
                    Từ ẩm thực, thư giãn đến các tiện ích đưa đón, mọi dịch vụ đều được tinh chỉnh để kỳ nghỉ của bạn trở nên hoàn hảo nhất.
                </p>
            </div>
        </section>

        <section class="relative z-20 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 -mt-20">
            <div class="app-card !p-6 md:!p-8 !rounded-[2rem] shadow-2xl border border-slate-100 dark:border-dark-border bg-white dark:bg-dark-card">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto] items-end">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">Tìm dịch vụ</label>
                        <input v-model="filterForm.search" type="text" placeholder="Vd: buffet, spa..." class="form-input-pms form-input-pms-compact w-full" @keyup.enter="applyFilters">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">Nhóm dịch vụ</label>
                        <select v-model="filterForm.type" class="form-input-pms form-input-pms-compact w-full cursor-pointer" @change="applyFilters">
                            <option value="">Tất cả nhóm</option>
                            <option v-for="type in types" :key="type" :value="type">{{ serviceTypeLabel(type) }}</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-500 pl-1">Sắp xếp</label>
                        <select v-model="filterForm.sort" class="form-input-pms form-input-pms-compact w-full cursor-pointer" @change="applyFilters">
                            <option value="featured">Mới cập nhật</option>
                            <option value="price_asc">Giá tăng dần</option>
                            <option value="price_desc">Giá giảm dần</option>
                            <option value="name_asc">Tên A-Z</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 h-[42px] mt-4 lg:mt-0">
                        <button @click="applyFilters" class="btn-primary !px-6 !py-0 h-full w-full lg:w-auto flex items-center justify-center gap-2">
                            Lọc ngay
                        </button>
                        <button v-if="hasFilters" @click="resetFilters" class="h-full px-4 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white dark:bg-rose-500/10 dark:hover:bg-rose-500 transition-colors font-bold text-xs whitespace-nowrap">
                            Xóa lọc
                        </button>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-dark-border flex flex-wrap gap-3">
                    <span class="px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 text-[10px] font-black uppercase tracking-widest text-slate-500 border border-slate-200 dark:border-slate-700">Tổng: {{ summary.total || 0 }} dịch vụ</span>
                    <span class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-[10px] font-black uppercase tracking-widest text-emerald-600 border border-emerald-200 dark:border-emerald-500/30">{{ summary.types || 0 }} Nhóm phân loại</span>
                    <span class="px-3 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-[10px] font-black uppercase tracking-widest text-amber-600 border border-amber-200 dark:border-amber-500/30">Giá TB: {{ formatCurrency(summary.avg_price || 0) }}</span>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div v-if="services?.data?.length" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="service in services.data"
                    :key="service.id"
                    class="bg-white dark:bg-dark-card rounded-[2rem] border border-slate-100 dark:border-dark-border shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col group p-6"
                >
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-primary-500/10 text-primary-500 group-hover:bg-primary-500 group-hover:text-white transition-colors duration-300 flex items-center justify-center">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8">
                                <path :d="serviceIcon(service.type)" />
                            </svg>
                        </div>
                        <span class="px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800 text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 border border-slate-100 dark:border-slate-700">
                            {{ serviceTypeLabel(service.type) }}
                        </span>
                    </div>

                    <h3 class="text-xl font-black text-main-text dark:text-white mb-2 line-clamp-2">
                        {{ service.name }}
                    </h3>
                    <p class="text-sm font-bold text-slate-400 mb-6 flex-1">
                        Cung cấp theo: <span class="text-main-text dark:text-slate-300 uppercase tracking-wider text-xs ml-1">{{ service.unit }}</span>
                    </p>

                    <div class="mt-auto pt-6 border-t border-slate-100 dark:border-dark-border flex items-end justify-between">
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-1">Mức giá tham khảo</span>
                            <span class="text-2xl font-black italic text-primary-500">{{ formatCurrency(service.price) }}</span>
                        </div>

                        <Link :href="route('client.rooms.index')" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 hover:bg-primary-500 hover:text-white transition-colors flex items-center justify-center" title="Đi đến Đặt phòng để gọi dịch vụ">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        </Link>
                    </div>
                </article>
            </div>

            <div v-else class="py-24 text-center bg-white dark:bg-dark-card rounded-[2.5rem] border-2 border-dashed border-slate-200 dark:border-dark-border">
                <svg class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
                <h3 class="text-2xl font-black text-main-text dark:text-white">Không tìm thấy dịch vụ</h3>
                <p class="text-desc mt-2 max-w-md mx-auto">Chưa có dịch vụ nào phù hợp với bộ lọc hiện tại. Hãy thử chọn nhóm khác hoặc xóa bộ lọc.</p>
                <button v-if="hasFilters" @click="resetFilters" class="mt-6 btn-primary !px-6 !py-3">Xóa bộ lọc</button>
            </div>

            <div v-if="services?.links?.length" class="mt-12 flex justify-center">
                <Pagination :links="services.links" />
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-[3rem] px-6 py-12 sm:px-12 sm:py-16 text-center text-white shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-primary-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10">
                    <span class="text-[10px] font-black uppercase tracking-widest text-primary-400 block mb-3">Tận hưởng kỳ nghỉ</span>
                    <h2 class="text-3xl md:text-5xl font-black tracking-tight mb-4">Sẵn sàng trải nghiệm dịch vụ?</h2>
                    <p class="mx-auto max-w-2xl text-sm font-medium text-slate-300 mb-8 leading-relaxed">
                        Bạn có thể gọi các dịch vụ này trực tiếp khi hoàn tất đặt phòng, hoặc liên hệ với bộ phận Lễ tân thông qua hệ thống POS trong suốt thời gian lưu trú tại khách sạn.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-4">
                        <Link :href="route('client.rooms.index')" class="btn-primary !px-8 !py-4 shadow-lg shadow-primary-500/30">
                            Đặt phòng ngay
                        </Link>
                        <Link :href="route('home')" class="px-8 py-4 rounded-xl border border-white/20 bg-white/5 hover:bg-white/10 text-white font-bold text-sm uppercase tracking-widest transition-colors backdrop-blur">
                            Về trang chủ
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </ClientLayout>
</template>
