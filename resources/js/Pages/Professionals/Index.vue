<script setup>
import BannerDisplay from '@/Components/BannerDisplay.vue';
import ProfessionalCard from '@/Components/ProfessionalCard.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Plus, Search } from 'lucide-vue-next';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    professionals: Object,
    featured: { type: Array, default: () => [] },
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

// Ангиллын icon-ууд
function initial(name) {
    return (name || '?').charAt(0).toUpperCase();
}
const hasFilters = computed(() => !!(props.filters.category || props.filters.city || props.filters.lang || props.filters.search));
</script>

<template>
    <Head title="Мэргэжлийн үйлчилгээ" />

    <PublicLayout>
        <PageHeader
            kicker="Мэргэжлийн туслах"
            title="Монголоор ярьдаг мэргэжилтэн"
            subtitle="Хуульч, эмч, орчуулагч, нягтлан бодогч болон бусад мэргэжилтэнтэй шууд холбогдоно."
        >
            <template #actions>
                <Link :href="user ? '/my/professional' : '/login'" class="inline-flex h-10 items-center gap-1.5 rounded-md bg-brand-600 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-500">
                    <Plus class="h-4 w-4" /> Үйлчилгээгээ нэмэх
                </Link>
            </template>
        </PageHeader>

        <!-- Хайлт -->
        <div class="flex flex-col gap-3 sm:flex-row">
            <label class="relative flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-400" />
                <input v-model="search" type="search" placeholder="Нэр, мэргэжил, үйлчилгээ хайх…" class="h-11 w-full rounded-md border-brand-200 pl-9 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600" />
            </label>
            <input v-model="city" type="text" placeholder="Хот" class="h-11 rounded-md border-brand-200 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600 sm:w-48" />
        </div>

        <!-- Ангилал — нэг мөр шүүлтүүр, хоосныг нууна. -->
        <div class="mb-4 mt-4 flex flex-wrap gap-2">
            <button
                type="button"
                class="h-8 rounded-md border px-3 text-sm transition-colors"
                :class="!filters.category ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterCategory(null)"
            >Бүгд</button>
            <button
                v-for="cat in categories.filter((c) => c.professionals_count > 0 || filters.category === c.slug)"
                :key="cat.id"
                type="button"
                class="inline-flex h-8 items-center gap-2 rounded-md border px-3 text-sm transition-colors"
                :class="filters.category === cat.slug ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterCategory(cat.slug)"
            >
                {{ cat.name }}
                <span class="tabular font-mono text-xs opacity-60">{{ cat.professionals_count }}</span>
            </button>
        </div>

        <!-- Хэл -->
        <div class="mb-6 flex flex-wrap items-center gap-2">
            <span class="text-sm text-gray-400">Хэл:</span>
            <button
                v-for="lang in languages"
                :key="lang"
                class="rounded-md px-2.5 py-1 text-xs transition"
                :class="filters.lang === lang ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterLang(lang)"
            >{{ lang }}</button>
        </div>

        <!-- Онцлох (төлбөртэй) — том, зурагтай -->
        <div v-if="featured.length" class="mb-8">
            <div class="mb-3 flex items-center gap-1.5">
                <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.9 6.26L22 9.27l-5 4.87L18.18 22 12 18.27 5.82 22 7 14.14l-5-4.87 7.1-1.01L12 2z" /></svg>
                <h2 class="text-lg font-bold text-gray-900">Онцлох мэргэжилтэн</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    v-for="p in featured"
                    :key="p.id"
                    :href="`/professionals/${p.slug}`"
                    class="group overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-soft ring-1 ring-amber-100 transition hover:shadow-md"
                >
                    <div class="relative aspect-[4/3] overflow-hidden bg-brand-600">
                        <img v-if="p.photo" :src="p.photo" :alt="p.name" class="h-full w-full object-cover transition" />
                        <div v-else class="flex h-full w-full items-center justify-center text-5xl font-semibold text-white/90">{{ initial(p.name) }}</div>
                        <span class="absolute left-2.5 top-2.5 rounded-md bg-amber-400 px-2 py-0.5 text-[10px] font-bold text-amber-900">Онцлох</span>
                    </div>
                    <div class="p-3.5">
                        <div class="flex items-center gap-1">
                            <h3 class="truncate font-semibold text-gray-900 group-hover:text-brand-700">{{ p.name }}</h3>
                            <svg v-if="p.is_verified" class="h-4 w-4 shrink-0 text-brand-600" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2l2.39 1.74 2.95-.02 1.06 2.76 2.43 1.7-.92 2.81.92 2.81-2.43 1.7-1.06 2.76-2.95-.02L12 22l-2.39-1.76-2.95.02-1.06-2.76-2.43-1.7.92-2.81-.92-2.81 2.43-1.7L6.66 3.7l2.95.02L12 2zm-1.1 13.2l5.2-5.2-1.4-1.4-3.8 3.8-1.8-1.8-1.4 1.4 3.2 3.2z" clip-rule="evenodd" /></svg>
                        </div>
                        <p v-if="p.profession" class="truncate text-sm text-brand-700">{{ p.profession }}</p>
                        <p class="mt-0.5 truncate text-xs text-gray-400">{{ p.category }}<span v-if="p.city"> · {{ p.city }}</span></p>
                    </div>
                </Link>
            </div>
        </div>

        <h2 v-if="featured.length && professionals.data.length" class="mb-3 text-lg font-bold text-gray-900">Бүх мэргэжилтэн</h2>

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
                :class="[link.active ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </PublicLayout>
</template>
