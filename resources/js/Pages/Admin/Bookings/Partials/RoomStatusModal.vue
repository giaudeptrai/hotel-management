<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    room: Object,
});

const emit = defineEmits(['close']);

const selectedStatus = ref('available');
const isSubmitting = ref(false);

const roomStatusOptions = [
    { value: 'available', label: 'Trống', color: 'bg-slate-100 dark:bg-slate-700', icon: '✓' },
    { value: 'cleaning', label: 'Đang dọn dẹp', color: 'bg-orange-100 dark:bg-orange-700', icon: '🧹' },
    { value: 'maintenance', label: 'Đang bảo trì', color: 'bg-purple-100 dark:bg-purple-700', icon: '🔧' },
    { value: 'occupied', label: 'Đang lưu trú', color: 'bg-emerald-100 dark:bg-emerald-700', icon: '👤', disabled: true },
];

const updateStatus = () => {
    if (!selectedStatus.value || isSubmitting.value) return;

    isSubmitting.value = true;
    router.patch(route('admin.rooms.update-status', props.room.id), {
        status: selectedStatus.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            isSubmitting.value = false;
            // 🎯 Reload trang để lấy dữ liệu phòng mới nhất
            setTimeout(() => {
                window.location.reload();
            }, 500);
        },
        onError: () => {
            isSubmitting.value = false;
        }
    });
};

const closeModal = () => {
    emit('close');
    selectedStatus.value = 'available';
};
</script>

<template>
    <teleport to="body">
        <transition name="modal-fade">
            <div v-if="show" class="fixed inset-0 bg-black/50 dark:bg-black/80 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-dark-card rounded-3xl shadow-2xl w-full max-w-md mx-4 p-8 animate-in zoom-in duration-300">
                    <!-- Header -->
                    <div class="mb-6">
                        <h3 class="text-2xl font-black text-main-text dark:text-white">
                            Cập nhật trạng thái
                        </h3>
                        <p class="text-sm text-muted-text mt-2">
                            Phòng <span class="font-bold text-primary-600">{{ room?.room_number }}</span>
                        </p>
                    </div>

                    <!-- Room Info -->
                    <div class="bg-slate-50 dark:bg-dark-border/30 rounded-2xl p-4 mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase text-muted-text">Trạng thái hiện tại</p>
                                <p class="text-sm font-black text-main-text dark:text-white mt-1">{{ room?.status }}</p>
                            </div>
                            <div class="text-3xl">📊</div>
                        </div>
                    </div>

                    <!-- Status Options -->
                    <div class="space-y-2 mb-6">
                        <label class="text-xs font-bold uppercase text-muted-text px-1 block">Chọn trạng thái mới</label>
                        <div class="space-y-2">
                            <div v-for="option in roomStatusOptions" :key="option.value"
                                 class="relative">
                                <input
                                    type="radio"
                                    :id="`status-${option.value}`"
                                    v-model="selectedStatus"
                                    :value="option.value"
                                    :disabled="option.disabled"
                                    class="sr-only peer"
                                />
                                <label
                                    :for="`status-${option.value}`"
                                    :class="[
                                        'flex items-center gap-3 p-4 rounded-2xl border-2 transition-all cursor-pointer',
                                        option.disabled ? 'opacity-50 cursor-not-allowed' : 'peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-900/20 hover:border-primary-300',
                                        selectedStatus === option.value ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-slate-200 dark:border-dark-border'
                                    ]">
                                    <span class="text-2xl">{{ option.icon }}</span>
                                    <div>
                                        <p class="font-bold text-main-text dark:text-white">{{ option.label }}</p>
                                        <p v-if="option.disabled" class="text-xs text-muted-text">Không thể đổi thủ công</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Helper Text -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-500/30 rounded-xl p-4 mb-6">
                        <p class="text-xs text-blue-700 dark:text-blue-300">
                            <strong>💡 Mẹo:</strong> Sau khi check-out, phòng tự động chuyển sang "Đang dọn dẹp". Khi dọn xong, hãy chọn "Trống" để phòng sẵn sàng tiếp khách.
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button
                            @click="closeModal"
                            class="flex-1 px-4 py-3 rounded-2xl font-bold uppercase tracking-wide text-sm border-2 border-slate-200 dark:border-dark-border text-main-text dark:text-white hover:bg-slate-50 dark:hover:bg-dark-border/50 transition-all">
                            Hủy
                        </button>
                        <button
                            @click="updateStatus"
                            :disabled="isSubmitting || selectedStatus === room?.status"
                            :class="[
                                'flex-1 px-4 py-3 rounded-2xl font-bold uppercase tracking-wide text-sm text-white transition-all',
                                isSubmitting || selectedStatus === room?.status
                                    ? 'bg-slate-400 cursor-not-allowed'
                                    : 'bg-primary-600 hover:bg-primary-700 shadow-lg shadow-primary-500/30'
                            ]">
                            <span v-if="isSubmitting" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Đang cập nhật...
                            </span>
                            <span v-else>✓ Xác nhận</span>
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<style scoped>
.modal-fade-enter-active, .modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from, .modal-fade-leave-to {
    opacity: 0;
}
</style>
