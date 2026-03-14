<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Tổng quan quản trị" />

    <AdminLayout>
        <div class="mb-10">
            <h2 class="font-display text-3xl font-semibold text-luxury-navy italic">
                Tổng quan hệ thống
            </h2>
            <p class="font-sans text-gray-500 mt-2">Chào mừng bạn trở lại, Giàu!</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <div class="luxury-card p-8 border-l-4 border-l-luxury-gold">
                <p class="font-sans text-xs uppercase tracking-widest text-gray-400 mb-2">Phòng trống</p>
                <p class="font-display text-4xl font-semibold text-luxury-navy">{{ $page.props.stats.availableRooms }}</p>
            </div>

            <div class="luxury-card p-8">
                <p class="font-sans text-xs uppercase tracking-widest text-gray-400 mb-2">Đang ở</p>
                <p class="font-display text-4xl font-semibold text-luxury-navy">{{ $page.props.stats.occupiedRooms }}</p>
            </div>

            <div class="luxury-card p-8">
                <p class="font-sans text-xs uppercase tracking-widest text-gray-400 mb-2">Cần dọn dẹp</p>
                <p class="font-display text-4xl font-semibold text-luxury-navy">{{ $page.props.stats.roomsNeedingCleaning }}</p>
            </div>

            <div class="luxury-card p-8 bg-luxury-navy">
                <p class="font-sans text-xs uppercase tracking-widest text-luxury-gold mb-2">Doanh thu ngày</p>
                <p class="font-display text-4xl font-semibold text-white">{{ $page.props.stats.dailyRevenue }}</p>
            </div>

        </div>
    </AdminLayout>
</template>