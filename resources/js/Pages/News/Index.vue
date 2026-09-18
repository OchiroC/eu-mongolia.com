<script setup>
import { X } from 'lucide-vue-next';
import BannerDisplay from '@/Components/BannerDisplay.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { timeAgo } from '@/lib/date';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    posts: Object,
    categories: Array,
    popularTags: { type: Array, default: () => [] },
    activeTag: { type: String, default: null },
    filters: Object,
});

const search = ref(props.filters.search ?? '');

let timer = null;
watch(search, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => go({ search: value || undefined }), 350);
});

function go(params) {
    router.get('/news', { ...props.filters, ...params }, { preserveState: true, replace: true, preserveScroll: true });
}

// Категори: дээд түвшин + дэд
const topLevel = computed(() => props.categories.filter((c) => !c.parent_id));
function childrenOf(id) {
    return props.categories.filter((c) => c.parent_id === id);
}
const activeCat = computed(() => props.categories.find((c) => c.slug === props.filters.category));
const activeParentId = computed(() => {
    const c = activeCat.value;
    return c ? (c.parent_id ?? c.id) : null;
});
const activeParentCat = computed(() => props.categories.find((c) => c.id === activeParentId.value) ?? null);
const subCategories = computed(() => (activeParentId.value ? childrenOf(activeParentId.value) : []));

function filterCategory(slug) {
    go({ category: slug || undefined });
}
function filterTag(slug) {
    go({ tag: slug || undefined });
}

</script>

<template>
    <Head title="Мэдээ" />

    <PublicLayout>
        <BannerDisplay placement="news_top" :placeholder="true" class="mb-6" />

        <PageHeader kicker="Мэдээ" title="Мэдээ мэдээлэл" subtitle="Европ дахь монголчуудын мэдээ, мэдээлэл.">
            <template #actions>
                <input v-model="search" type="search" placeholder="Хайх…" class="h-10 w-full rounded-md border-brand-200 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600 sm:w-64" />
            </template>
        </PageHeader>

        <!-- Категори (дээд түвшин) -->
        <div class="mb-3 flex flex-wrap gap-2">
            <button
                class="rounded-md px-3 py-1 text-sm transition"
                :class="!filters.category ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterCategory(null)"
            >
                Бүгд
            </button>
            <button
                v-for="cat in topLevel"
                :key="cat.id"
                class="rounded-md px-3 py-1 text-sm transition"
                :class="filters.category === cat.slug || activeParentId === cat.id ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterCategory(cat.slug)"
            >
                {{ cat.name }}
            </button>
        </div>

        <!-- Дэд категори (идэвхтэй ангиллынх) -->
        <div v-if="subCategories.length" class="mb-4 flex flex-wrap items-center gap-2 rounded-xl bg-gray-50 px-3 py-2.5">
            <span class="text-xs font-medium text-gray-400">Дэд ангилал:</span>
            <button
                class="rounded-md px-3 py-1 text-xs transition"
                :class="filters.category === activeParentCat?.slug ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterCategory(activeParentCat?.slug)"
            >
                Бүгд
            </button>
            <button
                v-for="sub in subCategories"
                :key="sub.id"
                class="rounded-md px-3 py-1 text-xs transition"
                :class="filters.category === sub.slug ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterCategory(sub.slug)"
            >
                {{ sub.name }}
            </button>
        </div>

        <!-- Таг шүүлт -->
        <div v-if="filters.tag || popularTags.length" class="mb-6 flex flex-wrap items-center gap-2">
            <span v-if="filters.tag" class="inline-flex items-center gap-1.5 rounded-md bg-brand-600 px-3 py-1 text-sm font-medium text-white">
                #{{ activeTag || filters.tag }}
                <button type="button" class="text-brand-200 hover:text-white" @click="filterTag(null)"><X class="h-3.5 w-3.5" /></button>
            </span>
            <template v-if="popularTags.length">
                <span class="text-sm text-gray-400">Түгээмэл таг:</span>
                <template v-for="t in popularTags" :key="t.id">
                    <button
                        v-if="t.slug !== filters.tag"
                        type="button"
                        class="rounded-md bg-gray-100 px-2.5 py-1 text-xs text-gray-600 transition hover:bg-brand-50 hover:text-brand-700"
                        @click="filterTag(t.slug)"
                    >#{{ t.name }}</button>
                </template>
            </template>
        </div>

        <div v-if="posts.data.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="post in posts.data"
                :key="post.id"
                :href="`/news/${post.slug}`"
                class="group overflow-hidden rounded-lg bg-white shadow-card ring-1 ring-gray-100 transition duration-300 hover:shadow-card-lg"
            >
                <div class="aspect-video overflow-hidden bg-gray-100">
                    <img v-if="post.cover_image" :src="post.cover_image" :alt="post.title" class="h-full w-full object-cover" />
                </div>
                <div class="p-4">
                    <span v-if="post.category" class="text-xs font-medium text-brand-700">
                        {{ post.category.name }}
                    </span>
                    <h2 class="mt-1 font-semibold group-hover:text-brand-700">{{ post.title }}</h2>
                    <p v-if="post.excerpt" class="mt-2 line-clamp-2 text-sm text-gray-600">{{ post.excerpt }}</p>
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-400">
                        <span>{{ post.author?.name }}</span>
                        <span>{{ timeAgo(post.published_at) }}</span>
                    </div>
                </div>
            </Link>
        </div>

        <p v-else class="rounded-lg bg-white py-12 text-center text-gray-500 ring-1 ring-gray-100">
            Мэдээ олдсонгүй.
        </p>

        <div v-if="posts.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-1">
            <Link
                v-for="link in posts.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[
                    link.active ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600',
                    !link.url ? 'pointer-events-none opacity-50' : '',
                ]"
            />
        </div>
    </PublicLayout>
</template>
