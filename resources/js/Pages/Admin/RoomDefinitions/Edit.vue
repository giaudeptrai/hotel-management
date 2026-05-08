<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    definition: Object,
    categories: Array,
    roomTypes: Array,
    amenities: Array
});

const form = useForm({
    _method: 'PUT',
    name: props.definition.name || '',
    description: props.definition.description || '', // 🎯 Đã cập nhật biến description
    room_category_id: props.definition.room_category_id || '',
    room_type_id: props.definition.room_type_id || '',
    base_price: props.definition.base_price || '',
    area: props.definition.area || '',
    view: props.definition.view || '',
    existing_images: Array.isArray(props.definition.images) ? [...props.definition.images] : [],
    images: [],
    amenity_ids: props.definition.amenities ? props.definition.amenities.map(a => a.id) : [],
});

const newImagePreviews = ref([]);

const refreshNewImagePreviews = () => {
    newImagePreviews.value.forEach((url) => {
        if (url) {
            URL.revokeObjectURL(url);
        }
    });

    newImagePreviews.value = form.images.map((file) => URL.createObjectURL(file));
};

const handleFileSelect = (e) => {
    const selectedFiles = Array.from(e.target.files || []);
    form.images = [...form.images, ...selectedFiles];
    refreshNewImagePreviews();
    e.target.value = '';
};

const imagePreviewUrl = (path) => {
    if (!path) return '';
    if (String(path).startsWith('http')) return path;
    if (String(path).startsWith('/storage/')) return path;
    return `/storage/${path}`;
};

const removeExistingImage = (index) => {
    form.existing_images.splice(index, 1);
};

const removeNewImage = (index) => {
    form.images.splice(index, 1);
    refreshNewImagePreviews();
};

onBeforeUnmount(() => {
    newImagePreviews.value.forEach((url) => {
        if (url) {
            URL.revokeObjectURL(url);
        }
    });
});

const submit = () => {
    form.post(route('admin.room-definitions.update', props.definition.id), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Hiệu chỉnh Hạng phòng" />
    <AdminLayout>
        <div class="max-w-6xl mx-auto space-y-8 pb-12">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="admin-index-subtitle">Hệ thống quản trị</span>
                    <h2 class="admin-index-title">Hiệu chỉnh: {{ definition.name }}</h2>
                </div>
                <Link :href="route('admin.room-definitions.index')" class="admin-form-back-link italic">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                    Hủy thay đổi
                </Link>
            </div>

            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <div class="lg:col-span-5 space-y-8">
                    <div class="admin-form-card">
                        <h3 class="admin-index-subtitle !text-primary-500">Thông số hiệu chỉnh</h3>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Tên hạng phòng *</label>
                                <input v-model="form.name" type="text" class="form-input-pms">
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic">Mô tả phòng (Hiển thị Client)</label>
                                <textarea v-model="form.description" rows="4" placeholder="VD: View biển cực chill, bồn tắm sứ nhập khẩu..."
                                          class="form-input-pms custom-scrollbar"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic text-[9px]">Danh mục</label>
                                    <select v-model="form.room_category_id" class="form-input-pms text-xs cursor-pointer appearance-none">
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic text-[9px]">Loại phòng</label>
                                    <select v-model="form.room_type_id" class="form-input-pms text-xs cursor-pointer appearance-none">
                                        <option v-for="type in roomTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic text-slate-500">Giá cơ bản (VND)</label>
                                <input v-model="form.base_price" type="number" class="form-input-pms input-number-clean !text-primary-500 !text-xl !font-black">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic text-[9px]">Diện tích (m²)</label>
                                    <input v-model="form.area" type="number" class="form-input-pms input-number-clean">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1 italic text-[9px]">Hướng nhìn</label>
                                    <input v-model="form.view" type="text" class="form-input-pms">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-form-card !p-8 space-y-6">
                        <h3 class="admin-index-subtitle !text-primary-500">Thư viện hình ảnh</h3>

                        <div class="space-y-3">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 px-1 italic">Ảnh hiện tại</p>
                            <div v-if="form.existing_images.length" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <div v-for="(path, idx) in form.existing_images" :key="`existing-${idx}`" class="relative aspect-video rounded-2xl overflow-hidden shadow-sm border border-slate-100 bg-slate-50">
                                    <img :src="imagePreviewUrl(path)" class="w-full h-full object-cover">
                                    <button
                                        type="button"
                                        class="absolute top-2 right-2 rounded-full bg-rose-500 text-white w-7 h-7 text-xs font-black hover:bg-rose-600 transition-colors"
                                        @click="removeExistingImage(idx)"
                                    >
                                        ×
                                    </button>
                                </div>
                            </div>
                            <p v-else class="text-xs font-bold text-slate-400 italic">Chưa có ảnh hiện tại.</p>
                        </div>

                        <div class="pt-6 border-t border-slate-100 dark:border-dark-border space-y-4">
                            <label class="text-[11px] font-black uppercase text-primary-500 italic block px-1 tracking-tighter">Thêm ảnh mới (có thể chọn nhiều lần)</label>

                            <div class="relative group cursor-pointer">
                                <input type="file" @change="handleFileSelect" accept="image/*" multiple class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                <div class="border-2 border-dashed border-slate-200 dark:border-dark-border p-10 rounded-[2rem] flex flex-col items-center justify-center group-hover:bg-slate-50 dark:group-hover:bg-dark-bg group-hover:border-primary-500 transition-all bg-white dark:bg-dark-card">
                                    <svg class="w-8 h-8 text-slate-300 mb-3 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path></svg>
                                    <span class="text-[10px] font-black uppercase text-slate-400">Chọn hoặc thả nhiều ảnh vào đây</span>
                                    <span v-if="form.images.length" class="text-xs font-black text-primary-500 mt-2 italic uppercase">Đã chọn {{ form.images.length }} ảnh mới</span>
                                </div>
                            </div>

                            <div v-if="form.images.length" class="space-y-3">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 px-1 italic">Ảnh mới sẽ thêm vào</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <div v-for="(file, idx) in form.images" :key="`new-${idx}`" class="relative aspect-video rounded-2xl overflow-hidden shadow-sm border border-slate-100 bg-slate-50">
                                        <img :src="newImagePreviews[idx]" class="w-full h-full object-cover">
                                        <button
                                            type="button"
                                            class="absolute top-2 right-2 rounded-full bg-slate-900 text-white w-7 h-7 text-xs font-black hover:bg-black transition-colors"
                                            @click="removeNewImage(idx)"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <p class="text-[9px] text-slate-400 mt-2 italic font-bold leading-tight">* Bạn có thể xóa từng ảnh cũ hoặc thêm nhiều ảnh mới. Hệ thống sẽ giữ lại ảnh còn lại và thêm ảnh mới sau khi lưu.</p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-8">
                    <div class="admin-form-card min-h-[500px]">
                        <div class="flex justify-between items-center mb-10">
                            <h3 class="admin-index-subtitle !text-primary-500">Đặc quyền tiện ích</h3>
                            <span class="text-[10px] font-black px-4 py-2 bg-slate-100 text-slate-600 rounded-full border border-slate-200 dark:border-dark-border">Chọn lại: {{ form.amenity_ids.length }}</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6">
                            <label v-for="amenity in amenities" :key="amenity.id"
                                class="relative flex flex-col items-center p-6 rounded-[2rem] border-2 border-slate-100 dark:border-dark-border cursor-pointer transition-all hover:bg-slate-50 dark:hover:bg-dark-bg"
                                :class="{ 'border-primary-500 bg-primary-500/5': form.amenity_ids.includes(amenity.id) }">

                                <input type="checkbox" :value="amenity.id" v-model="form.amenity_ids" class="hidden">
                                <div class="w-16 h-16 rounded-2xl overflow-hidden mb-4 shadow-sm bg-white dark:bg-dark-bg">
                                    <img :src="amenity.icon_url" class="w-full h-full object-cover p-2">
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-tight text-center leading-tight">{{ amenity.name }}</span>

                                <div v-if="form.amenity_ids.includes(amenity.id)" class="absolute -top-2 -right-2 bg-primary-500 text-white w-7 h-7 rounded-full flex items-center justify-center text-[8px]">✓</div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="w-full btn-primary !py-6 !rounded-[2rem] font-black uppercase tracking-[0.2em] text-sm">
                        {{ form.processing ? 'Hệ thống đang lưu...' : 'Lưu mọi thay đổi ngay' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
