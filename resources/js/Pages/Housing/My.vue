<script setup>
import Button from '@/Components/ui/Button.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ posts: Array });

const statusLabel = { active: 'Идэвхтэй', closed: 'Хаагдсан' };
const statusClass = { active: 'bg-emerald-50 text-emerald-700', closed: 'bg-gray-100 text-gray-600' };

function close(p) {
    router.post(`/housing/${p.id}/close`, {}, { preserveScroll: true });
}
function destroy(p) {
    if (confirm('Энэ зарыг устгах уу?')) router.delete(`/housing/${p.id}`, { preserveScroll: true });
}
function price(p) {
    return p.price ? Number(p.price).toLocaleString('mn-MN') + '€/сар' : 'Тохиролцоно';
}
</script>

<template>
    <Head title="Миний орон сууцны зар" />

    <PublicLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Миний орон сууцны зар</h1>
            <Button :as="Link" href="/housing/new">+ Зар нэмэх</Button>
        </div>

        <div v-if="posts.length" class="space-y-3">
            <div v-for="p in posts" :key="p.id" class="flex flex-col gap-4 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-gray-100 sm:flex-row sm:items-center">
                <Link :href="`/housing/${p.slug}`" class="h-20 w-28 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                    <img v-if="p.cover" :src="p.cover" class="h-full w-full object-cover" />
                </Link>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass[p.status]">{{ statusLabel[p.status] }}</span>
                        <span class="text-xs text-gray-400">👁 {{ p.views }}</span>
                    </div>
                    <Link :href="`/housing/${p.slug}`" class="mt-1 block truncate font-semibold text-gray-900 hover:text-brand-700">{{ p.title }}</Link>
                    <p class="text-sm text-gray-500">{{ price(p) }} · {{ p.type_label }} · {{ p.city }}</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <Button variant="outline" size="sm" @click="close(p)">{{ p.status === 'closed' ? 'Дахин нээх' : 'Хаах' }}</Button>
                    <Button :as="Link" :href="`/housing/${p.id}/edit`" variant="secondary" size="sm">Засах</Button>
                    <Button variant="destructive" size="sm" @click="destroy(p)">Устгах</Button>
                </div>
            </div>
        </div>

        <div v-else class="rounded-2xl bg-white py-16 text-center shadow-soft ring-1 ring-gray-100">
            <p class="text-gray-500">Та одоогоор зар нийтлээгүй байна.</p>
            <Link href="/housing/new" class="mt-3 inline-block font-semibold text-brand-700 hover:underline">Анхны зараа нэмэх →</Link>
        </div>
    </PublicLayout>
</template>
