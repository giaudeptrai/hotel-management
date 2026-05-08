<script setup>
import Header from '@/Components/Client/Header.vue';
import Footer from '@/Components/Client/Footer.vue';
</script>

<template>
    <div class="relative min-h-screen bg-surface dark:bg-dark-bg text-main-text dark:text-dark-text transition-colors duration-500 flex flex-col font-sans">
        <div class="pointer-events-none absolute inset-0 opacity-60 [background:radial-gradient(circle_at_20%_0%,rgba(15,118,110,0.08),transparent_35%),radial-gradient(circle_at_80%_20%,rgba(245,158,11,0.08),transparent_35%)]"></div>
        <Header />

        <main class="relative z-10 flex-grow pt-20">
            <slot />
        </main>

        <Footer />
    </div>
</template>
