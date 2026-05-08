<script setup>
const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    inputClass: {
        type: String,
        default: '',
    },
});
defineEmits(['update:modelValue']);
</script>
<template>
    <div class="flex flex-col gap-2">
        <label v-if="label" class="text-[11px] font-bold uppercase tracking-wider text-muted-text px-1">
            {{ label }}
        </label>
        <input
            :type="props.type"
            :value="props.modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :class="[
                'form-input-pms',
                props.inputClass,
                props.error ? '!border-rose-500/50 !bg-rose-50/30' : ''
            ]"
            v-bind="$attrs"
        >
        <p v-if="props.error" class="text-rose-500 text-xs font-bold px-1">{{ props.error }}</p>
    </div>
</template>
