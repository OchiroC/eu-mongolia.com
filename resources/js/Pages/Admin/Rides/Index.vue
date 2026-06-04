<script setup>
import Button from '@/Components/ui/Button.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ rides: Object });

const statusLabel = { active: 'Идэвхтэй', closed: 'Хаагдсан' };
const statusClass = { active: 'bg-green-100 text-green-700', closed: 'bg-gray-100 text-gray-600' };

function close(id) {
    router.post(`/admin/rides/${id}/close`, {}, { preserveScroll: true });
}
function destroy(id) {
    if (confirm('Энэ аяллыг устгах уу?')) router.delete(`/admin/rides/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Аялал" />

    <AdminLayout>
        <template #title>Аялал (carpool)</template>

        <div class="overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Чиглэл</th>
                        <th class="px-4 py-3">Хөдлөх</th>
                        <th class="px-4 py-3">Суудал</th>
                        <th class="px-4 py-3">Жолооч</th>
                        <th class="px-4 py-3">Төлөв</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="r in rides.data" :key="r.id">
                        <td class="px-4 py-3">
                            <Link :href="`/rides/${r.id}`" target="_blank" class="font-medium text-gray-800 hover:text-brand-700">{{ r.route }}</Link>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ r.depart_at }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ r.seats }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ r.user ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs" :class="statusClass[r.status]">{{ statusLabel[r.status] }}</span></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <Button variant="outline" size="sm" @click="close(r.id)">{{ r.status === 'closed' ? 'Нээх' : 'Хаах' }}</Button>
                                <Button variant="destructive" size="sm" @click="destroy(r.id)">Устгах</Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!rides.data.length">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Аялал алга байна.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="rides.links && rides.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <Link
                v-for="link in rides.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </AdminLayout>
</template>
