<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Check, Plus } from 'lucide-vue-next';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    questions: Object,
    categories: { type: Array, default: () => [] },
    filters: Object,
});

const user = computed(() => usePage().props.auth?.user);
const search = ref(props.filters.search ?? '');
let timer = null;
watch(search, (v) => {
    clearTimeout(timer);
    timer = setTimeout(() => go({ search: v || undefined }), 350);
});

function go(params) {
    router.get('/questions', { ...props.filters, ...params }, { preserveState: true, replace: true, preserveScroll: true });
}
function filterCategory(key) {
    go({ category: props.filters.category === key ? undefined : key });
}
function setSort(s) {
    go({ sort: s });
}
const sorts = [
    { key: 'latest', label: 'Шинэ' },
    { key: 'unanswered', label: 'Хариултгүй' },
    { key: 'popular', label: 'Идэвхтэй' },
];
</script>

<template>
    <Head title="Асуулт хариулт" />

    <PublicLayout>
        <PageHeader kicker="Нийгэмлэг" title="Асуулт хариулт" subtitle="Асуултаа тавьж, туршлагатай хүмүүсээс хариулт аваарай.">
            <template #actions>
                <Link :href="user ? '/questions/ask' : '/login'" class="inline-flex h-10 items-center gap-1.5 rounded-md bg-brand-600 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-500">
                    <Plus class="h-4 w-4" /> Асуулт асуух
                </Link>
            </template>
        </PageHeader>

        <input v-model="search" type="search" placeholder="Асуулт хайх..." class="mb-4 w-full rounded-md border-brand-200 focus:border-brand-600 focus:ring-1 focus:ring-brand-600" />

        <div class="mb-4 flex flex-wrap gap-2">
            <button class="rounded-md px-3 py-1 text-sm transition" :class="!filters.category ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'" @click="filterCategory(null)">Бүгд</button>
            <button
                v-for="c in categories"
                :key="c.key"
                class="rounded-md px-3 py-1 text-sm transition"
                :class="filters.category === c.key ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterCategory(c.key)"
            >{{ c.label }} <span class="text-xs opacity-70">{{ c.count }}</span></button>
        </div>

        <div class="mb-6 flex gap-2 border-b border-gray-100">
            <button
                v-for="s in sorts"
                :key="s.key"
                class="-mb-px border-b-2 px-3 py-2 text-sm font-medium transition"
                :class="filters.sort === s.key ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-800'"
                @click="setSort(s.key)"
            >{{ s.label }}</button>
        </div>

        <div v-if="questions.data.length" class="space-y-3">
            <Link
                v-for="q in questions.data"
                :key="q.id"
                :href="`/questions/${q.slug}`"
                class="group flex gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-card transition duration-300 hover:shadow-card-lg"
            >
                <div class="flex w-14 shrink-0 flex-col items-center justify-center rounded-xl text-center" :class="q.solved ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-50 text-gray-500'">
                    <span class="text-lg font-bold">{{ q.answers }}</span>
                    <span class="text-[10px]">хариулт</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rounded-md bg-brand-50 px-2 py-0.5 text-[11px] font-medium text-brand-700">{{ q.category_label }}</span>
                        <span v-if="q.solved" class="inline-flex items-center gap-0.5 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700"><Check class="h-3 w-3" />Шийдсэн</span>
                    </div>
                    <h2 class="mt-1 font-semibold text-gray-900 group-hover:text-brand-700">{{ q.title }}</h2>
                    <p class="mt-0.5 line-clamp-1 text-sm text-gray-500">{{ q.excerpt }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ q.user }} · {{ q.created_at }} · {{ q.views }} үзсэн</p>
                </div>
            </Link>
        </div>

        <div v-else class="rounded-3xl border border-dashed border-gray-200 bg-white py-16 text-center">
            <p class="font-medium text-gray-700">Асуулт олдсонгүй</p>
            <Link :href="user ? '/questions/ask' : '/login'" class="mt-2 inline-block font-medium text-brand-700 hover:underline">Анхны асуултаа асуу →</Link>
        </div>

        <div v-if="questions.links && questions.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-1">
            <Link
                v-for="link in questions.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[link.active ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </PublicLayout>
</template>
