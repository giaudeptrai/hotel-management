<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/Components/Admin/StatCard.vue';
import { Head } from '@inertiajs/vue3';

// Giả lập dữ liệu (Sau này Giàu sẽ truyền từ Controller qua nhé)
const stats = [
    { title: 'Tổng Doanh Thu', value: '128.5M', icon: 'fas fa-wallet', trend: '+15.2%', isUp: true },
    { title: 'Tỷ lệ lấp đầy', value: '85%', icon: 'fas fa-door-open', trend: '+5.4%', isUp: true },
    { title: 'Lượt đặt mới', value: '24', icon: 'fas fa-calendar-check', trend: '-2.1%', isUp: false },
    { title: 'Khách đang ở', value: '42', icon: 'fas fa-users', trend: '+8%', isUp: true },
];
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <div class="mb-10">
            <h1 class="text-2xl font-black text-base-text tracking-tight">Chào buổi sáng, {{ $page.props.auth.user.name }}!</h1>
            <p class="text-muted-text text-sm">Đây là những gì đang diễn ra tại khách sạn của bạn hôm nay.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <StatCard v-for="stat in stats" :key="stat.title" v-bind="stat" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 app-card flex flex-col justify-center items-center min-h-[400px]">
                <div class="w-full flex justify-between items-center mb-8">
                    <h3 class="font-bold text-base-text uppercase text-xs tracking-widest">Phân tích doanh thu</h3>
                    <select class="text-xs font-bold border-none bg-slate-50 rounded-lg focus:ring-0">
                        <option>7 ngày qua</option>
                        <option>30 ngày qua</option>
                    </select>
                </div>
                <div class="flex-1 w-full bg-slate-50/50 rounded-2xl border-2 border-dashed border-slate-100 flex items-center justify-center">
                    <p class="text-slate-300 text-sm italic">Khu vực hiển thị biểu đồ doanh thu</p>
                </div>
            </div>

            <div class="app-card">
                <h3 class="font-bold text-base-text uppercase text-xs tracking-widest mb-6">Trạng thái phòng</h3>
                <div class="space-y-4">
                    <div v-for="i in 4" :key="i" class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            <span class="text-sm font-bold text-base-text">Phòng {{ 100 + i }}</span>
                        </div>
                        <span class="text-[10px] font-black uppercase text-emerald-500">Sẵn sàng</span>
                    </div>
                </div>
                <button class="w-full mt-6 text-primary-500 text-[11px] font-black uppercase tracking-widest hover:underline">
                    Xem tất cả sơ đồ phòng
                </button>
            </div>
        </div>
    </AdminLayout>
</template>