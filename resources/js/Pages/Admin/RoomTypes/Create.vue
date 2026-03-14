<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

// Khởi tạo form với các trường dữ liệu
const form = useForm({
    name: '',
    base_price: '',
    area: '',
    max_adults: 2,
    max_children: 1,
    bed_type: '',
    view: '',
    description: '',
    amenities: [],
});

const submit = () => {
    form.post(route('admin.room-types.store'));
};
</script>

<template>
    <Head title="Thêm hạng phòng mới" />

    <AdminLayout>
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <h2 class="font-display text-2xl font-semibold text-luxury-navy">Thêm Hạng Phòng Mới</h2>
                <p class="font-sans text-gray-500">Thiết lập các thông số cho hạng phòng đẳng cấp của bạn</p>
            </div>

            <form @submit.prevent="submit" class="luxury-card p-10 space-y-8">
                <div class="grid grid-cols-1 gap-6">
                    <div class="flex flex-col">
                        <label class="font-display text-sm font-semibold text-gray-700 mb-2">Tên hạng phòng</label>
                        <input v-model="form.name" type="text" placeholder="VD: Presidential Ocean Suite" 
                               class="font-sans border-gray-200 focus:border-luxury-gold focus:ring-luxury-gold rounded-xl py-3 px-4 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex flex-col">
                        <label class="font-display text-sm font-semibold text-gray-700 mb-2">Giá cơ bản (VNĐ)</label>
                        <input v-model="form.base_price" type="number" class="font-sans border-gray-200 focus:border-luxury-gold focus:ring-luxury-gold rounded-xl py-3 px-4">
                    </div>
                    <div class="flex flex-col">
                        <label class="font-display text-sm font-semibold text-gray-700 mb-2">Diện tích (m²)</label>
                        <input v-model="form.area" type="number" class="font-sans border-gray-200 focus:border-luxury-gold focus:ring-luxury-gold rounded-xl py-3 px-4">
                    </div>
                    <div class="flex flex-col">
                        <label class="font-display text-sm font-semibold text-gray-700 mb-2">Loại giường</label>
                        <input v-model="form.bed_type" type="text" placeholder="King Size" class="font-sans border-gray-200 focus:border-luxury-gold focus:ring-luxury-gold rounded-xl py-3 px-4">
                    </div>
                </div>

                <div class="flex flex-col">
                    <label class="font-display text-sm font-semibold text-gray-700 mb-2">Mô tả hạng phòng</label>
                    <textarea v-model="form.description" rows="4" class="font-sans border-gray-200 focus:border-luxury-gold focus:ring-luxury-gold rounded-xl py-3 px-4"></textarea>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" :disabled="form.processing" class="btn-gold !rounded-xl !px-10 !py-4 shadow-xl">
                        {{ form.processing ? 'Đang lưu...' : 'Lưu Hạng Phòng' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>