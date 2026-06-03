<script setup>
import ListingCard from '@/components/ListingCard.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';

const props = defineProps({
    listings: Object,
    categories: Array,
    filters: Object,
});

const search = ref(props.filters.search ?? '');
const location = ref(props.filters.location ?? '');
const priceRange = reactive({
    min_price: props.filters.min_price ?? '',
    max_price: props.filters.max_price ?? '',
});

function apply(extra = {}) {
    router.get('/zar', {
        ...props.filters,
        search: search.value || undefined,
        location: location.value || undefined,
        min_price: priceRange.min_price || undefined,
        max_price: priceRange.max_price || undefined,
        ...extra,
    }, { preserveState: true, replace: true, preserveScroll: true });
}

function selectCategory(slug) {
    apply({ category: slug || undefined });
}

let t = null;
watch([search, location], () => {
    clearTimeout(t);
    t = setTimeout(() => apply(), 400);
});

const catIcon = {
    car: 'M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13m-14 0h14m-14 0v4a1 1 0 001 1h1a1 1 0 001-1v-1h8v1a1 1 0 001 1h1a1 1 0 001-1v-4M7 16h.01M17 16h.01',
    home: 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10',
    briefcase: 'M21 13.255A23.9 23.9 0 0112 15c-3.18 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    device: 'M9 17.25v1.007a3 3 0 01-.879 2.122L7 21.5h10l-1.121-1.121A3 3 0 0115 18.257V17.25m6-12V15a2 2 0 01-2 2H5a2 2 0 01-2-2V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2 2 0 01-2 2H5a2 2 0 01-2-2V5.25',
    sofa: 'M3 10V7a2 2 0 012-2h14a2 2 0 012 2v3M3 10a2 2 0 012 2v3h14v-3a2 2 0 012-2M3 10h18M5 18v2m14-2v2',
    shirt: 'M16 4l4 4-3 2v10H7V10L4 8l4-4 4 2 4-2z',
    wrench: 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63',
    tag: 'M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z',
};
</script>

<template>
    <Head title="Зар" />

    <PublicLayout>
        <!-- Хайлтын мөр -->
        <div class="mb-6 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-gray-100">
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 z-10 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <Input v-model="search" type="search" placeholder="Юу хайж байна?" class="pl-10" />
                </div>
                <div class="relative sm:w-56">
                    <svg class="pointer-events-none absolute left-3 top-1/2 z-10 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <Input v-model="location" type="text" placeholder="Хот эсвэл PLZ" class="pl-10" />
                </div>
                <Button :as="Link" href="/zar/new" class="shrink-0">+ Зар нэмэх</Button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[240px_1fr]">
            <!-- Зүүн талын ангилал -->
            <aside class="space-y-4">
                <div class="rounded-2xl bg-white p-3 shadow-soft ring-1 ring-gray-100">
                    <h2 class="px-2 py-1.5 text-sm font-bold text-gray-900">Ангилал</h2>
                    <button
                        class="flex w-full items-center justify-between rounded-lg px-2 py-2 text-sm transition"
                        :class="!filters.category ? 'bg-brand-50 font-semibold text-brand-700' : 'text-gray-600 hover:bg-gray-50'"
                        @click="selectCategory(null)"
                    >
                        <span>Бүгд</span>
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        class="flex w-full items-center gap-2.5 rounded-lg px-2 py-2 text-sm transition"
                        :class="filters.category === cat.slug ? 'bg-brand-50 font-semibold text-brand-700' : 'text-gray-600 hover:bg-gray-50'"
                        @click="selectCategory(cat.slug)"
                    >
                        <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" :d="catIcon[cat.icon] || catIcon.tag" /></svg>
                        <span class="flex-1 text-left">{{ cat.name }}</span>
                        <span class="text-xs text-gray-400">{{ cat.listings_count }}</span>
                    </button>
                </div>

                <!-- Үнийн шүүлт -->
                <div class="rounded-2xl bg-white p-4 shadow-soft ring-1 ring-gray-100">
                    <h2 class="mb-3 text-sm font-bold text-gray-900">Үнэ (€)</h2>
                    <div class="flex items-center gap-2">
                        <Input v-model="priceRange.min_price" type="number" min="0" placeholder="0" class="h-9 text-sm" @change="apply()" />
                        <span class="text-gray-400">—</span>
                        <Input v-model="priceRange.max_price" type="number" min="0" placeholder="∞" class="h-9 text-sm" @change="apply()" />
                    </div>
                    <Button :variant="filters.price_type === 'free' ? 'default' : 'secondary'" size="sm" class="mt-3 w-full" @click="apply({ price_type: filters.price_type === 'free' ? undefined : 'free' })">
                        {{ filters.price_type === 'free' ? '✓ ' : '' }}Зөвхөн үнэгүй
                    </Button>
                </div>
            </aside>

            <!-- Зарын grid -->
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-gray-500">{{ listings.total }} зар олдлоо</p>
                </div>

                <div v-if="listings.data.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-4">
                    <ListingCard v-for="l in listings.data" :key="l.id" :listing="l" />
                </div>

                <div v-else class="rounded-2xl bg-white py-16 text-center shadow-soft ring-1 ring-gray-100">
                    <p class="text-gray-500">Зар олдсонгүй.</p>
                    <Link href="/zar/new" class="mt-3 inline-block font-semibold text-brand-700 hover:underline">Анхны зараа нэмэх →</Link>
                </div>

                <!-- Хуудаслалт -->
                <div v-if="listings.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-1">
                    <Link
                        v-for="link in listings.links"
                        :key="link.label"
                        :href="link.url || ''"
                        v-html="link.label"
                        class="rounded-lg px-3.5 py-2 text-sm"
                        :class="[
                            link.active ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50',
                            !link.url ? 'pointer-events-none opacity-40' : '',
                        ]"
                    />
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
