<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    definition: Object,
    categories: Array,
    roomTypes: Array,
    amenities: Array
});

const form = useForm({
    _method: 'PUT',
    name: props.definition.name,
    room_category_id: props.definition.room_category_id,
    room_type_id: props.definition.room_type_id,
    base_price: props.definition.base_price,
    area: props.definition.area,
    view: props.definition.view,
    images: [], 
    amenity_ids: props.definition.amenities.map(a => a.id),
});

const handleFileSelect = (e) => {
    form.images = Array.from(e.target.files);
};

const submit = () => {
    form.post(route('admin.room-definitions.update', props.definition.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Hiệu chỉnh Hạng phòng" />
    <AdminLayout>
        <div class="max-w-6xl mx-auto space-y-10 pb-20">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="text-[10px] font-black text-blue-500 uppercase tracking-[0.3em] mb-2 block italic tracking-tighter text-blue-500">Hệ thống quản trị</span>
                    <h2 class="text-3xl font-black text-main-text dark:text-white tracking-tight italic uppercase">Hiệu chỉnh: {{ definition.name }}</h2>
                </div>
                <Link :href="route('admin.room-definitions.index')" class="text-sm font-bold text-muted-text hover:text-rose-500 transition-colors flex items-center gap-2 mb-1 italic">
                    ✕ Hủy thay đổi
                </Link>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <div class="lg:col-span-5 space-y-8">
                    <div class="app-card !p-8 md:!p-10 !rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-dark-border space-y-8 relative overflow-hidden bg-white dark:bg-dark-card">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>
                        <h3 class="text-[11px] font-black text-blue-500 uppercase tracking-widest px-1 italic border-l-4 border-blue-500 pl-3">Thông số hiệu chỉnh</h3>
                        
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Tên hạng phòng *</label>
                                <input v-model="form.name" type="text" class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-blue-500/20 focus:bg-white rounded-2xl py-4 px-6 text-main-text font-bold transition-all outline-none">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic text-[9px]">Danh mục</label>
                                    <select v-model="form.room_category_id" class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent rounded-2xl py-4 px-4 text-xs font-bold transition-all outline-none appearance-none">
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic text-[9px]">Loại phòng</label>
                                    <select v-model="form.room_type_id" class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent rounded-2xl py-4 px-4 text-xs font-bold transition-all outline-none appearance-none">
                                        <option v-for="type in roomTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic text-slate-500">Giá cơ bản (VND)</label>
                                <input v-model="form.base_price" type="number" class="w-full bg-slate-50 dark:bg-dark-bg border-2 border-transparent focus:border-blue-500/20 rounded-2xl py-4 px-6 text-blue-600 font-black text-xl transition-all outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="app-card !p-8 !rounded-[2.5rem] shadow-xl border border-slate-100 bg-white space-y-6 relative overflow-hidden">
                        <h3 class="text-[11px] font-black text-rose-500 uppercase tracking-widest px-1 italic border-l-4 border-rose-500 pl-3">Thư viện hình ảnh</h3>
                        
                        <div class="grid grid-cols-3 gap-3">
                            <div v-for="(url, idx) in definition.image_urls" :key="idx" class="aspect-video rounded-2xl overflow-hidden shadow-sm border border-slate-100 bg-slate-50">
                                <img :src="url" class="w-full h-full object-cover">
                            </div>
                        </div>
                        
                        <div class="pt-6 border-t border-slate-50 space-y-4">
                            <label class="text-[11px] font-black uppercase text-rose-500 italic block px-1 tracking-tighter">Cập nhật thư viện ảnh (Optional)</label>
                            
                            <div class="relative group cursor-pointer">
                                <input type="file" @change="handleFileSelect" multiple class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                <div class="border-2 border-dashed border-slate-200 p-10 rounded-[2.5rem] flex flex-col items-center justify-center group-hover:bg-slate-50 group-hover:border-rose-500 transition-all bg-white">
                                    <svg class="w-8 h-8 text-slate-300 mb-3 group-hover:text-rose-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path></svg>
                                    <span class="text-[10px] font-black uppercase text-slate-400">Thả ảnh mới vào để thay thế</span>
                                    <span v-if="form.images.length" class="text-xs font-black text-rose-500 mt-2 italic uppercase">Đã chọn {{ form.images.length }} file mới</span>
                                </div>
                            </div>
                            <p class="text-[9px] text-slate-400 mt-2 italic font-bold leading-tight">* Lưu ý: Upload ảnh mới sẽ thay thế toàn bộ ảnh hiện tại của hạng phòng này.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-8">
                    <div class="app-card !p-8 md:!p-10 !rounded-[2.5rem] shadow-xl border border-slate-100 bg-white relative overflow-hidden min-h-[500px]">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>
                        <div class="flex justify-between items-center mb-10">
                            <h3 class="text-[11px] font-black text-emerald-500 uppercase tracking-widest px-1 italic border-l-4 border-emerald-500 pl-3">Đặc quyền tiện ích</h3>
                            <span class="text-[10px] font-black px-4 py-2 bg-emerald-50 text-emerald-600 rounded-full border border-emerald-100 italic shadow-sm">Chọn lại: {{ form.amenity_ids.length }}</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                            <label v-for="amenity in amenities" :key="amenity.id" 
                                class="relative flex flex-col items-center p-6 rounded-[2.5rem] border-2 border-slate-50 cursor-pointer transition-all hover:bg-slate-50 group"
                                :class="{ 'border-emerald-500 bg-emerald-50/50 shadow-lg shadow-emerald-500/10': form.amenity_ids.includes(amenity.id) }">
                                
                                <input type="checkbox" :value="amenity.id" v-model="form.amenity_ids" class="hidden">
                                <div class="w-16 h-16 rounded-2xl overflow-hidden mb-4 shadow-md bg-white">
                                    <img :src="amenity.icon_url" class="w-full h-full object-cover p-2">
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-tight text-center leading-tight">{{ amenity.name }}</span>
                                <div v-if="form.amenity_ids.includes(amenity.id)" class="absolute -top-2 -right-2 bg-emerald-500 text-white w-7 h-7 rounded-full flex items-center justify-center text-[8px] shadow-lg ring-4 ring-white">✓</div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full btn-primary !bg-blue-600 !py-6 !rounded-[2.5rem] shadow-2xl shadow-blue-500/30 font-black uppercase tracking-[0.2em] text-sm italic transition-all active:scale-95">
                        {{ form.processing ? 'Hệ thống đang lưu...' : 'Lưu mọi thay đổi ngay' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>