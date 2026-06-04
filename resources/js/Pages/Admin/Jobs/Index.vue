<script setup>
import Button from '@/Components/ui/Button.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ jobs: Object });

const statusLabel = { active: 'Идэвхтэй', closed: 'Хаагдсан' };
const statusClass = { active: 'bg-green-100 text-green-700', closed: 'bg-gray-100 text-gray-600' };

function close(id) {
    router.post(`/admin/jobs/${id}/close`, {}, { preserveScroll: true });
}
function destroy(id) {
    if (confirm('Энэ ажлын зарыг устгах уу?')) {
        router.delete(`/admin/jobs/${id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Ажлын байр" />

    <AdminLayout>
        <template #title>Ажлын байр</template>

        <div class="overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Албан тушаал</th>
                        <th class="px-4 py-3">Ангилал</th>
                        <th class="px-4 py-3">Байршил</th>
                        <th class="px-4 py-3">Нийтлэгч</th>
                        <th class="px-4 py-3">Төлөв</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="j in jobs.data" :key="j.id">
                        <td class="px-4 py-3">
                            <Link :href="`/jobs/${j.slug}`" target="_blank" class="font-medium text-gray-800 hover:text-brand-700">{{ j.title }}</Link>
                            <p v-if="j.company" class="text-xs text-gray-400">{{ j.company }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ j.category_label }} · {{ j.type_label }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ j.city ?? '—' }}<span v-if="j.country">, {{ j.country }}</span></td>
                        <td class="px-4 py-3 text-gray-500">{{ j.author ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs" :class="statusClass[j.status]">{{ statusLabel[j.status] }}</span></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <Button variant="outline" size="sm" @click="close(j.id)">{{ j.status === 'closed' ? 'Нээх' : 'Хаах' }}</Button>
                                <Button variant="destructive" size="sm" @click="destroy(j.id)">Устгах</Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!jobs.data.length">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Ажлын зар алга байна.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="jobs.links && jobs.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <Link
                v-for="link in jobs.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </AdminLayout>
</template>
