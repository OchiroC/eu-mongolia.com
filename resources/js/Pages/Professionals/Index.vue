<script setup>
import BannerDisplay from '@/components/BannerDisplay.vue';
import ProfessionalCard from '@/components/ProfessionalCard.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
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
</script>

<template>
    <Head title="Мэргэжилтэн" />

    <PublicLayout>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Мэргэжилтний лавлах</h1>
                <p class="mt-1 text-sm text-gray-500">Европ дахь монгол хуульч, эмч, орчуулагч болон бусад мэргэжилтнүүд.</p>
            </div>
            <Link :href="user ? '/my/professional' : '/login'" class="inline-flex w-fit items-center gap-1.5 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Мэргэжилтнээр бүртгүүлэх
            </Link>
        </div>

        <!-- Хайлт -->
        <div class="mb-4 flex flex-col gap-2 sm:flex-row">
            <input v-model="search" type="search" placeholder="Нэр, мэргэжил, үйлчилгээ..." class="w-full rounded-lg border-gray-300 sm:flex-1" />
            <input v-model="city" type="text" placeholder="Хот" class="w-full rounded-lg border-gray-300 sm:w-48" />
        </div>

        <!-- Ангилал -->
        <div class="mb-3 flex flex-wrap gap-2">
            <button class="rounded-full px-3 py-1 text-sm transition" :class="!filters.category ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50'" @click="filterCategory(null)">Бүгд</button>
            <button
                v-for="cat in categories"
                :key="cat.id"
                class="rounded-full px-3 py-1 text-sm transition"
                :class="filters.category === cat.slug ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50'"
                @click="filterCategory(cat.slug)"
            >{{ cat.name }} <span class="text-xs opacity-70">{{ cat.professionals_count }}</span></button>
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

        <BannerDisplay placement="news_top" :placeholder="true" class="mb-6" />

        <div v-if="professionals.data.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            <ProfessionalCard v-for="p in professionals.data" :key="p.id" :pro="p" />
        </div>

        <p v-else class="rounded-2xl bg-white py-16 text-center text-gray-500 ring-1 ring-gray-100">
            Мэргэжилтэн олдсонгүй.
        </p>

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
