<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDate } from '@/lib/date';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Plus } from 'lucide-vue-next';

defineProps({ parcels: { type: Array, default: () => [] } });

function close(id) {
    router.post(`/achaa/${id}/close`, {}, { preserveScroll: true });
}
function destroy(id) {
    if (confirm('Энэ зарыг устгах уу?')) router.delete(`/achaa/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Миний ачааны зар" />

    <PublicLayout>
        <PageHeader kicker="Ачаа" title="Миний ачааны зар">
            <template #actions>
                <Link href="/achaa/new" class="inline-flex h-10 items-center gap-1.5 rounded-md bg-brand-600 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-500">
                    <Plus class="h-4 w-4" /> Зар нэмэх
                </Link>
            </template>
        </PageHeader>

        <ul v-if="parcels.length" class="border-t border-brand-100">
            <li v-for="p in parcels" :key="p.id" class="flex flex-col gap-3 border-b border-brand-100 py-4 sm:flex-row sm:items-center sm:justify-between">
                <Link :href="`/achaa/${p.id}`" class="min-w-0">
                    <span class="kicker block">{{ p.type_label }} · {{ p.direction_label }}</span>
                    <span class="mt-1 block font-medium text-brand-600 hover:underline">{{ p.from_city }} → {{ p.to_city }}</span>
                    <span class="tabular mt-1 block font-mono text-xs text-brand-400">
                        <template v-if="p.flight">{{ p.flight.code }} · </template>{{ formatDate(p.date) }} ·
                        <Eye class="inline h-3 w-3 align-[-1px]" /> {{ p.views }}
                        <span v-if="p.status === 'closed'"> · Хаагдсан</span>
                    </span>
                </Link>
                <div class="flex gap-2">
                    <Link :href="`/achaa/${p.id}/edit`" class="inline-flex h-9 items-center rounded-md border border-brand-200 px-3 text-sm text-brand-600 hover:border-brand-600">Засах</Link>
                    <button type="button" class="inline-flex h-9 items-center rounded-md border border-brand-200 px-3 text-sm text-brand-600 hover:border-brand-600" @click="close(p.id)">{{ p.status === 'closed' ? 'Нээх' : 'Хаах' }}</button>
                    <button type="button" class="inline-flex h-9 items-center rounded-md border border-red-200 px-3 text-sm text-red-600 hover:border-red-600" @click="destroy(p.id)">Устгах</button>
                </div>
            </li>
        </ul>
        <div v-else class="border border-brand-100 px-6 py-16 text-center">
            <p class="font-medium text-brand-600">Та ачааны зар нийтлээгүй байна</p>
            <Link href="/achaa/new" class="mt-2 inline-block text-sm font-medium text-brand-600 underline underline-offset-2">Эхний зараа нэмэх</Link>
        </div>
    </PublicLayout>
</template>
