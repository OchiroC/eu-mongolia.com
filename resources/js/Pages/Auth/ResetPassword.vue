<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Шинэ нууц үг" />

    <GuestLayout title="Шинэ нууц үг тохируулах" subtitle="Шинэ нууц үгээ оруулна уу">
        <form @submit.prevent="submit" class="space-y-5">
            <div class="space-y-1.5">
                <Label for="email">И-мэйл хаяг</Label>
                <Input id="email" type="email" v-model="form.email" required autofocus autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="space-y-1.5">
                <Label for="password">Шинэ нууц үг</Label>
                <Input id="password" type="password" v-model="form.password" required autocomplete="new-password" placeholder="••••••••" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="space-y-1.5">
                <Label for="password_confirmation">Нууц үг давтах</Label>
                <Input id="password_confirmation" type="password" v-model="form.password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">Нууц үг шинэчлэх</Button>
        </form>
    </GuestLayout>
</template>
