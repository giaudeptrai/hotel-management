<script setup>
defineProps(['modelValue', 'label', 'error', 'type']);
defineEmits(['update:modelValue']);
</script>
<template>
    <div class="flex flex-col gap-2">
        <label v-if="label" class="text-[11px] font-bold uppercase tracking-wider text-muted-text px-1">
            {{ label }}
        </label>
        <input 
            :type="type || 'text'"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="app-input"
            v-bind="$attrs"
        >
        <p v-if="error" class="text-red-500 text-xs italic mt-1">{{ error }}</p>
    </div>
</template>