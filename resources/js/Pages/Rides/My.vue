<script setup>
import Button from '@/Components/ui/Button.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDateTime } from '@/lib/date';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ rides: Array });

const statusLabel = { active: 'Идэвхтэй', closed: 'Хаагдсан' };
const statusClass = { active: 'bg-emerald-50 text-emerald-700', closed: 'bg-gray-100 text-gray-600' };

function close(r) {
    router.post(`/rides/${r.id}/close`, {}, { preserveScroll: true });
}
function destroy(r) {
    if (confirm('Энэ аяллыг устгах уу?')) router.delete(`/rides/${r.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Миний аялал" />

    <PublicLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Миний аялал</h1>
            <Button :as="Link" href="/rides/new">+ Аялал нэмэх</Button>
        </div>

        <div v-if="rides.length" class="space-y-3">
            <div v-for="r in rides" :key="r.id" class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-gray-100 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass[r.status]">{{ statusLabel[r.status] }}</span>
                    </div>
                    <Link :href="`/rides/${r.id}`" class="mt-1 block font-semibold text-gray-900 hover:text-brand-700">{{ r.from_city }} → {{ r.to_city }}</Link>
                    <p class="text-sm text-gray-500">🕒 {{ formatDateTime(r.depart_at) }} · {{ r.seats }} суудал<span v-if="r.price"> · {{ r.price }}</span></p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <Button variant="outline" size="sm" @click="close(r)">{{ r.status === 'closed' ? 'Дахин нээх' : 'Хаах' }}</Button>
                    <Button :as="Link" :href="`/rides/${r.id}/edit`" variant="secondary" size="sm">Засах</Button>
                    <Button variant="destructive" size="sm" @click="destroy(r)">Устгах</Button>
                </div>
            </div>
        </div>

        <div v-else class="rounded-2xl bg-white py-16 text-center shadow-soft ring-1 ring-gray-100">
            <p class="text-gray-500">Та одоогоор аяллын зар нийтлээгүй байна.</p>
            <Link href="/rides/new" class="mt-3 inline-block font-semibold text-brand-700 hover:underline">Анхны зараа нэмэх →</Link>
        </div>
    </PublicLayout>
</template>
