import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export default function useAdminFlash() {
    const page = usePage();

    const flashSuccess = computed(() => ((page.props || {}).flash || {}).success || '');
    const flashError = computed(() => ((page.props || {}).flash || {}).error || '');

    return {
        flashSuccess,
        flashError,
    };
}