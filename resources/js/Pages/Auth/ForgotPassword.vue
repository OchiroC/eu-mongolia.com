<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: { type: String },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Нууц үг сэргээх" />

    <GuestLayout title="Нууц үг мартсан уу?" subtitle="И-мэйлээ оруулбал сэргээх холбоос илгээнэ">
        <div v-if="status" class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="space-y-1.5">
                <Label for="email">И-мэйл хаяг</Label>
                <Input id="email" type="email" v-model="form.email" required autofocus autocomplete="username" placeholder="tani@mail.com" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">Сэргээх холбоос илгээх</Button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            <Link :href="route('login')" class="font-semibold text-brand-700 hover:underline">← Нэвтрэх рүү буцах</Link>
        </p>
    </GuestLayout>
</template>
