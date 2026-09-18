<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowUpRight, Search } from 'lucide-vue-next';
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
function clearTopic() {
    go({ topic: undefined });
}
function filterCountry(c) {
    go({ country: c || undefined });
}

// Хоосон сэдвийг нууж шүүлтүүрийг богиносгоно (идэвхтэй бол үлдээнэ).
const visibleTopics = computed(() => props.topics.filter((t) => t.count > 0 || t.key === props.filters.topic));
const countryModel = computed({ get: () => props.filters.country ?? '', set: (v) => filterCountry(v) });
const offset = computed(() => (props.guides.from ?? 1) - 1);
</script>

<template>
    <Head title="Гарын авлага" />

    <PublicLayout>
        <PageHeader
            kicker="Гарын авлага"
            title="Ирсний дараах алхмууд"
            subtitle="Виз, хотын бүртгэл, даатгал, татвар, жолооны үнэмлэхтэй холбоотой ажлыг алхам алхмаар тайлбарласан заавар."
        />

        <!-- Хайлт + улс -->
        <div class="flex flex-col gap-3 sm:flex-row">
            <label class="relative flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-brand-400" />
                <input v-model="search" type="search" placeholder="Гарын авлага хайх…" class="h-11 w-full rounded-md border-brand-200 pl-9 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600" />
            </label>
            <select v-model="countryModel" class="h-11 rounded-md border-brand-200 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600 sm:w-48">
                <option value="">Бүх улс</option>
                <option v-for="c in countries" :key="c" :value="c">{{ c }}</option>
            </select>
        </div>

        <!-- Сэдэв -->
        <div class="mt-4 flex flex-wrap gap-2">
            <button
                type="button"
                class="h-8 rounded-md border px-3 text-sm transition-colors"
                :class="!filters.topic ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="clearTopic"
            >Бүгд</button>
            <button
                v-for="t in visibleTopics"
                :key="t.key"
                type="button"
                class="inline-flex h-8 items-center gap-2 rounded-md border px-3 text-sm transition-colors"
                :class="filters.topic === t.key ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterTopic(t.key)"
            >
                {{ t.label }}
                <span class="tabular font-mono text-xs opacity-60">{{ t.count }}</span>
            </button>
        </div>

        <!-- Жагсаалт — нүүр хуудсын "эхний алхмууд"-тай ижил хэлбэр. -->
        <ol v-if="guides.data.length" class="mt-10 grid gap-x-10 md:grid-cols-2">
            <li v-for="(g, i) in guides.data" :key="g.id" class="border-t border-brand-100">
                <Link :href="`/guides/${g.slug}`" class="group flex gap-5 py-6">
                    <span class="tabular font-mono text-sm text-brand-300">{{ String(offset + i + 1).padStart(2, '0') }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="kicker block">{{ g.topic_label }}<template v-if="g.country"> · {{ g.country }}</template></span>
                        <span class="mt-2 flex items-start justify-between gap-3">
                            <span class="text-[17px] font-medium leading-snug text-brand-600 group-hover:underline group-hover:underline-offset-4">{{ g.title }}</span>
                            <ArrowUpRight class="mt-1 h-4 w-4 shrink-0 text-brand-300 transition-colors group-hover:text-brand-600" />
                        </span>
                        <span v-if="g.excerpt" class="mt-2 line-clamp-2 block text-sm leading-relaxed text-brand-400">{{ g.excerpt }}</span>
                    </span>
                </Link>
            </li>
        </ol>

        <div v-else class="mt-10 border border-brand-100 px-6 py-16 text-center">
            <p class="font-medium text-brand-600">Гарын авлага олдсонгүй</p>
            <p class="mt-1 text-sm text-brand-400">Сэдэв эсвэл улсаа өөрчилж үзнэ үү.</p>
        </div>

        <div v-if="guides.links && guides.links.length > 3" class="mt-10 flex flex-wrap justify-center gap-1">
            <Link
                v-for="link in guides.links"
                :key="link.label"
                :href="link.url || ''"
                class="tabular flex h-9 min-w-9 items-center justify-center rounded-md border px-3 font-mono text-sm"
                :class="[link.active ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200 text-brand-500 hover:border-brand-600', !link.url ? 'pointer-events-none opacity-40' : '']"
                v-html="link.label"
            />
        </div>
    </PublicLayout>
</template>
