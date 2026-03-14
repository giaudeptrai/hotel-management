<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10 border-b border-gray-200">
        <div class="flex items-center">
            <h2 class="text-lg font-semibold text-gray-700">Hệ thống quản lý khách sạn</h2>
        </div>
        
        <div class="flex items-center space-x-4">
            <Link :href="route('logout')" method="post" as="button" 
                class="text-gray-500 hover:text-red-600 font-medium text-sm transition-colors flex items-center">
                <span class="mr-1">🚪</span> Đăng xuất
            </Link>
        </div>
    </header>
</template>