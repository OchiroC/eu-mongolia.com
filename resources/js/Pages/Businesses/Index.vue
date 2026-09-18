<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Plus, Search } from 'lucide-vue-next';
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

function initial(name) { return (name || '?').charAt(0).toUpperCase(); }
</script>

<template>
    <Head title="Монгол бизнес лавлах" />

    <PublicLayout>
        <PageHeader
            kicker="Бизнес лавлах"
            title="Монгол бизнесүүд"
            subtitle="Монгол ресторан, дэлгүүр, үйлчилгээний газруудын лавлах."
        >
            <template #actions>
                <Link :href="user ? '/my/businesses' : '/login'" class="inline-flex h-10 items-center gap-1.5 rounded-md bg-brand-600 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-500">
                    <Plus class="h-4 w-4" /> Бизнесээ нэмэх
                </Link>
            </template>
        </PageHeader>

        <div class="flex flex-col gap-3 sm:flex-row">
            <label class="relative flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-400" />
                <input v-model="search" type="search" placeholder="Бизнес хайх…" class="h-11 w-full rounded-md border-brand-200 pl-9 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600" />
            </label>
            <select v-model="cityModel" class="h-11 rounded-md border-brand-200 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600 sm:w-48">
                <option value="">Бүх хот</option>
                <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
            </select>
        </div>

        <div class="mb-8 mt-4 flex flex-wrap gap-2">
            <button
                type="button"
                class="h-8 rounded-md border px-3 text-sm transition-colors"
                :class="!filters.category ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterCategory(null)"
            >Бүгд</button>
            <button
                v-for="c in categories.filter((x) => x.count > 0 || filters.category === x.key)"
                :key="c.key"
                type="button"
                class="inline-flex h-8 items-center gap-2 rounded-md border px-3 text-sm transition-colors"
                :class="filters.category === c.key ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterCategory(c.key)"
            >
                {{ c.label }}
                <span class="tabular font-mono text-xs opacity-60">{{ c.count }}</span>
            </button>
        </div>

        <!-- Онцлох -->
        <div v-if="featured.length" class="mb-8">
            <div class="mb-3 flex items-center gap-1.5">
                <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.9 6.26L22 9.27l-5 4.87L18.18 22 12 18.27 5.82 22 7 14.14l-5-4.87 7.1-1.01L12 2z" /></svg>
                <h2 class="text-lg font-bold text-gray-900">Онцлох бизнес</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Link v-for="b in featured" :key="b.id" :href="`/businesses/${b.slug}`" class="group overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-card ring-1 ring-amber-100 transition duration-300 hover:shadow-card-lg">
                    <div class="relative aspect-[4/3] overflow-hidden bg-brand-600">
                        <img v-if="b.photo" :src="b.photo" :alt="b.name" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center text-5xl font-semibold text-white/90">{{ initial(b.name) }}</div>
                        <span class="absolute left-2.5 top-2.5 rounded-md bg-amber-400 px-2 py-0.5 text-[10px] font-bold text-amber-900">Онцлох</span>
                    </div>
                    <div class="p-3.5">
                        <h3 class="truncate font-semibold text-gray-900 group-hover:text-brand-700">{{ b.name }}</h3>
                        <p class="mt-0.5 truncate text-xs text-gray-400">{{ b.category_label }} · {{ b.city }}</p>
                    </div>
                </Link>
            </div>
        </div>

        <div v-if="businesses.data.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link v-for="b in businesses.data" :key="b.id" :href="`/businesses/${b.slug}`" class="group flex items-center gap-3 rounded-2xl border border-gray-100 bg-white p-3 shadow-card transition duration-300 hover:shadow-card-lg">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-100 text-xl font-semibold text-brand-700">
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
            <Link v-for="link in businesses.links" :key="link.label" :href="link.url || ''" v-html="link.label" class="rounded-md px-3 py-1 text-sm" :class="[link.active ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600', !link.url ? 'pointer-events-none opacity-50' : '']" />
        </div>
    </PublicLayout>
</template>
