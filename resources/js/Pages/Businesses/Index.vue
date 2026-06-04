<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    businesses: Object,
    featured: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    cities: { type: Array, default: () => [] },
    filters: Object,
});

const user = computed(() => usePage().props.auth?.user);
const search = ref(props.filters.search ?? '');
let timer = null;
watch(search, (v) => { clearTimeout(timer); timer = setTimeout(() => go({ search: v || undefined }), 350); });

function go(params) {
    router.get('/businesses', { ...props.filters, ...params }, { preserveState: true, replace: true, preserveScroll: true });
}
function filterCategory(key) {
    go({ category: props.filters.category === key ? undefined : key });
}
const cityModel = computed({ get: () => props.filters.city ?? '', set: (v) => go({ city: v || undefined }) });

const icon = {
    restaurant: 'M3 3h18M5 3v18m14-18v18M9 8h6m-6 4h6m-6 4h6',
    cafe: 'M18 8h1a4 4 0 010 8h-1M3 8h15v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3',
    grocery: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293A1 1 0 005.414 17H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
    bakery: 'M12 2a7 7 0 017 7c0 2-1 3-1 5v6a1 1 0 01-1 1H8a1 1 0 01-1-1v-6c0-2-1-3-1-5a7 7 0 017-7z',
    beauty: 'M16 4l4 4-3 2v10H7V10L4 8l4-4 4 2 4-2z',
    retail: 'M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z',
    service: 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63',
    other: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
};
function initial(name) { return (name || '?').charAt(0).toUpperCase(); }
</script>

<template>
    <Head title="Монгол бизнес лавлах" />

    <PublicLayout>
        <div class="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 px-6 py-8 sm:px-10">
            <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-xl">
                    <h1 class="text-2xl font-bold text-white sm:text-3xl">Монгол бизнес лавлах</h1>
                    <p class="mt-2 text-sm text-brand-100 sm:text-base">Европ дахь монгол ресторан, дэлгүүр, бизнесүүд — хаана юу байгааг олоорой.</p>
                </div>
                <Link :href="user ? '/my/businesses' : '/login'" class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-gray-900 transition hover:bg-brand-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Бизнесээ нэмэх
                </Link>
            </div>
            <div class="relative mt-6 flex flex-col gap-2 rounded-2xl bg-white p-1.5 shadow-lg sm:flex-row">
                <input v-model="search" type="search" placeholder="Бизнес хайх..." class="min-w-0 flex-1 rounded-xl border-0 px-3 py-2.5 text-gray-900 placeholder-gray-400 focus:ring-0" />
                <select v-model="cityModel" class="rounded-xl border-0 px-3 py-2.5 text-gray-900 focus:ring-0 sm:w-40 sm:border-l sm:border-gray-100">
                    <option value="">Бүх хот</option>
                    <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
                </select>
            </div>
        </div>

        <!-- Ангилал tile -->
        <div class="mb-6 grid grid-cols-4 gap-2.5 sm:grid-cols-4 lg:grid-cols-8">
            <button class="flex flex-col items-center gap-1.5 rounded-2xl border p-3 text-center transition" :class="!filters.category ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-100 bg-white text-gray-600 hover:border-brand-200'" @click="filterCategory(null)">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" :d="icon.other" /></svg>
                <span class="text-[11px] font-medium">Бүгд</span>
            </button>
            <button
                v-for="c in categories"
                :key="c.key"
                class="flex flex-col items-center gap-1.5 rounded-2xl border p-3 text-center transition"
                :class="filters.category === c.key ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-100 bg-white text-gray-600 hover:border-brand-200'"
                @click="filterCategory(c.key)"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" :d="icon[c.key] || icon.other" /></svg>
                <span class="line-clamp-2 text-[11px] font-medium leading-tight">{{ c.label }}</span>
                <span class="text-[10px] text-gray-400">{{ c.count }}</span>
            </button>
        </div>

        <!-- Онцлох -->
        <div v-if="featured.length" class="mb-8">
            <div class="mb-3 flex items-center gap-1.5">
                <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.9 6.26L22 9.27l-5 4.87L18.18 22 12 18.27 5.82 22 7 14.14l-5-4.87 7.1-1.01L12 2z" /></svg>
                <h2 class="text-lg font-bold text-gray-900">Онцлох бизнес</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Link v-for="b in featured" :key="b.id" :href="`/businesses/${b.slug}`" class="group overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-soft ring-1 ring-amber-100 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700">
                        <img v-if="b.photo" :src="b.photo" :alt="b.name" class="h-full w-full object-cover transition group-hover:scale-105" />
                        <div v-else class="flex h-full w-full items-center justify-center text-5xl font-bold text-white/90">{{ initial(b.name) }}</div>
                        <span class="absolute left-2.5 top-2.5 rounded-full bg-amber-400 px-2 py-0.5 text-[10px] font-bold text-amber-900">Онцлох</span>
                    </div>
                    <div class="p-3.5">
                        <h3 class="truncate font-semibold text-gray-900 group-hover:text-brand-700">{{ b.name }}</h3>
                        <p class="mt-0.5 truncate text-xs text-gray-400">{{ b.category_label }} · {{ b.city }}</p>
                    </div>
                </Link>
            </div>
        </div>

        <div v-if="businesses.data.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link v-for="b in businesses.data" :key="b.id" :href="`/businesses/${b.slug}`" class="group flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-3 shadow-soft transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-100 text-xl font-bold text-brand-700">
                    <img v-if="b.photo" :src="b.photo" :alt="b.name" class="h-full w-full object-cover" /><template v-else>{{ initial(b.name) }}</template>
                </span>
                <div class="min-w-0">
                    <h3 class="truncate font-semibold text-gray-900 group-hover:text-brand-700">{{ b.name }}</h3>
                    <p class="truncate text-sm text-gray-500">{{ b.category_label }}</p>
                    <p class="text-xs text-gray-400">{{ b.city }}<span v-if="b.country">, {{ b.country }}</span></p>
                </div>
            </Link>
        </div>

        <div v-else class="rounded-3xl border border-dashed border-gray-200 bg-white py-16 text-center">
            <p class="font-medium text-gray-700">Бизнес олдсонгүй</p>
            <Link :href="user ? '/my/businesses' : '/login'" class="mt-2 inline-block font-medium text-brand-700 hover:underline">Анхны бизнесээ нэмэх →</Link>
        </div>

        <div v-if="businesses.links && businesses.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-1">
            <Link v-for="link in businesses.links" :key="link.label" :href="link.url || ''" v-html="link.label" class="rounded-md px-3 py-1 text-sm" :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']" />
        </div>
    </PublicLayout>
</template>
