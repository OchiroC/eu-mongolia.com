<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { timeAgo } from '@/lib/date';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    jobs: Object,
    categories: { type: Array, default: () => [] },
    types: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
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
    router.get('/jobs', { ...props.filters, ...params }, { preserveState: true, replace: true, preserveScroll: true });
}
function filterCategory(key) {
    go({ category: props.filters.category === key ? undefined : key });
}
const typeModel = computed({ get: () => props.filters.type ?? '', set: (v) => go({ type: v || undefined }) });
const countryModel = computed({ get: () => props.filters.country ?? '', set: (v) => go({ country: v || undefined }) });
</script>

<template>
    <Head title="Ажлын байр" />

    <PublicLayout>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Ажлын байр</h1>
                <p class="mt-1 text-sm text-gray-500">Европ дахь монголчуудад зориулсан ажлын зар.</p>
            </div>
            <Link :href="user ? '/jobs/new' : '/login'" class="inline-flex w-fit items-center gap-1.5 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Ажлын зар нэмэх
            </Link>
        </div>

        <!-- Хайлт + шүүлт -->
        <div class="mb-4 flex flex-col gap-2 sm:flex-row">
            <input v-model="search" type="search" placeholder="Албан тушаал, компани, түлхүүр үг..." class="w-full rounded-lg border-gray-300 sm:flex-1" />
            <select v-model="typeModel" class="rounded-lg border-gray-300 text-sm sm:w-40">
                <option value="">Бүх төрөл</option>
                <option v-for="t in types" :key="t.key" :value="t.key">{{ t.label }}</option>
            </select>
            <select v-model="countryModel" class="rounded-lg border-gray-300 text-sm sm:w-36">
                <option value="">Бүх улс</option>
                <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
            </select>
        </div>

        <!-- Ангилал -->
        <div class="mb-6 flex flex-wrap gap-2">
            <button class="rounded-full px-3 py-1 text-sm transition" :class="!filters.category ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50'" @click="filterCategory(null)">Бүгд</button>
            <button
                v-for="c in categories"
                :key="c.key"
                class="rounded-full px-3 py-1 text-sm transition"
                :class="filters.category === c.key ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50'"
                @click="filterCategory(c.key)"
            >{{ c.label }} <span class="text-xs opacity-70">{{ c.count }}</span></button>
        </div>

        <div v-if="jobs.data.length" class="space-y-3">
            <Link
                v-for="j in jobs.data"
                :key="j.id"
                :href="`/jobs/${j.slug}`"
                class="group flex items-start justify-between gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-soft transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-medium text-brand-700">{{ j.type_label }}</span>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-500">{{ j.category_label }}</span>
                    </div>
                    <h2 class="mt-1.5 font-semibold text-gray-900 group-hover:text-brand-700">{{ j.title }}</h2>
                    <p class="text-sm text-gray-500">
                        <span v-if="j.company">{{ j.company }}</span>
                        <span v-if="j.company && (j.city || j.country)"> · </span>
                        <span v-if="j.city || j.country">{{ j.city }}<span v-if="j.country">, {{ j.country }}</span></span>
                    </p>
                </div>
                <div class="shrink-0 text-right">
                    <p v-if="j.salary" class="font-semibold text-gray-900">{{ j.salary }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ timeAgo(j.created_at) }}</p>
                </div>
            </Link>
        </div>

        <div v-else class="rounded-3xl border border-dashed border-gray-200 bg-white py-16 text-center">
            <p class="font-medium text-gray-700">Ажлын зар олдсонгүй</p>
            <Link :href="user ? '/jobs/new' : '/login'" class="mt-2 inline-block font-medium text-brand-700 hover:underline">Анхны зараа нэмэх →</Link>
        </div>

        <div v-if="jobs.links && jobs.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-1">
            <Link
                v-for="link in jobs.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </PublicLayout>
</template>
