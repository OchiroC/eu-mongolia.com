<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const result = computed(() => page.props.flash?.checkin ?? null);
const codeInput = ref(null);

const form = useForm({ code: '' });

function submit() {
    form.post('/admin/check-in', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('code');
            codeInput.value?.focus();
        },
    });
}
</script>

<template>
    <Head title="Тасалбар шалгах" />

    <AdminLayout>
        <template #title>Тасалбар шалгах (Check-in)</template>

        <div class="mx-auto max-w-md">
            <form @submit.prevent="submit" class="space-y-1.5 rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <Label>Тасалбарын код</Label>
                <Input
                    ref="codeInput"
                    v-model="form.code"
                    type="text"
                    autofocus
                    placeholder="QR доорх кодыг оруулна уу"
                    class="font-mono"
                />
                <Button type="submit" :disabled="form.processing" class="mt-4 w-full">Шалгах</Button>
            </form>

            <div
                v-if="result"
                class="mt-6 rounded-lg p-6 text-center"
                :class="result.ok ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
            >
                <p class="text-2xl font-bold">{{ result.message }}</p>
                <p v-if="result.detail" class="mt-2">{{ result.detail }}</p>
            </div>

            <p class="mt-4 text-center text-xs text-gray-400">
                Утасны QR уншигчаар кодыг уншуулаад энд буулгаж болно. (Камер скан дараа нэмж болно)
            </p>
        </div>
    </AdminLayout>
</template>
