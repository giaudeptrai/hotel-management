<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: String,
    active: Boolean,
});
</script>

<template>
    <Link :href="href" 
        :class="[
            'flex items-center px-6 py-4 transition-all duration-300 border-l-2',
            active 
                ? 'bg-luxury-charcoal text-luxury-gold border-luxury-gold' 
                : 'text-gray-400 border-transparent hover:text-luxury-champagne hover:bg-gray-800'
        ]">
        <slot />
    </Link>
</template>