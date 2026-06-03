<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ banners: Object });

const placementLabels = {
    home_top: 'Нүүр-дээд',
    home_sidebar: 'Нүүр-хажуу',
    news_top: 'Мэдээ-дээд',
    footer: 'Хөл',
};

const statusLabels = {
    pending: 'Хүлээгдэж буй',
    active: 'Идэвхтэй',
    rejected: 'Татгалзсан',
    expired: 'Дууссан',
};

const statusClass = {
    pending: 'bg-yellow-100 text-yellow-700',
    active: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
    expired: 'bg-gray-100 text-gray-600',
};

function pay(id) {
    router.post(`/admin/banners/${id}/pay`, {}, { preserveScroll: true });
}

function setStatus(id, status) {
    router.patch(`/admin/banners/${id}/status`, { status }, { preserveScroll: true });
}

function destroy(id) {
    if (confirm('Энэ баннерыг устгах уу?')) {
        router.delete(`/admin/banners/${id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Баннер удирдах" />

    <AdminLayout>
        <template #title>Зар / Баннер</template>

        <div class="mb-4 flex justify-end">
            <Button :as="Link" href="/admin/banners/create" size="sm">+ Шинэ баннер</Button>
        </div>

        <div class="overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Баннер</th>
                        <th class="px-4 py-3">Байршил</th>
                        <th class="px-4 py-3">Төлөв</th>
                        <th class="px-4 py-3">Төлбөр</th>
                        <th class="px-4 py-3">Үзэлт/Клик</th>
                        <th class="px-4 py-3">Дуусах</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="b in banners.data" :key="b.id">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ b.title }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ placementLabels[b.placement] }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs" :class="statusClass[b.status]">
                                {{ statusLabels[b.status] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="b.is_paid" class="text-green-600">✓ {{ b.price }}€</span>
                            <button v-else class="rounded bg-emerald-600 px-2 py-1 text-xs text-white hover:bg-emerald-700" @click="pay(b.id)">
                                Төлбөр авах
                            </button>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ b.impressions }} / {{ b.clicks }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ b.ends_at ?? '—' }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <button v-if="b.status === 'pending'" class="text-green-700 hover:underline" @click="setStatus(b.id, 'active')">Зөвшөөрөх</button>
                            <button v-if="b.status === 'pending'" class="ml-2 text-red-600 hover:underline" @click="setStatus(b.id, 'rejected')">Татгалзах</button>
                            <Link :href="`/admin/banners/${b.id}/edit`" class="ml-2 text-brand-700 hover:underline">Засах</Link>
                            <button class="ml-2 text-red-600 hover:underline" @click="destroy(b.id)">Устгах</button>
                        </td>
                    </tr>
                    <tr v-if="!banners.data.length">
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Баннер алга байна.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="banners.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <Link
                v-for="link in banners.links"
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
