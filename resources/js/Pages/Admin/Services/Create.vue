<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const form = useForm({
    name: '',
    type: 'other', // Mặc định là khác
    price: '',
    unit: 'Lần',
    is_active: true,
});

const submit = () => form.post(route('admin.services.store'));
</script>

<template>
    <Head title="Thêm Dịch Vụ" />
    <AdminLayout>
        <div class="admin-form-page">
            <div class="flex items-end justify-between px-2">
                <div>
                    <span class="admin-index-subtitle">Menu Khách Sạn</span>
                    <h2 class="admin-index-title">Khởi Tạo Dịch Vụ</h2>
                </div>
                <Link :href="route('admin.services.index')" class="admin-form-back-link">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                    Quay lại menu
                </Link>
            </div>

            <form @submit.prevent="submit" class="admin-form-card">
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Tên dịch vụ/món hàng <span class="text-rose-500">*</span></label>
                        <input v-model="form.name" type="text" class="form-input-pms" placeholder="VD: Mì ly, Giặt nhanh, Spa...">
                        <p v-if="form.errors.name" class="text-[10px] text-rose-500 font-bold uppercase mt-1 px-1 italic">{{ form.errors.name }}</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Phân loại <span class="text-rose-500">*</span></label>
                        <select v-model="form.type" class="form-input-pms cursor-pointer appearance-none">
                            <option value="food">🍔 Đồ ăn (Food)</option>
                            <option value="drink">🥤 Thức uống (Drink)</option>
                            <option value="spa">💆‍♀️ Spa & Massage</option>
                            <option value="laundry">🧺 Giặt ủi (Laundry)</option>
                            <option value="other">📦 Dịch vụ khác</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Đơn vị tính</label>
                        <input v-model="form.unit" type="text" class="form-input-pms" placeholder="Ly, Gói, Lần...">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[11px] font-black text-muted-text uppercase tracking-widest px-1">Đơn giá (VNĐ) <span class="text-rose-500">*</span></label>
                        <input v-model="form.price" type="number" class="form-input-pms input-number-clean !text-primary-500 !text-2xl !font-black" placeholder="Nhập giá bán...">
                        <p v-if="form.errors.price" class="text-[10px] text-rose-500 font-bold uppercase mt-1 px-1 italic">{{ form.errors.price }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 px-1">
                    <input type="checkbox" v-model="form.is_active" id="is_active" class="w-5 h-5 rounded-lg border-slate-200 text-primary-500 focus:ring-primary-500/20 transition-all">
                    <label for="is_active" class="text-[11px] font-black text-muted-text uppercase tracking-widest cursor-pointer">Kinh doanh ngay khi tạo</label>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full admin-index-create-btn !justify-center !py-5 !text-[11px] !uppercase !tracking-[0.2em]">
                    {{ form.processing ? 'Đang khởi tạo...' : 'Xác nhận thêm dịch vụ' }}
                </button>
            </form>
        </div>
    </AdminLayout>
</template>
