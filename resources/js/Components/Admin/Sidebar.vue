<script setup>
import SidebarLink from '@/Components/SidebarLink.vue';
import { usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;
</script>

<template>
    <aside class="w-64 bg-luxury-navy text-white flex flex-col shadow-2xl">
        <div class="h-20 flex items-center justify-center border-b border-gray-800">
            <h1 class="font-display text-xl font-600 tracking-wider text-luxury-gold uppercase"> Marriott </h1>
        </div>
        
        <nav class="flex-1 py-4">
            <SidebarLink :href="route('dashboard')" :active="route().current('dashboard')">
                <span class="text-xs tracking-widest uppercase">Dashboard</span>
            </SidebarLink>
            <SidebarLink :href="route('manage.rooms.matrix')" :active="route().current('manage.rooms.matrix')">
                <span class="text-xs tracking-widest uppercase">Room Matrix</span>
            </SidebarLink>
            <SidebarLink :href="route('admin.room-types.create')" :active="route().current('admin.room-types.create')">
                <span class="text-xs tracking-widest uppercase">Add Room Type</span>
            </SidebarLink>
        </nav>

        <div class="p-6 border-t border-gray-800 bg-luxury-charcoal bg-opacity-50">
            <p class="text-[10px] uppercase tracking-tighter text-gray-500">Logged in as</p>
            <p class="text-sm font-medium text-luxury-champagne">{{ $page.props.auth.user.name }}</p>
        </div>
    </aside>
</template>