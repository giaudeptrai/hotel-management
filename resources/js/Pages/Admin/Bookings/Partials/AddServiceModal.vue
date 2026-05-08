<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    bookingId: [String, Number],
    roomNumber: String,
    guestName: String,
    services: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
    services: [],
});

const formatCurrency = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v || 0);

// GOM NHÓM DỊCH VỤ DỰA VÀO CỘT TYPE
const groupedServices = computed(() => {
    const groups = { food: [], drink: [], spa: [], laundry: [], other: [] };
    (props.services || []).forEach(s => {
        const type = groups[s.type] ? s.type : 'other';
        groups[type].push(s);
    });
    return groups;
});

const typeLabels = {
    food: '🍔 Đồ ăn',
    drink: '🥤 Thức uống',
    spa: '💆‍♀️ Spa',
    laundry: '🧺 Giặt ủi',
    other: '📦 Khác'
};

const activeCategory = ref('food');
const selectedServiceId = ref(null);

const getServiceById = (serviceId) => (props.services || []).find((s) => s.id === serviceId) || null;
const getCartItemByServiceId = (serviceId) => form.services.find((item) => item.service_id === serviceId) || null;
const isInCart = (serviceId) => !!getCartItemByServiceId(serviceId);

const selectService = (service) => {
    selectedServiceId.value = service.id;
    const existing = getCartItemByServiceId(service.id);
    if (!existing) {
        form.services.push({ service_id: service.id, quantity: 1, price: service.price });
    }
};

const selectedServiceData = computed(() => getServiceById(selectedServiceId.value));
const selectedCartItem = computed(() => getCartItemByServiceId(selectedServiceId.value));
const currentTotal = computed(() => form.services.reduce((sum, item) => sum + (item.price * item.quantity), 0));

const sanitizeQuantity = (item) => { item.quantity = Math.max(1, Number(item.quantity) || 1); };
const decreaseQty = () => { if (selectedCartItem.value) selectedCartItem.value.quantity = Math.max(1, selectedCartItem.value.quantity - 1); };
const increaseQty = () => { if (selectedCartItem.value) selectedCartItem.value.quantity += 1; };

const removeServiceFromCart = (serviceId) => {
    form.services = form.services.filter((item) => item.service_id !== serviceId);
    if (selectedServiceId.value === serviceId) {
        selectedServiceId.value = form.services[0]?.service_id || null;
    }
};

const submit = () => {
    if (!form.services.length) return;
    form.transform((data) => ({
        services: data.services.map((item) => ({
            service_id: item.service_id,
            quantity: Math.max(1, Number(item.quantity) || 1),
            price: item.price,
        })),
    })).post(route('admin.bookings.add-service', props.bookingId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            selectedServiceId.value = null;
            emit('close');
        }
    });
};

watch(() => props.show, (newVal) => {
    if (!newVal) {
        form.reset();
        activeCategory.value = 'food';
        selectedServiceId.value = null;
    }
});
</script>

<template>
    <transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="show" class="fixed inset-0 z-[999] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
            <div class="absolute inset-0" @click="emit('close')"></div>

            <div class="app-card !p-0 w-full max-w-5xl bg-white dark:bg-dark-card relative overflow-hidden z-10 flex flex-col h-[85vh] max-h-[750px] shadow-2xl animate-in fade-in zoom-in-95 duration-300" @click.stop>

                <div class="px-8 py-5 border-b border-slate-100 dark:border-dark-border flex justify-between items-center bg-white dark:bg-dark-card z-20 shrink-0">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <span class="px-2.5 py-1 rounded-md bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 text-[10px] font-black uppercase tracking-widest">Hệ thống POS</span>
                            <span class="text-xs font-bold text-muted-text">Phòng {{ roomNumber }}</span>
                        </div>
                        <h3 class="text-xl font-black text-main-text dark:text-white">Thêm dịch vụ cho 👤 {{ guestName }}</h3>
                    </div>
                    <button @click="emit('close')" class="w-10 h-10 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/20 flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="flex flex-1 overflow-hidden flex-col lg:flex-row">

                    <div class="flex-1 flex flex-col bg-slate-50/50 dark:bg-dark-bg/50 overflow-hidden">
                        <div class="px-8 py-4 bg-white dark:bg-dark-card border-b border-slate-100 dark:border-dark-border shadow-sm z-10 flex gap-2 overflow-x-auto custom-scrollbar shrink-0">
                            <button v-for="(label, key) in typeLabels" :key="key"
                                    @click="activeCategory = key"
                                    class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider whitespace-nowrap shrink-0 transition-all active:scale-95"
                                    :class="activeCategory === key
                                        ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20'
                                        : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'">
                                {{ label }}
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto custom-scrollbar p-8">
                            <div class="grid grid-cols-2 xl:grid-cols-3 gap-4">
                                <div v-for="s in groupedServices[activeCategory]" :key="s.id"
                                     @click="selectService(s)"
                                     class="p-5 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between h-[110px] group"
                                     :class="isInCart(s.id)
                                        ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-500/50 shadow-sm'
                                        : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-dark-card hover:border-amber-300 dark:hover:border-amber-500/30 shadow-sm hover:shadow-md'">
                                    <span class="font-bold text-sm text-main-text dark:text-white leading-tight line-clamp-2 group-hover:text-amber-600 transition-colors">{{ s.name }}</span>
                                    <span class="text-amber-600 font-black text-xs">{{ formatCurrency(s.price) }}</span>
                                </div>
                                <div v-if="groupedServices[activeCategory].length === 0" class="col-span-full py-16 text-center opacity-50">
                                    <svg class="w-12 h-12 mx-auto text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    <span class="text-xs font-black uppercase tracking-widest italic text-slate-500">Chưa có dịch vụ</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="w-full lg:w-[350px] shrink-0 border-t lg:border-t-0 lg:border-l border-slate-100 dark:border-dark-border bg-white dark:bg-dark-card flex flex-col relative z-20">

                        <div class="p-6 border-b border-slate-100 dark:border-dark-border bg-amber-50/30 dark:bg-amber-900/5 shrink-0">
                            <h4 class="text-[10px] font-black text-amber-600/70 uppercase tracking-widest mb-3">Đang chọn điều chỉnh</h4>
                            <div v-if="selectedServiceData && selectedCartItem" class="bg-white dark:bg-dark-bg p-4 rounded-2xl border border-amber-100 dark:border-amber-500/20 shadow-sm">
                                <p class="font-black text-sm text-main-text dark:text-white truncate mb-1" :title="selectedServiceData.name">{{ selectedServiceData.name }}</p>
                                <p class="text-xs font-bold text-amber-500 mb-3">{{ formatCurrency(selectedServiceData.price) }} / {{ selectedServiceData.unit || 'Lần' }}</p>

                                <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800 rounded-xl p-1 border border-slate-200 dark:border-dark-border">
                                    <button @click="decreaseQty" class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-rose-100 hover:text-rose-500 flex items-center justify-center font-black transition-colors shadow-sm">-</button>
                                    <input v-model.number="selectedCartItem.quantity" @change="sanitizeQuantity(selectedCartItem)" type="number" class="w-16 text-center bg-transparent border-none outline-none font-black text-lg input-number-clean text-main-text dark:text-white" min="1">
                                    <button @click="increaseQty" class="w-10 h-10 rounded-lg bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-emerald-100 hover:text-emerald-500 flex items-center justify-center font-black transition-colors shadow-sm">+</button>
                                </div>
                            </div>
                            <div v-else class="py-6 text-center border-2 border-dashed border-slate-200 dark:border-dark-border rounded-2xl bg-white dark:bg-dark-bg text-slate-400">
                                <p class="text-[10px] font-black uppercase tracking-widest italic">Chọn món để thao tác</p>
                            </div>
                        </div>

                        <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-3">
                            <h4 v-if="form.services.length" class="text-[10px] font-black text-muted-text uppercase tracking-widest mb-1">Danh sách đã thêm</h4>
                            <div v-for="item in form.services" :key="item.service_id"
                                 @click="selectedServiceId = item.service_id"
                                 class="p-3.5 rounded-xl border cursor-pointer transition-all flex items-center justify-between gap-3 group"
                                 :class="selectedServiceId === item.service_id
                                    ? 'border-amber-400 bg-amber-50 dark:bg-amber-900/20 shadow-sm'
                                    : 'border-slate-100 dark:border-dark-border hover:border-amber-300 dark:hover:border-amber-500/30 hover:bg-slate-50 dark:hover:bg-slate-800'">
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-black text-main-text dark:text-white truncate">{{ getServiceById(item.service_id)?.name || 'Dịch vụ' }}</p>
                                    <p class="text-[10px] text-amber-600 font-black mt-1">{{ formatCurrency(item.price) }} <span class="text-slate-400 dark:text-slate-500 mx-1">x</span> <span class="text-sm">{{ item.quantity }}</span></p>
                                </div>
                                <button @click.stop="removeServiceFromCart(item.service_id)" class="w-8 h-8 rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-100 dark:hover:bg-rose-500/20 flex items-center justify-center transition-colors shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <div v-if="!form.services.length" class="h-full flex items-center justify-center opacity-30 flex-col gap-2">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                                <span class="text-[10px] font-black uppercase tracking-widest">Giỏ hàng trống</span>
                            </div>
                        </div>

                        <div class="p-6 border-t border-slate-100 dark:border-dark-border bg-slate-50 dark:bg-slate-800/50 space-y-4 shrink-0">
                            <div class="flex justify-between items-end">
                                <span class="text-[11px] font-black uppercase tracking-widest text-slate-500">Tạm tính</span>
                                <span class="text-2xl font-black text-amber-500 tracking-tighter italic">{{ formatCurrency(currentTotal) }}</span>
                            </div>
                            <p v-if="form.errors.services" class="text-[10px] font-bold text-rose-500 text-center">{{ form.errors.services }}</p>
                            <button @click="submit" :disabled="form.processing || !form.services.length"
                                    class="w-full bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white py-4 rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg shadow-amber-500/25 transition-all active:scale-95 flex items-center justify-center gap-2">
                                <svg v-if="!form.processing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <svg v-else class="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ form.processing ? 'Đang đẩy...' : 'Xác nhận' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
