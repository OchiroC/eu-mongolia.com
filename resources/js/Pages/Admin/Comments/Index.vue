<script setup>
import Button from '@/Components/ui/Button.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    comments: Object,
    filter: String,
    counts: Object,
});

const tabs = [
    { key: 'pending', label: 'Хүлээгдэж буй' },
    { key: 'approved', label: 'Зөвшөөрсөн' },
    { key: 'spam', label: 'Спам' },
];

function go(status) {
    router.get('/admin/comments', { status }, { preserveScroll: true, preserveState: true });
}
function approve(id) {
    router.post(`/admin/comments/${id}/approve`, {}, { preserveScroll: true });
}
function spam(id) {
    router.post(`/admin/comments/${id}/spam`, {}, { preserveScroll: true });
}
function destroy(id) {
    if (confirm('Сэтгэгдлийг бүрмөсөн устгах уу?')) {
        router.delete(`/admin/comments/${id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Сэтгэгдэл" />

    <AdminLayout>
        <template #title>Сэтгэгдлийн модерац</template>

        <!-- Шүүлтүүр -->
        <div class="mb-5 flex flex-wrap gap-2">
            <button
                v-for="t in tabs"
                :key="t.key"
                class="rounded-full px-4 py-1.5 text-sm font-medium transition"
                :class="filter === t.key ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50'"
                @click="go(t.key)"
            >
                {{ t.label }}
                <span class="ml-1 text-xs opacity-80">{{ counts[t.key] }}</span>
            </button>
        </div>

        <div v-if="comments.data.length" class="space-y-3">
            <div v-for="c in comments.data" :key="c.id" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-400">
                            <span class="font-semibold text-gray-700">{{ c.user }}</span>
                            <span v-if="c.is_reply" class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-500">хариу</span>
                            <span>· {{ c.created_at }}</span>
                        </div>
                        <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ c.body }}</p>
                        <Link v-if="c.post" :href="`/news/${c.post.slug}#comments`" target="_blank" class="mt-2 inline-block text-xs text-brand-600 hover:underline">
                            → {{ c.post.title }}
                        </Link>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-2">
                        <Button v-if="c.status !== 'approved'" size="sm" @click="approve(c.id)">Зөвшөөрөх</Button>
                        <Button v-if="c.status !== 'spam'" variant="outline" size="sm" @click="spam(c.id)">Спам</Button>
                        <Button variant="destructive" size="sm" @click="destroy(c.id)">Устгах</Button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="rounded-2xl bg-white py-16 text-center shadow-sm ring-1 ring-gray-100">
            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.5C3.5 15.3 3 13.7 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
            <p class="mt-3 font-medium text-gray-700">Сэтгэгдэл алга</p>
        </div>

        <div v-if="comments.links && comments.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <Link
                v-for="link in comments.links"
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
