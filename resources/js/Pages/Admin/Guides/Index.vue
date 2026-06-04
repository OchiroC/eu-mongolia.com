<script setup>
import Button from '@/Components/ui/Button.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ guides: Object });

const statusLabel = { draft: 'Ноорог', published: 'Нийтлэгдсэн' };
const statusClass = { draft: 'bg-gray-100 text-gray-600', published: 'bg-green-100 text-green-700' };

function destroy(id) {
    if (confirm('Энэ Guide-ийг устгах уу?')) {
        router.delete(`/admin/guides/${id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Guide" />

    <AdminLayout>
        <template #title>Guide / Гарын авлага</template>

        <div class="mb-4 flex justify-end">
            <Button :as="Link" href="/admin/guides/create" size="sm">+ Шинэ Guide</Button>
        </div>

        <div class="overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Гарчиг</th>
                        <th class="px-4 py-3">Сэдэв</th>
                        <th class="px-4 py-3">Улс</th>
                        <th class="px-4 py-3">Төлөв</th>
                        <th class="px-4 py-3">Үзсэн</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="g in guides.data" :key="g.id">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ g.title }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ g.topic_label }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ g.country ?? 'Ерөнхий' }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs" :class="statusClass[g.status]">{{ statusLabel[g.status] }}</span></td>
                        <td class="px-4 py-3 text-gray-500">{{ g.views }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <Button :as="Link" :href="`/admin/guides/${g.id}/edit`" variant="ghost" size="icon" title="Засах">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </Button>
                                <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive" title="Устгах" @click="destroy(g.id)">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!guides.data.length">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Guide алга байна.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="guides.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <Link
                v-for="link in guides.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </AdminLayout>
</template>
