<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const year = new Date().getFullYear();
const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <footer class="mt-auto border-t border-slate-200 bg-white text-slate-600 transition-colors duration-500 dark:border-dark-border dark:bg-slate-950 dark:text-slate-400">
        <div class="border-b border-slate-100 dark:border-dark-border bg-slate-50 dark:bg-slate-900/30">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="max-w-xl text-center md:text-left">
                        <h3 class="text-xl font-black tracking-tight text-slate-900 dark:text-white">Nhận ưu đãi độc quyền từ Dasher Hotel</h3>
                        <p class="text-sm font-medium mt-1">Đăng ký email để nhận thông báo về các chương trình giảm giá và phòng mới nhất.</p>
                    </div>
                    <div class="w-full md:w-auto flex gap-2">
                        <input type="email" placeholder="Nhập email của bạn..." class="h-12 w-full md:w-72 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-900 outline-none placeholder:text-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:bg-slate-900 dark:border-slate-700 dark:text-white">
                        <button class="h-12 px-6 rounded-xl bg-slate-900 text-white font-black uppercase tracking-widest text-[10px] hover:bg-primary-500 transition-colors dark:bg-white dark:text-slate-900 dark:hover:bg-primary-500 dark:hover:text-white shrink-0">
                            Đăng Ký
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 pt-16 pb-8 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-12 lg:gap-8">

                <div class="lg:col-span-4 space-y-6">
                    <Link href="/" class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl flex items-center justify-center font-black text-xl italic">
                            D
                        </div>
                        <p class="text-2xl font-black italic tracking-tighter text-slate-900 dark:text-white">
                            DASHER<span class="text-primary-500">HOTEL</span>
                        </p>
                    </Link>
                    <p class="text-sm font-medium leading-relaxed max-w-sm">
                        Hệ thống khách sạn chuẩn mực mang đến trải nghiệm lưu trú đẳng cấp, tiện nghi và hiện đại ngay trung tâm thành phố.
                    </p>
                    <div class="flex gap-3">
                        <a href="#" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 hover:bg-primary-500 hover:text-white transition-all dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-primary-500 dark:hover:text-white" title="Facebook">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12a10 10 0 10-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.5-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.45H15.2c-1.24 0-1.62.77-1.62 1.56V12h2.77l-.44 2.89h-2.33v6.99A10 10 0 0022 12"/></svg>
                        </a>
                        <a href="#" class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 hover:bg-primary-500 hover:text-white transition-all dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-primary-500 dark:hover:text-white" title="Instagram">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="2.5" y="2.5" width="19" height="19" rx="5"/><circle cx="12" cy="12" r="4.5"/><circle cx="18" cy="6" r="1" fill="currentColor" stroke="none"/></svg>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-4">
                    <h4 class="text-[11px] font-black uppercase tracking-widest text-slate-900 dark:text-white">Khám Phá</h4>
                    <ul class="space-y-3">
                        <li><Link :href="route('home')" class="text-sm font-bold hover:text-primary-500 transition-colors">Trang chủ</Link></li>
                        <li><Link :href="route('client.rooms.index')" class="text-sm font-bold hover:text-primary-500 transition-colors">Tất cả phòng</Link></li>
                        <!-- <li><Link :href="route('home') + '#dich-vu'" class="text-sm font-bold hover:text-primary-500 transition-colors">Dịch vụ & Tiện ích</Link></li> -->
                        <li><Link :href="route('client.contact.index')" class="text-sm font-bold hover:text-primary-500 transition-colors">Liên hệ</Link></li>
                    </ul>
                </div>

                <div class="lg:col-span-3 space-y-4">
                    <h4 class="text-[11px] font-black uppercase tracking-widest text-slate-900 dark:text-white">Khách Hàng</h4>
                    <ul class="space-y-3">
                        <template v-if="user">
                            <li><Link :href="route('client.bookings.index')" class="text-sm font-bold hover:text-primary-500 transition-colors">Quản lý Đơn đặt phòng</Link></li>
                            <li><Link :href="route('profile.edit')" class="text-sm font-bold hover:text-primary-500 transition-colors">Hồ sơ cá nhân</Link></li>
                        </template>
                        <template v-else>
                            <li><Link :href="route('login')" class="text-sm font-bold hover:text-primary-500 transition-colors">Tra cứu Đơn đặt phòng</Link></li>
                        </template>
                        <li><a href="#" class="text-sm font-bold hover:text-primary-500 transition-colors">Chính sách Hoàn & Hủy</a></li>
                        <li><a href="#" class="text-sm font-bold hover:text-primary-500 transition-colors">Câu hỏi thường gặp (FAQ)</a></li>
                    </ul>
                </div>

                <div class="lg:col-span-3 space-y-4">
                    <h4 class="text-[11px] font-black uppercase tracking-widest text-slate-900 dark:text-white">Liên Hệ Trực Tiếp</h4>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3 text-sm font-bold">
                            <svg class="w-5 h-5 shrink-0 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span>123 Nguyễn Văn Cừ, Long Xuyên, An Giang</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm font-bold">
                            <svg class="w-5 h-5 shrink-0 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            <a href="tel:0792008096" class="hover:text-primary-500 transition-colors">0792 008 096</a>
                        </li>
                        <li class="flex items-start gap-3 text-sm font-bold">
                            <svg class="w-5 h-5 shrink-0 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            <a href="mailto:booking@dasherhotel.vn" class="hover:text-primary-500 transition-colors">booking@dasherhotel.vn</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-slate-100 dark:border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">© {{ year }} Dasher Hotel. All rights reserved.</p>
                <div class="flex items-center gap-4 text-sm font-bold">
                    <a href="#" class="hover:text-primary-500 transition-colors">Điều khoản sử dụng</a>
                    <span class="text-slate-300 dark:text-slate-700">|</span>
                    <a href="#" class="hover:text-primary-500 transition-colors">Bảo mật thông tin</a>
                </div>
            </div>
        </div>
    </footer>
</template>
