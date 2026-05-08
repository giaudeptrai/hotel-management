<script setup>
defineProps({
    title: String,
    value: [String, Number],
    icon: String,
    trend: String, // Ví dụ: "+12%"
    isUp: Boolean  // Để biết là tăng hay giảm (xanh hay đỏ)
});
</script>

<template>
    <div class="app-card flex items-center justify-between group hover:border-primary-500/30 transition-all cursor-default">
        <div>
            <p class="text-[11px] font-bold text-muted-text uppercase tracking-widest mb-2">{{ title }}</p>
            <h3 class="text-2xl font-black text-base-text tracking-tight">{{ value }}</h3>
            <div v-if="trend" class="mt-2 flex items-center gap-1">
                <span :class="isUp ? 'text-emerald-500' : 'text-rose-500'" class="text-[11px] font-bold">
                    {{ trend }}
                </span>
                <span class="text-[10px] text-muted-text italic text-opacity-50">so với tháng trước</span>
            </div>
        </div>
        
        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-primary-500 group-hover:bg-primary-500 group-hover:text-white transition-all duration-300">
            <i :class="icon" class="text-xl"></i>
        </div>
    </div>
</template>