<script setup>
const props = defineProps({
    value: {
        type: Number,
        default: 0,
    },
    sizeClass: {
        type: String,
        default: 'h-4 w-4',
    },
    activeClass: {
        type: String,
        default: 'text-amber-400',
    },
    inactiveClass: {
        type: String,
        default: 'text-slate-300 dark:text-slate-600',
    },
});

const rounded = Math.max(0, Math.min(5, Math.round(Number(props.value || 0))));
</script>

<template>
    <span class="inline-flex items-center gap-1">
        <svg
            v-for="star in 5"
            :key="star"
            :class="[sizeClass, star <= rounded ? activeClass : inactiveClass]"
            fill="currentColor"
            viewBox="0 0 20 20"
            aria-hidden="true"
        >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.955a1 1 0 00.95.69h4.158c.969 0 1.371 1.24.588 1.81l-3.365 2.445a1 1 0 00-.364 1.118l1.286 3.955c.3.921-.755 1.688-1.538 1.118l-3.365-2.445a1 1 0 00-1.175 0l-3.365 2.445c-.783.57-1.838-.197-1.538-1.118l1.286-3.955a1 1 0 00-.364-1.118L2.067 9.382c-.783-.57-.38-1.81.588-1.81h4.158a1 1 0 00.95-.69l1.286-3.955z" />
        </svg>
    </span>
</template>
