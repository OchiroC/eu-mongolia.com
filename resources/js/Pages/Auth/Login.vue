<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/components/ui/Button.vue';
import Checkbox from '@/components/ui/Checkbox.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Нэвтрэх" />

    <GuestLayout title="Тавтай морил 👋" subtitle="Бүртгэлдээ нэвтэрнэ үү">
        <div v-if="status" class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="space-y-1.5">
                <Label for="email">И-мэйл хаяг</Label>
                <Input id="email" type="email" v-model="form.email" required autofocus autocomplete="username" placeholder="tani@mail.com" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="space-y-1.5">
                <Label for="password">Нууц үг</Label>
                <Input id="password" type="password" v-model="form.password" required autocomplete="current-password" placeholder="••••••••" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <Checkbox v-model="form.remember" />
                    Намайг сана
                </label>

                <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm font-medium text-brand-700 hover:text-brand-800 hover:underline">
                    Нууц үг мартсан?
                </Link>
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">Нэвтрэх</Button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Бүртгэлгүй юу?
            <Link :href="route('register')" class="font-semibold text-brand-700 hover:underline">Бүртгүүлэх</Link>
        </p>
    </GuestLayout>
</template>
