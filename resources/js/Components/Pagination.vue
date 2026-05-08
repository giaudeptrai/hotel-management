<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array, // Mảng links từ Laravel pagination
});

// Hàm xử lý lại chữ hiển thị (Đổi sang tiếng Việt và bỏ các ký tự đặc biệt)
const formatLabel = (label) => {
    if (!label) return '';

    if (label.includes('Previous') || label.includes('pagination.previous')) {
        return 'Trước';
    }
    if (label.includes('Next') || label.includes('pagination.next')) {
        return 'Sau';
    }

    // Nếu là các con số (1, 2, 3...) thì giữ nguyên
    return label;
};
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap justify-center items-center gap-3">
        <template v-for="(link, key) in links" :key="key">

            <div v-if="link.url === null"
                 class="px-5 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-300 bg-slate-50/50 rounded-[1.2rem] border border-slate-100 cursor-not-allowed"
                 v-html="formatLabel(link.label)"
            />

            <Link v-else
                  :href="link.url"
                  class="px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] rounded-[1.2rem] transition-all duration-500 border relative group"
                  :class="{
                      'bg-slate-900 text-white border-slate-900 shadow-xl scale-110 z-10': link.active,
                      'bg-white text-main-text border-slate-100 hover:border-primary-500 hover:text-primary-500 shadow-sm': !link.active
                  }"
                  preserve-scroll
            >
                <span v-html="formatLabel(link.label)"></span>

                <div v-if="link.active" class="absolute inset-0 rounded-[1.2rem] shadow-[0_10px_25px_rgba(236,72,153,0.3)] -z-10 animate-pulse"></div>
            </Link>

        </template>
    </div>
</template>
