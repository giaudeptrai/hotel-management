<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({ service: Object });

const form = useForm({
    name: props.service.name,
    type: props.service.type || 'other',
    price: props.service.price,
    unit: props.service.unit,
    is_active: props.service.is_active,
});

const submit = () => form.put(route('admin.services.update', props.service.id));
</script>

<template>
    <Head title="Sửa Dịch Vụ" />
    <AdminLayout>
        <div class="admin-form-page">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="admin-index-subtitle">Hiệu chỉnh đơn giá</span>
                    <h2 class="admin-index-title">Cập Nhật Dịch Vụ</h2>
                </div>
                <Link :href="route('admin.services.index')" class="admin-form-back-link">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    Quay lại menu
                </Link>
            </div>

            <form @submit.prevent="submit" class="admin-form-card">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Tên dịch vụ/món hàng</label>
                        <input v-model="form.name" type="text" class="form-input-pms font-black italic">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Phân loại</label>
                        <select v-model="form.type" class="form-input-pms cursor-pointer appearance-none">
                            <option value="food">🍔 Đồ ăn (Food)</option>
                            <option value="drink">🥤 Thức uống (Drink)</option>
                            <option value="spa">💆‍♀️ Spa & Massage</option>
                            <option value="laundry">🧺 Giặt ủi (Laundry)</option>
                            <option value="other">📦 Dịch vụ khác</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Sửa đơn vị</label>
                        <input v-model="form.unit" type="text" class="form-input-pms">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Giá mới (VNĐ)</label>
                        <input v-model="form.price" type="number" class="form-input-pms input-number-clean !text-primary-500 !text-2xl !font-black">
                    </div>
                </div>

                <div class="flex items-center gap-3 px-1">
                    <input type="checkbox" v-model="form.is_active" id="edit_active" class="w-5 h-5 rounded-lg border-slate-200 text-primary-500 focus:ring-primary-500/20 transition-all">
                    <label for="edit_active" class="text-[11px] font-black text-muted-text uppercase tracking-widest cursor-pointer">Trạng thái kinh doanh</label>
                </div>

                <button type="submit" :disabled="form.processing"
                        class="w-full admin-index-create-btn !justify-center !py-5 !text-[11px] !uppercase !tracking-[0.2em]">
                    {{ form.processing ? 'Đang lưu...' : 'Cập nhật bảng giá' }}
                </button>
            </form>
        </div>
    </AdminLayout>
</template>
