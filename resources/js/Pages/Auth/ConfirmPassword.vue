<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Нууц үг баталгаажуулах" />

    <GuestLayout title="Аюулгүй байдлын шалгалт" subtitle="Үргэлжлүүлэхийн тулд нууц үгээ оруулна уу">
        <p class="text-sm text-gray-600">
            Энэ хэсэг нь хамгаалалттай. Үргэлжлүүлэхийн өмнө нууц үгээ дахин баталгаажуулна уу.
        </p>

        <form @submit.prevent="submit" class="mt-6 space-y-5">
            <div class="space-y-1.5">
                <Label for="password">Нууц үг</Label>
                <Input id="password" type="password" v-model="form.password" required autocomplete="current-password" autofocus placeholder="••••••••" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">Баталгаажуулах</Button>
        </form>
    </GuestLayout>
</template>
