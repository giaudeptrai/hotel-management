<script setup>
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="admin-index-title !text-2xl !text-rose-600">
                Xóa Tài Khoản
            </h2>

            <p class="mt-1 text-desc">
                Khi xóa tài khoản, toàn bộ dữ liệu liên quan sẽ bị xóa vĩnh viễn.
                Hãy chắc chắn bạn đã sao lưu các thông tin cần giữ trước khi thực hiện.
            </p>
        </header>

        <button @click="confirmUserDeletion" class="px-6 py-3 rounded-layout bg-rose-500 text-white font-bold text-sm tracking-tight transition-all hover:bg-rose-600">
            Xóa Tài Khoản
        </button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-lg font-black text-main-text dark:text-white"
                >
                    Bạn có chắc chắn muốn xóa tài khoản?
                </h2>

                <p class="mt-1 text-sm text-muted-text">
                    Hành động này không thể hoàn tác. Vui lòng nhập mật khẩu để
                    xác nhận xóa tài khoản vĩnh viễn.
                </p>

                <div class="mt-6">
                    <label for="password" class="sr-only">Mật khẩu</label>

                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="form-input-pms mt-1 w-full sm:w-3/4"
                        placeholder="Nhập mật khẩu"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" @click="closeModal" class="admin-index-secondary-btn !py-2.5 !px-5">
                        Hủy
                    </button>

                    <button
                        type="button"
                        class="ms-3 px-5 py-2.5 rounded-layout bg-rose-500 text-white font-bold text-sm tracking-tight transition-all hover:bg-rose-600"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Xác Nhận Xóa
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
