<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from '@/components/ui/Button.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ events: Object });

const statusLabels = { draft: 'Ноорог', published: 'Нийтлэгдсэн', cancelled: 'Цуцлагдсан' };
const statusClass = {
    draft: 'bg-gray-100 text-gray-600',
    published: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700',
};

function destroy(id) {
    if (confirm('Энэ эвентийг устгах уу? (тасалбарууд хамт устана)')) {
        router.delete(`/admin/events/${id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Эвент удирдах" />

    <AdminLayout>
        <template #title>Эвент</template>

        <div class="mb-4 flex justify-end">
            <Button :as="Link" href="/admin/events/create" size="sm">+ Шинэ эвент</Button>
        </div>

        <div class="overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Эвент</th>
                        <th class="px-4 py-3">Хот</th>
                        <th class="px-4 py-3">Огноо</th>
                        <th class="px-4 py-3">Төлөв</th>
                        <th class="px-4 py-3">Зарагдсан</th>
                        <th class="px-4 py-3">Захиалга</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="e in events.data" :key="e.id">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-14 shrink-0 overflow-hidden rounded bg-gray-100">
                                    <img v-if="e.cover_image" :src="e.cover_image" alt="" class="h-full w-full object-cover" />
                                    <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                </div>
                                <span class="font-medium text-gray-800">{{ e.title }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ e.city ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ e.starts_at }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs" :class="statusClass[e.status]">{{ statusLabels[e.status] }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ e.sold }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ e.orders_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <Button :as="Link" :href="`/admin/events/${e.id}/sales`" variant="ghost" size="icon" title="Борлуулалт / хяналт">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                </Button>
                                <Button :as="Link" :href="`/admin/events/${e.id}/edit`" variant="ghost" size="icon" title="Засах">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </Button>
                                <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive" title="Устгах" @click="destroy(e.id)">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!events.data.length">
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Эвент алга байна.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="events.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <Link
                v-for="link in events.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[
                    link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200',
                    !link.url ? 'pointer-events-none opacity-50' : '',
                ]"
            />
        </div>
    </AdminLayout>
</template>
