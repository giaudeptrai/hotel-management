<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    categories: Array,
    roomTypes: Array,
    amenities: Array
});

const form = useForm({
    name: '',
    description: '', // 🎯 Thêm biến description
    room_category_id: '',
    room_type_id: '',
    base_price: '',
    area: '',
    view: '',
    images: [],
    amenity_ids: [],
});

const handleFileSelect = (e) => {
    form.images = Array.from(e.target.files);
};

const submit = () => {
    form.post(route('admin.room-definitions.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Thêm Hạng phòng" />
    <AdminLayout>
        <div class="max-w-6xl mx-auto space-y-8 pb-12">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="admin-index-subtitle">Kiến tạo không gian</span>
                    <h2 class="admin-index-title">Tạo Định nghĩa Phòng</h2>
                </div>
                <Link :href="route('admin.room-definitions.index')" class="admin-form-back-link italic">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    Hủy bỏ
                </Link>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-5 admin-form-card">
                    <h3 class="admin-index-subtitle !text-primary-500">1. Thông số cốt lõi</h3>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Tên hạng phòng *</label>
                            <input v-model="form.name" type="text" placeholder="VD: VIP Single Suite..." class="form-input-pms">
                            <p v-if="form.errors.name" class="text-rose-500 text-[10px] font-bold px-1 italic uppercase">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Mô tả phòng (Hiển thị Client)</label>
                            <textarea v-model="form.description" rows="3" placeholder="Nhập mô tả hấp dẫn..." class="form-input-pms custom-scrollbar"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Danh mục *</label>
                                <select v-model="form.room_category_id" class="form-input-pms text-xs cursor-pointer appearance-none">
                                    <option value="" disabled>Chọn danh mục</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Loại phòng *</label>
                                <select v-model="form.room_type_id" class="form-input-pms text-xs cursor-pointer appearance-none">
                                    <option value="" disabled>Chọn loại</option>
                                    <option v-for="type in roomTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Giá cơ bản (VND/Đêm) *</label>
                            <input v-model="form.base_price" type="number" class="form-input-pms input-number-clean !text-primary-500 !text-xl !font-black">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Diện tích (m²)</label>
                                <input v-model="form.area" type="number" class="form-input-pms input-number-clean">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Hướng nhìn</label>
                                <input v-model="form.view" type="text" placeholder="Hướng biển..." class="form-input-pms">
                            </div>
                        </div>

                        <div class="space-y-2 pt-4">
                            <label class="text-[11px] font-black text-primary-500 uppercase tracking-widest px-1 italic">Hình ảnh đại diện</label>
                            <div class="relative group cursor-pointer">
                                <input type="file" @change="handleFileSelect" multiple class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                <div class="border-2 border-dashed border-slate-200 dark:border-dark-border p-10 rounded-[2rem] flex flex-col items-center justify-center bg-slate-50 dark:bg-dark-bg group-hover:bg-white dark:group-hover:bg-dark-card transition-all">
                                    <span class="text-[10px] font-black uppercase text-slate-400">Tải ảnh lên tại đây</span>
                                    <span v-if="form.images.length" class="text-xs font-black text-primary-500 mt-2 italic uppercase">Đã chọn {{ form.images.length }} file</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-8">
                    <div class="admin-form-card min-h-[500px]">
                        <div class="flex justify-between items-center mb-10">
                            <h3 class="admin-index-subtitle !text-primary-500">2. Tiện nghi đẳng cấp</h3>
                            <span class="text-[10px] font-black px-4 py-2 bg-slate-100 text-slate-600 rounded-full">Đã chọn: {{ form.amenity_ids.length }}</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                            <label v-for="amenity in amenities" :key="amenity.id"
                                class="relative flex flex-col items-center p-6 rounded-[2rem] border-2 border-slate-100 dark:border-dark-border cursor-pointer transition-all"
                                :class="{ 'border-primary-500 bg-primary-500/5': form.amenity_ids.includes(amenity.id) }">

                                <input type="checkbox" :value="amenity.id" v-model="form.amenity_ids" class="hidden">

                                <div class="w-16 h-16 rounded-2xl overflow-hidden mb-4 shadow-sm bg-white dark:bg-dark-bg">
                                    <img :src="amenity.icon_url" class="w-full h-full object-cover p-2">
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-tight text-center leading-tight">{{ amenity.name }}</span>

                                <div v-if="form.amenity_ids.includes(amenity.id)" class="absolute -top-2 -right-2 bg-primary-500 text-white w-7 h-7 rounded-full flex items-center justify-center text-[10px]">✓</div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full btn-primary !py-6 !rounded-[2rem] font-black uppercase tracking-[0.2em] text-sm">
                        {{ form.processing ? 'Hệ thống đang xử lý...' : 'Kích hoạt định nghĩa hạng phòng' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
