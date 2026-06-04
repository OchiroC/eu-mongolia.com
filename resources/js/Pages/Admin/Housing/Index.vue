<script setup>
import Button from '@/Components/ui/Button.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ posts: Object });

const statusLabel = { active: 'Идэвхтэй', closed: 'Хаагдсан' };
const statusClass = { active: 'bg-green-100 text-green-700', closed: 'bg-gray-100 text-gray-600' };

function close(id) {
    router.post(`/admin/housing/${id}/close`, {}, { preserveScroll: true });
}
function destroy(id) {
    if (confirm('Энэ зарыг устгах уу?')) router.delete(`/admin/housing/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Орон сууц" />

    <AdminLayout>
        <template #title>Орон сууц</template>

        <div class="overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Гарчиг</th>
                        <th class="px-4 py-3">Төрөл</th>
                        <th class="px-4 py-3">Хот</th>
                        <th class="px-4 py-3">Үнэ</th>
                        <th class="px-4 py-3">Нийтлэгч</th>
                        <th class="px-4 py-3">Төлөв</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="p in posts.data" :key="p.id">
                        <td class="px-4 py-3"><Link :href="`/housing/${p.slug}`" target="_blank" class="font-medium text-gray-800 hover:text-brand-700">{{ p.title }}</Link></td>
                        <td class="px-4 py-3 text-gray-500">{{ p.type_label }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ p.city }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ p.price ? p.price + '€' : '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ p.user ?? '—' }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-0.5 text-xs" :class="statusClass[p.status]">{{ statusLabel[p.status] }}</span></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <Button variant="outline" size="sm" @click="close(p.id)">{{ p.status === 'closed' ? 'Нээх' : 'Хаах' }}</Button>
                                <Button variant="destructive" size="sm" @click="destroy(p.id)">Устгах</Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!posts.data.length">
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Зар алга байна.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="posts.links && posts.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <Link
                v-for="link in posts.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </AdminLayout>
</template>
