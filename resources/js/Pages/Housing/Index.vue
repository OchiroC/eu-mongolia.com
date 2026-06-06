<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { timeAgo } from '@/lib/date';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    posts: Object,
    types: { type: Array, default: () => [] },
    filters: Object,
});

const user = computed(() => usePage().props.auth?.user);
const city = ref(props.filters.city ?? '');
const maxPrice = ref(props.filters.max_price ?? '');

let timer = null;
watch([city, maxPrice], () => {
    clearTimeout(timer);
    timer = setTimeout(() => go({ city: city.value || undefined, max_price: maxPrice.value || undefined }), 350);
});

function go(params) {
    router.get('/housing', { ...props.filters, ...params }, { preserveState: true, replace: true, preserveScroll: true });
}
function filterType(key) {
    go({ type: props.filters.type === key ? undefined : key });
}
function price(p) {
    return p.price ? Number(p.price).toLocaleString('mn-MN') + '€/сар' : 'Тохиролцоно';
}
</script>

<template>
    <Head title="Орон сууц / Өрөө хуваалцах" />

    <PublicLayout>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Орон сууц / Өрөө хуваалцах</h1>
                <p class="mt-1 text-sm text-gray-500">Европ дахь монголчуудад зориулсан түрээс, өрөө, WG зар.</p>
            </div>
            <Link :href="user ? '/housing/new' : '/login'" class="inline-flex w-fit items-center gap-1.5 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-brand-700 hover:shadow-brand-glow active:translate-y-0">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Зар нэмэх
            </Link>
        </div>

        <div class="mb-4 flex flex-col gap-2 sm:flex-row">
            <input v-model="city" type="text" placeholder="Хот" class="w-full rounded-lg border-gray-300 text-sm sm:flex-1" />
            <input v-model.number="maxPrice" type="number" min="0" placeholder="Дээд үнэ (€/сар)" class="w-full rounded-lg border-gray-300 text-sm sm:w-48" />
        </div>

        <div class="mb-6 flex flex-wrap gap-2">
            <button class="rounded-full px-3 py-1 text-sm transition" :class="!filters.type ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50'" @click="filterType(null)">Бүгд</button>
            <button
                v-for="t in types"
                :key="t.key"
                class="rounded-full px-3 py-1 text-sm transition"
                :class="filters.type === t.key ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50'"
                @click="filterType(t.key)"
            >{{ t.label }} <span class="text-xs opacity-70">{{ t.count }}</span></button>
        </div>

        <div v-if="posts.data.length" class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="p in posts.data"
                :key="p.id"
                :href="`/housing/${p.slug}`"
                class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-card-lg"
            >
                <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                    <img v-if="p.cover" :src="p.cover" :alt="p.title" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                    <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                        <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    </div>
                    <span class="absolute left-2 top-2 rounded-md bg-white/90 px-2 py-0.5 text-[11px] font-medium text-gray-700 backdrop-blur">{{ p.type_label }}</span>
                </div>
                <div class="flex flex-1 flex-col p-3.5">
                    <p class="text-base font-bold text-gray-900">{{ price(p) }}</p>
                    <h3 class="mt-0.5 line-clamp-1 text-sm font-medium text-gray-800 group-hover:text-brand-700">{{ p.title }}</h3>
                    <p class="mt-1 text-xs text-gray-400">
                        <span v-if="p.rooms">{{ p.rooms }} өрөө · </span><span v-if="p.size">{{ p.size }}м² · </span>{{ p.district || p.city }}
                    </p>
                    <div class="mt-auto flex items-center justify-between pt-2 text-xs text-gray-400">
                        <span>{{ p.city }}</span>
                        <span>{{ timeAgo(p.created_at) }}</span>
                    </div>
                </div>
            </Link>
        </div>

        <div v-else class="rounded-3xl border border-dashed border-gray-200 bg-white py-16 text-center">
            <p class="font-medium text-gray-700">Зар олдсонгүй</p>
            <Link :href="user ? '/housing/new' : '/login'" class="mt-2 inline-block font-medium text-brand-700 hover:underline">Анхны зараа нэмэх →</Link>
        </div>

        <div v-if="posts.links && posts.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-1">
            <Link
                v-for="link in posts.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </PublicLayout>
</template>
