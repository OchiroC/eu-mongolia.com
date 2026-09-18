<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Plus } from 'lucide-vue-next';
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
        <PageHeader kicker="Ажил" title="Ажлын байр" subtitle="Европ дахь монголчуудад зориулсан ажлын зар.">
            <template #actions>
                <Link :href="user ? '/jobs/new' : '/login'" class="inline-flex h-10 items-center gap-1.5 rounded-md bg-brand-600 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-500">
                    <Plus class="h-4 w-4" /> Ажлын зар нэмэх
                </Link>
            </template>
        </PageHeader>

        <!-- Хайлт + шүүлт -->
        <div class="mb-4 flex flex-col gap-2 sm:flex-row">
            <input v-model="search" type="search" placeholder="Албан тушаал, компани, түлхүүр үг..." class="w-full rounded-md border-brand-200 sm:flex-1 focus:border-brand-600 focus:ring-1 focus:ring-brand-600" />
            <select v-model="typeModel" class="rounded-md border-brand-200 text-sm sm:w-40 focus:border-brand-600 focus:ring-1 focus:ring-brand-600">
                <option value="">Бүх төрөл</option>
                <option v-for="t in types" :key="t.key" :value="t.key">{{ t.label }}</option>
            </select>
            <select v-model="countryModel" class="rounded-md border-brand-200 text-sm sm:w-36 focus:border-brand-600 focus:ring-1 focus:ring-brand-600">
                <option value="">Бүх улс</option>
                <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
            </select>
        </div>

        <!-- Ангилал -->
        <div class="mb-6 flex flex-wrap gap-2">
            <button class="rounded-md px-3 py-1 text-sm transition" :class="!filters.category ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'" @click="filterCategory(null)">Бүгд</button>
            <button
                v-for="c in categories"
                :key="c.key"
                class="rounded-md px-3 py-1 text-sm transition"
                :class="filters.category === c.key ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterCategory(c.key)"
            >{{ c.label }} <span class="text-xs opacity-70">{{ c.count }}</span></button>
        </div>

        <div v-if="jobs.data.length" class="space-y-3">
            <Link
                v-for="j in jobs.data"
                :key="j.id"
                :href="`/jobs/${j.slug}`"
                class="group flex items-start justify-between gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-card transition duration-300 hover:shadow-card-lg"
            >
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rounded-md bg-brand-50 px-2 py-0.5 text-[11px] font-medium text-brand-700">{{ j.type_label }}</span>
                        <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] text-gray-500">{{ j.category_label }}</span>
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
                :class="[link.active ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </PublicLayout>
</template>
