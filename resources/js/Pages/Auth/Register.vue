<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Бүртгүүлэх" />

    <GuestLayout title="Бүртгэл үүсгэх" subtitle="Хэдхэн алхамаар нэгдээрэй">
        <form @submit.prevent="submit" class="space-y-5">
            <div class="space-y-1.5">
                <Label for="name">Нэр</Label>
                <Input id="name" type="text" v-model="form.name" required autofocus autocomplete="name" placeholder="Таны нэр" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="space-y-1.5">
                <Label for="email">И-мэйл хаяг</Label>
                <Input id="email" type="email" v-model="form.email" required autocomplete="username" placeholder="tani@mail.com" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="space-y-1.5">
                <Label for="password">Нууц үг</Label>
                <Input id="password" type="password" v-model="form.password" required autocomplete="new-password" placeholder="••••••••" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="space-y-1.5">
                <Label for="password_confirmation">Нууц үг давтах</Label>
                <Input id="password_confirmation" type="password" v-model="form.password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">Бүртгүүлэх</Button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Бүртгэлтэй юу?
            <Link :href="route('login')" class="font-semibold text-brand-700 hover:underline">Нэвтрэх</Link>
        </p>
    </GuestLayout>
</template>
