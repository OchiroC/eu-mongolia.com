<script setup>
import Pagination from '@/Components/Pagination.vue';
import Button from '@/Components/ui/Button.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ parcels: Object });

const statusLabel = { active: 'Идэвхтэй', closed: 'Хаагдсан' };
const statusClass = { active: 'bg-green-100 text-green-700', closed: 'bg-gray-100 text-gray-600' };

function close(id) {
    router.post(`/admin/parcels/${id}/close`, {}, { preserveScroll: true });
}
function destroy(id) {
    if (confirm('Энэ зарыг устгах уу?')) router.delete(`/admin/parcels/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Ачаа" />

    <AdminLayout>
        <template #title>Ачаа, илгээмж</template>

        <div class="overflow-x-auto rounded-md border border-brand-100 bg-white">
            <table class="min-w-full text-sm">
                <thead class="text-left text-xs uppercase text-gray-500">
                    <tr class="border-b border-brand-100">
                        <th class="px-4 py-3">Төрөл</th><th class="px-4 py-3">Чиглэл</th><th class="px-4 py-3">Огноо</th><th class="px-4 py-3">Хэрэглэгч</th><th class="px-4 py-3">Төлөв</th><th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in parcels.data" :key="p.id" class="border-b border-brand-100 last:border-b-0">
                        <td class="px-4 py-3 text-gray-600">{{ p.type }}</td>
                        <td class="px-4 py-3"><Link :href="`/achaa/${p.id}`" target="_blank" class="font-medium hover:underline">{{ p.route }}</Link></td>
                        <td class="tabular px-4 py-3 font-mono text-gray-500">{{ p.date ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ p.user ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="rounded-md px-2 py-0.5 text-xs" :class="statusClass[p.status]">{{ statusLabel[p.status] }}</span></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <Button variant="outline" size="sm" @click="close(p.id)">{{ p.status === 'closed' ? 'Нээх' : 'Хаах' }}</Button>
                                <Button variant="destructive" size="sm" @click="destroy(p.id)">Устгах</Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!parcels.data.length"><td colspan="6" class="px-4 py-8 text-center text-gray-400">Ачааны зар алга.</td></tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="parcels.links" />
    </AdminLayout>
</template>
