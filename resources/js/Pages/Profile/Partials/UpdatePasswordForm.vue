<script setup>
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-semibold text-gray-900">Нууц үг солих</h2>
            <p class="mt-1 text-sm text-gray-500">Аюулгүй байдлын үүднээс урт, давтагдашгүй нууц үг ашиглана уу.</p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-5">
            <div class="space-y-1.5">
                <Label>Одоогийн нууц үг</Label>
                <Input ref="currentPasswordInput" v-model="form.current_password" type="password" autocomplete="current-password" />
                <InputError :message="form.errors.current_password" class="mt-1" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <Label>Шинэ нууц үг</Label>
                    <Input ref="passwordInput" v-model="form.password" type="password" autocomplete="new-password" />
                    <InputError :message="form.errors.password" class="mt-1" />
                </div>
                <div class="space-y-1.5">
                    <Label>Шинэ нууц үг давтах</Label>
                    <Input v-model="form.password_confirmation" type="password" autocomplete="new-password" />
                    <InputError :message="form.errors.password_confirmation" class="mt-1" />
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="form.processing">Шинэчлэх</Button>
                <transition enter-active-class="transition" enter-from-class="opacity-0" leave-active-class="transition" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-emerald-600">Хадгалагдлаа ✓</p>
                </transition>
            </div>
        </form>
    </section>
</template>
