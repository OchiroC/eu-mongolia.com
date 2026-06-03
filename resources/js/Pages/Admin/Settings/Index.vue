<script setup>
import Button from '@/Components/ui/Button.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    comments_enabled: props.settings.comments_enabled,
});

function save() {
    form.put('/admin/settings', { preserveScroll: true });
}
</script>

<template>
    <Head title="Тохиргоо" />

    <AdminLayout>
        <template #title>Сайтын тохиргоо</template>

        <div class="max-w-2xl space-y-5">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <h2 class="font-semibold text-gray-900">Сэтгэгдэл</h2>
                <p class="mt-1 text-sm text-gray-500">Бүх мэдээн дээрх сэтгэгдлийн системийг удирдана.</p>

                <label class="mt-5 flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-gray-100 p-4 transition hover:bg-gray-50">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Сэтгэгдэл идэвхтэй</p>
                        <p class="mt-0.5 text-sm text-gray-500">Унтраавал бүх мэдээн дээр сэтгэгдэл бичих хэсэг хаагдана (мэдээ бүрийн тохиргооноос үл хамаарна).</p>
                    </div>
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="form.comments_enabled"
                        class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 items-center rounded-full transition"
                        :class="form.comments_enabled ? 'bg-brand-600' : 'bg-gray-300'"
                        @click="form.comments_enabled = !form.comments_enabled"
                    >
                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition" :class="form.comments_enabled ? 'translate-x-5' : 'translate-x-0.5'" />
                    </button>
                </label>
            </div>

            <Button :disabled="form.processing" @click="save">Хадгалах</Button>
        </div>
    </AdminLayout>
</template>
