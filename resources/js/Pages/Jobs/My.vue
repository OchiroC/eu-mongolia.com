<script setup>
import Button from '@/Components/ui/Button.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ jobs: Array });

const statusLabel = { active: 'Идэвхтэй', closed: 'Хаагдсан' };
const statusClass = { active: 'bg-emerald-50 text-emerald-700', closed: 'bg-gray-100 text-gray-600' };

function close(j) {
    router.post(`/jobs/${j.id}/close`, {}, { preserveScroll: true });
}
function destroy(j) {
    if (confirm('Энэ зарыг устгах уу?')) {
        router.delete(`/jobs/${j.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Миний ажлын зар" />

    <PublicLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Миний ажлын зар</h1>
            <Button :as="Link" href="/jobs/new">+ Зар нэмэх</Button>
        </div>

        <div v-if="jobs.length" class="space-y-3">
            <div v-for="j in jobs" :key="j.id" class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-gray-100 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass[j.status]">{{ statusLabel[j.status] }}</span>
                        <span class="text-xs text-gray-400">👁 {{ j.views }}</span>
                    </div>
                    <Link :href="`/jobs/${j.slug}`" class="mt-1 block truncate font-semibold text-gray-900 hover:text-brand-700">{{ j.title }}</Link>
                    <p class="text-sm text-gray-500">{{ j.type_label }} · {{ j.category_label }}<span v-if="j.city"> · {{ j.city }}</span></p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <Button variant="outline" size="sm" @click="close(j)">{{ j.status === 'closed' ? 'Дахин нээх' : 'Хаах' }}</Button>
                    <Button :as="Link" :href="`/jobs/${j.id}/edit`" variant="secondary" size="sm">Засах</Button>
                    <Button variant="destructive" size="sm" @click="destroy(j)">Устгах</Button>
                </div>
            </div>
        </div>

        <div v-else class="rounded-2xl bg-white py-16 text-center shadow-soft ring-1 ring-gray-100">
            <p class="text-gray-500">Та одоогоор ажлын зар нийтлээгүй байна.</p>
            <Link href="/jobs/new" class="mt-3 inline-block font-semibold text-brand-700 hover:underline">Анхны зараа нэмэх →</Link>
        </div>
    </PublicLayout>
</template>
