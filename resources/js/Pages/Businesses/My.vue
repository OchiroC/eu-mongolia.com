<script setup>
import Button from '@/Components/ui/Button.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ businesses: Array });

const statusMap = {
    pending: { label: 'Хяналтад', cls: 'bg-amber-100 text-amber-700' },
    active: { label: 'Нийтлэгдсэн', cls: 'bg-emerald-100 text-emerald-700' },
    inactive: { label: 'Идэвхгүй', cls: 'bg-gray-100 text-gray-600' },
};

function promote(b) {
    if (confirm('30 хоног онцлох болгох уу? (mock төлбөр)')) {
        router.post(`/businesses/${b.id}/promote`, {}, { preserveScroll: true });
    }
}
function destroy(b) {
    if (confirm('Энэ бизнесийг устгах уу?')) router.delete(`/businesses/${b.id}`, { preserveScroll: true });
}
function initial(name) { return (name || '?').charAt(0).toUpperCase(); }
</script>

<template>
    <Head title="Миний бизнес" />

    <PublicLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Миний бизнес</h1>
            <Button :as="Link" href="/businesses/new">+ Бизнес нэмэх</Button>
        </div>

        <div v-if="businesses.length" class="space-y-3">
            <div v-for="b in businesses" :key="b.id" class="flex flex-col gap-4 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-gray-100 sm:flex-row sm:items-center">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-100 font-bold text-brand-700">
                    <img v-if="b.photo" :src="b.photo" alt="" class="h-full w-full object-cover" /><template v-else>{{ initial(b.name) }}</template>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusMap[b.status]?.cls">{{ statusMap[b.status]?.label }}</span>
                        <span v-if="b.is_featured" class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">★ Онцлох · {{ b.featured_until }}</span>
                        <span class="text-xs text-gray-400">👁 {{ b.views }}</span>
                    </div>
                    <Link :href="`/businesses/${b.slug}`" class="mt-1 block font-semibold text-gray-900 hover:text-brand-700">{{ b.name }}</Link>
                    <p class="text-sm text-gray-500">{{ b.category_label }} · {{ b.city }}</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <Button size="sm" class="bg-amber-500 hover:bg-amber-600" @click="promote(b)">★ {{ b.is_featured ? 'Сунгах' : 'Онцлох' }}</Button>
                    <Button :as="Link" :href="`/businesses/${b.id}/edit`" variant="secondary" size="sm">Засах</Button>
                    <Button variant="destructive" size="sm" @click="destroy(b)">Устгах</Button>
                </div>
            </div>
        </div>

        <div v-else class="rounded-2xl bg-white py-16 text-center shadow-soft ring-1 ring-gray-100">
            <p class="text-gray-500">Та одоогоор бизнес нэмээгүй байна.</p>
            <Link href="/businesses/new" class="mt-3 inline-block font-semibold text-brand-700 hover:underline">Бизнесээ нэмэх →</Link>
        </div>
    </PublicLayout>
</template>
