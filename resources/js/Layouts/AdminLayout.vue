<script setup>
import Sidebar from '@/Components/Admin/Sidebar.vue';
import Topbar from '@/Components/Admin/Topbar.vue';
</script>

<template>
    <div class="flex h-screen bg-gray-50 font-sans antialiased">
        <Sidebar />

        <div class="flex-1 flex flex-col overflow-hidden">
            <Topbar />

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
                <slot />
            </main>
        </div>
    </div>
</template>