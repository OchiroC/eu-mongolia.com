<script setup>
import BannerDisplay from '@/Components/BannerDisplay.vue';
import ProfessionalCard from '@/Components/ProfessionalCard.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    professionals: Object,
    categories: { type: Array, default: () => [] },
    languages: { type: Array, default: () => [] },
    filters: Object,
});

const user = computed(() => usePage().props.auth?.user);
const search = ref(props.filters.search ?? '');
const city = ref(props.filters.city ?? '');

let timer = null;
watch([search, city], () => {
    clearTimeout(timer);
    timer = setTimeout(() => go({ search: search.value || undefined, city: city.value || undefined }), 350);
});

function go(params) {
    router.get('/professionals', { ...props.filters, ...params }, { preserveState: true, replace: true, preserveScroll: true });
}
function filterCategory(slug) {
    go({ category: slug || undefined });
}
function filterLang(lang) {
    go({ lang: props.filters.lang === lang ? undefined : lang });
}

// Ангиллын icon (зарын catIcon-той ижил багц)
const catIcon = {
    briefcase: 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
    wrench: 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63',
    tag: 'M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z',
    shirt: 'M16 4l4 4-3 2v10H7V10L4 8l4-4 4 2 4-2z',
    device: 'M9 17H7A5 5 0 017 7h2m6 0h2a5 5 0 010 10h-2m-8-5h12',
};
function iconFor(c) {
    return catIcon[c.icon] || catIcon.tag;
}
const hasFilters = computed(() => !!(props.filters.category || props.filters.city || props.filters.lang || props.filters.search));
</script>

<template>
    <Head title="Мэргэжлийн үйлчилгээ" />

    <PublicLayout>
        <!-- Hero толгой -->
        <div class="relative mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 px-6 py-8 sm:px-10 sm:py-10">
            <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-xl">
                    <h1 class="text-2xl font-bold text-white sm:text-3xl">Мэргэжлийн үйлчилгээ</h1>
                    <p class="mt-2 text-sm text-brand-100 sm:text-base">Европ дахь монгол хуульч, эмч, орчуулагч, үсчин болон бусад мэргэжилтнүүд — баталгаажсан, шууд холбогдоно.</p>
                </div>
                <Link :href="user ? '/my/professional' : '/login'" class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-gray-900 transition hover:bg-brand-50">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Үйлчилгээгээ нэмэх
                </Link>
            </div>

            <!-- Хайлт -->
            <div class="relative mt-6 flex flex-col gap-2 rounded-2xl bg-white p-1.5 shadow-lg sm:flex-row">
                <input v-model="search" type="search" placeholder="Нэр, мэргэжил, үйлчилгээ хайх..." class="min-w-0 flex-1 rounded-xl border-0 px-3 py-2.5 text-gray-900 placeholder-gray-400 focus:ring-0" />
                <input v-model="city" type="text" placeholder="Хот" class="w-full rounded-xl border-0 px-3 py-2.5 text-gray-900 placeholder-gray-400 focus:ring-0 sm:w-40 sm:border-l sm:border-gray-100" />
            </div>
        </div>

        <!-- Ангиллын tile-ууд -->
        <div class="mb-6 grid grid-cols-3 gap-2.5 sm:grid-cols-5 lg:grid-cols-9">
            <button
                class="flex flex-col items-center gap-1.5 rounded-2xl border p-3 text-center transition"
                :class="!filters.category ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-100 bg-white text-gray-600 hover:border-brand-200 hover:bg-brand-50/40'"
                @click="filterCategory(null)"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                <span class="text-[11px] font-medium leading-tight">Бүгд</span>
            </button>
            <button
                v-for="cat in categories"
                :key="cat.id"
                class="flex flex-col items-center gap-1.5 rounded-2xl border p-3 text-center transition"
                :class="filters.category === cat.slug ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-gray-100 bg-white text-gray-600 hover:border-brand-200 hover:bg-brand-50/40'"
                @click="filterCategory(cat.slug)"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" :d="iconFor(cat)" /></svg>
                <span class="line-clamp-2 text-[11px] font-medium leading-tight">{{ cat.name }}</span>
                <span class="text-[10px] text-gray-400">{{ cat.professionals_count }}</span>
            </button>
        </div>

        <!-- Хэл -->
        <div class="mb-6 flex flex-wrap items-center gap-2">
            <span class="text-sm text-gray-400">Хэл:</span>
            <button
                v-for="lang in languages"
                :key="lang"
                class="rounded-full px-2.5 py-1 text-xs transition"
                :class="filters.lang === lang ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-brand-50 hover:text-brand-700'"
                @click="filterLang(lang)"
            >{{ lang }}</button>
        </div>

        <div v-if="professionals.data.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            <ProfessionalCard v-for="p in professionals.data" :key="p.id" :pro="p" />
        </div>

        <!-- Хоосон төлөв -->
        <div v-else class="rounded-3xl border border-dashed border-gray-200 bg-white py-16 text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-50 text-brand-500">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z" /></svg>
            </span>
            <p class="mt-3 font-medium text-gray-700">{{ hasFilters ? 'Тохирох үйлчилгээ олдсонгүй' : 'Одоогоор бүртгэгдсэн үйлчилгээ алга' }}</p>
            <p class="text-sm text-gray-400">{{ hasFilters ? 'Шүүлтүүрээ өөрчилж үзнэ үү.' : 'Анхных болж өөрийн үйлчилгээгээ нэмээрэй.' }}</p>
            <Link :href="user ? '/my/professional' : '/login'" class="mt-4 inline-block font-medium text-brand-700 hover:underline">Үйлчилгээгээ нэмэх →</Link>
        </div>

        <div v-if="professionals.links && professionals.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-1">
            <Link
                v-for="link in professionals.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </PublicLayout>
</template>
