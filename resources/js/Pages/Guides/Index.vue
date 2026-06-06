<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    guides: Object,
    topics: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
    filters: Object,
});

const search = ref(props.filters.search ?? '');
let timer = null;
watch(search, (v) => {
    clearTimeout(timer);
    timer = setTimeout(() => go({ search: v || undefined }), 350);
});

function go(params) {
    router.get('/guides', { ...props.filters, ...params }, { preserveState: true, replace: true, preserveScroll: true });
}
function filterTopic(key) {
    go({ topic: props.filters.topic === key ? undefined : key });
}
function filterCountry(c) {
    go({ country: c || undefined });
}

const topicIcon = {
    visa: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    registration: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
    insurance: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    tax: 'M9 7h6m-6 4h6m-2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2h-1m-4 0v-4m0 0a2 2 0 100-4 2 2 0 000 4z',
    work: 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    study: 'M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.42a12 12 0 01.84 4.42 12 12 0 01-14 0 12 12 0 01.84-4.42L12 14z',
    health: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
    driving: 'M8 7h8l1.5 4.5M6.5 11.5L8 7M5 11h14a1 1 0 011 1v4a1 1 0 01-1 1h-1m-3 0H9m-3 0H5a1 1 0 01-1-1v-4a1 1 0 011-1m2 6a1 1 0 102 0m6 0a1 1 0 102 0',
    bank: 'M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11m16-11v11M8 14v3m4-3v3m4-3v3',
    housing: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    other: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};
const activeTopicLabel = computed(() => props.topics.find((t) => t.key === props.filters.topic)?.label);
const countryModel = computed({ get: () => props.filters.country ?? '', set: (v) => filterCountry(v) });
</script>

<template>
    <Head title="Guide / Гарын авлага" />

    <PublicLayout>
        <!-- Hero -->
        <div class="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 px-6 py-8 sm:px-10">
            <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative max-w-xl">
                <h1 class="text-2xl font-bold text-white sm:text-3xl">Guide / Гарын авлага</h1>
                <p class="mt-2 text-sm text-brand-100 sm:text-base">Европод виз, бүртгэл, даатгал, татвар, жолооны үнэмлэх зэрэг асуудлыг алхам алхмаар шийдэх практик гарын авлага.</p>
            </div>
            <div class="relative mt-5 rounded-2xl bg-white p-1.5 shadow-lg sm:max-w-md">
                <input v-model="search" type="search" placeholder="Guide хайх..." class="w-full rounded-xl border-0 px-3 py-2.5 text-gray-900 placeholder-gray-400 focus:ring-0" />
            </div>
        </div>

        <!-- Сэдвийн tile -->
        <div class="mb-5 grid grid-cols-3 gap-2.5 sm:grid-cols-4 lg:grid-cols-6">
            <button
                v-for="t in topics"
                :key="t.key"
                class="flex flex-col items-center gap-1.5 rounded-2xl border p-3 text-center transition"
                :class="filters.topic === t.key ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-100 bg-white text-gray-600 hover:border-brand-200 hover:bg-brand-50/40'"
                @click="filterTopic(t.key)"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" :d="topicIcon[t.key] || topicIcon.other" /></svg>
                <span class="line-clamp-2 text-[11px] font-medium leading-tight">{{ t.label }}</span>
                <span class="text-[10px] text-gray-400">{{ t.count }}</span>
            </button>
        </div>

        <!-- Улсаар шүүх + идэвхтэй сэдэв -->
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <select v-model="countryModel" class="rounded-lg border-gray-300 text-sm">
                <option value="">Бүх улс</option>
                <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
            </select>
            <span v-if="activeTopicLabel" class="inline-flex items-center gap-1.5 rounded-full bg-brand-600 px-3 py-1 text-sm font-medium text-white">
                {{ activeTopicLabel }}
                <button type="button" class="text-brand-200 hover:text-white" @click="filterTopic(filters.topic)">✕</button>
            </span>
        </div>

        <div v-if="guides.data.length" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="g in guides.data"
                :key="g.id"
                :href="`/guides/${g.slug}`"
                class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-card-lg"
            >
                <div v-if="g.cover_image" class="aspect-[16/9] overflow-hidden bg-gray-100">
                    <img :src="g.cover_image" :alt="g.title" class="h-full w-full object-cover" />
                </div>
                <div class="flex flex-1 flex-col p-4">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-medium text-brand-700">{{ g.topic_label }}</span>
                        <span v-if="g.country" class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-500">{{ g.country }}</span>
                    </div>
                    <h2 class="mt-2 font-semibold text-gray-900 group-hover:text-brand-700">{{ g.title }}</h2>
                    <p v-if="g.excerpt" class="mt-1 line-clamp-2 text-sm text-gray-500">{{ g.excerpt }}</p>
                </div>
            </Link>
        </div>

        <div v-else class="rounded-3xl border border-dashed border-gray-200 bg-white py-16 text-center">
            <p class="font-medium text-gray-700">Guide олдсонгүй</p>
            <p class="text-sm text-gray-400">Сэдэв эсвэл улсаа өөрчилж үзнэ үү.</p>
        </div>

        <div v-if="guides.links && guides.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-1">
            <Link
                v-for="link in guides.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </PublicLayout>
</template>
