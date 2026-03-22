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
        <div class="max-w-6xl mx-auto space-y-10 pb-20">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="text-[10px] font-black text-primary-500 uppercase tracking-[0.3em] mb-2 block italic">Kiến tạo không gian</span>
                    <h2 class="text-3xl font-black text-main-text dark:text-white tracking-tight italic uppercase">Tạo Định nghĩa Phòng</h2>
                </div>
                <Link :href="route('admin.room-definitions.index')" class="text-sm font-bold text-muted-text hover:text-rose-500 transition-colors flex items-center gap-2 mb-1 italic">
                    ✕ Hủy bỏ
                </Link>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <div class="lg:col-span-5 app-card !p-8 md:!p-10 !rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-dark-border space-y-8 relative overflow-hidden bg-white dark:bg-dark-card">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>
                    
                    <h3 class="text-[11px] font-black text-primary-500 uppercase tracking-widest px-1 italic border-l-4 border-primary-500 pl-3">1. Thông số cốt lõi</h3>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Tên hạng phòng *</label>
                            <input v-model="form.name" type="text" placeholder="VD: VIP Single Suite..." class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white rounded-2xl py-4 px-6 text-main-text font-bold transition-all outline-none">
                            <p v-if="form.errors.name" class="text-rose-500 text-[10px] font-bold px-1 italic uppercase">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Danh mục *</label>
                                <select v-model="form.room_category_id" class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white rounded-2xl py-4 px-4 text-xs font-bold transition-all outline-none appearance-none">
                                    <option value="" disabled>Chọn danh mục</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Loại phòng *</label>
                                <select v-model="form.room_type_id" class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white rounded-2xl py-4 px-4 text-xs font-bold transition-all outline-none appearance-none">
                                    <option value="" disabled>Chọn loại</option>
                                    <option v-for="type in roomTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Giá cơ bản (VND/Đêm) *</label>
                            <input v-model="form.base_price" type="number" class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white rounded-2xl py-4 px-6 text-emerald-600 text-xl font-black transition-all outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Diện tích (m²)</label>
                                <input v-model="form.area" type="number" class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white rounded-2xl py-4 px-6 font-bold transition-all outline-none">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Hướng nhìn</label>
                                <input v-model="form.view" type="text" placeholder="Hướng biển..." class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-primary-500/20 focus:bg-white rounded-2xl py-4 px-6 font-bold transition-all outline-none">
                            </div>
                        </div>

                        <div class="space-y-2 pt-4">
                            <label class="text-[11px] font-black text-primary-500 uppercase tracking-widest px-1 italic">Hình ảnh đại diện</label>
                            <div class="relative group cursor-pointer">
                                <input type="file" @change="handleFileSelect" multiple class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                <div class="border-2 border-dashed border-slate-200 p-10 rounded-[2rem] flex flex-col items-center justify-center group-hover:bg-slate-50 transition-all">
                                    <span class="text-[10px] font-black uppercase text-slate-400">Tải ảnh lên tại đây</span>
                                    <span v-if="form.images.length" class="text-xs font-black text-primary-500 mt-2 italic uppercase">Đã chọn {{ form.images.length }} file</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-8">
                    <div class="app-card !p-8 md:!p-10 !rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-dark-border relative overflow-hidden bg-white dark:bg-dark-card min-h-[500px]">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>
                        
                        <div class="flex justify-between items-center mb-10">
                            <h3 class="text-[11px] font-black text-emerald-500 uppercase tracking-widest px-1 italic border-l-4 border-emerald-500 pl-3">2. Tiện nghi đẳng cấp</h3>
                            <span class="text-[10px] font-black px-4 py-2 bg-emerald-50 text-emerald-600 rounded-full italic shadow-sm">Đã chọn: {{ form.amenity_ids.length }}</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                            <label v-for="amenity in amenities" :key="amenity.id" 
                                class="relative flex flex-col items-center p-6 rounded-[2.5rem] border-2 border-slate-50 cursor-pointer transition-all hover:scale-105"
                                :class="{ 'border-emerald-500 bg-emerald-50/50 shadow-lg shadow-emerald-500/10': form.amenity_ids.includes(amenity.id) }">
                                
                                <input type="checkbox" :value="amenity.id" v-model="form.amenity_ids" class="hidden">
                                
                                <div class="w-16 h-16 rounded-2xl overflow-hidden mb-4 shadow-md bg-white">
                                    <img :src="amenity.icon_url" class="w-full h-full object-cover p-2">
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-tight text-center leading-tight">{{ amenity.name }}</span>
                                
                                <div v-if="form.amenity_ids.includes(amenity.id)" class="absolute -top-2 -right-2 bg-emerald-500 text-white w-7 h-7 rounded-full flex items-center justify-center text-[10px] shadow-lg ring-4 ring-white">✓</div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full btn-primary !py-6 !rounded-[2rem] shadow-2xl shadow-primary-500/30 font-black uppercase tracking-[0.2em] text-sm italic transition-all active:scale-95">
                        {{ form.processing ? 'Hệ thống đang xử lý...' : 'Kích hoạt định nghĩa hạng phòng' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>