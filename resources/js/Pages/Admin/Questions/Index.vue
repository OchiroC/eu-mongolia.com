<script setup>
import Button from '@/Components/ui/Button.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ questions: Object });

function destroy(id) {
    if (confirm('Энэ асуултыг (хариултуудтай нь) устгах уу?')) {
        router.delete(`/admin/questions/${id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Асуулт хариулт" />

    <AdminLayout>
        <template #title>Асуулт хариулт</template>

        <div class="overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Асуулт</th>
                        <th class="px-4 py-3">Ангилал</th>
                        <th class="px-4 py-3">Хэрэглэгч</th>
                        <th class="px-4 py-3">Хариулт</th>
                        <th class="px-4 py-3">Үзсэн</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="q in questions.data" :key="q.id">
                        <td class="px-4 py-3">
                            <Link :href="`/questions/${q.slug}`" target="_blank" class="font-medium text-gray-800 hover:text-brand-700">{{ q.title }}</Link>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ q.category_label }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ q.user ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ q.answers }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ q.views }}</td>
                        <td class="px-4 py-3 text-right">
                            <Button variant="destructive" size="sm" @click="destroy(q.id)">Устгах</Button>
                        </td>
                    </tr>
                    <tr v-if="!questions.data.length">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Асуулт алга байна.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="questions.links && questions.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <Link
                v-for="link in questions.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </AdminLayout>
</template>
