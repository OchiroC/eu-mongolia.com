<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Button from '@/Components/ui/Button.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    status: { type: String },
});

const form = useForm({});

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');

const submit = () => {
    form.post(route('verification.send'));
};
</script>

<template>
    <Head title="И-мэйл баталгаажуулах" />

    <GuestLayout title="И-мэйлээ баталгаажуулна уу" subtitle="Бид танд баталгаажуулах холбоос илгээлээ">
        <p class="text-sm text-gray-600">
            Бүртгүүлсэнд баярлалаа! Эхлэхээсээ өмнө бид танд илгээсэн холбоос дээр дарж
            и-мэйлээ баталгаажуулна уу. Хэрэв и-мэйл ирээгүй бол дахин илгээх боломжтой.
        </p>

        <div v-if="verificationLinkSent" class="mt-4 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            Таны и-мэйл рүү шинэ баталгаажуулах холбоос илгээгдлээ.
        </div>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
            <Button type="submit" class="w-full" :disabled="form.processing">
                Баталгаажуулах холбоос дахин илгээх
            </Button>

            <Link :href="route('logout')" method="post" as="button" class="block w-full text-center text-sm text-gray-500 hover:text-brand-700 hover:underline">
                Гарах
            </Link>
        </form>
    </GuestLayout>
</template>
