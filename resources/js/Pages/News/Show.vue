<script setup>
import BannerDisplay from '@/components/BannerDisplay.vue';
import CommentsSection from '@/components/CommentsSection.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { timeAgo } from '@/lib/date';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    post: Object,
    related: Array,
    categories: { type: Array, default: () => [] },
    comments: { type: Array, default: () => [] },
    commentsEnabled: { type: Boolean, default: true },
});

const gallery = computed(() => props.post.gallery ?? []);

// Зургийн цомог: цөөн жижиг thumbnail харуулж, илүүг нь "+N" дээр нуу.
const GALLERY_VISIBLE = 6;
const visibleGallery = computed(() => gallery.value.slice(0, GALLERY_VISIBLE));
const hiddenCount = computed(() => Math.max(0, gallery.value.length - GALLERY_VISIBLE));

// Lightbox (бүх зургийг үзэх)
const lightboxIndex = ref(null);
function openLightbox(i) {
    lightboxIndex.value = i;
}
function closeLightbox() {
    lightboxIndex.value = null;
}
function prevImage() {
    lightboxIndex.value = (lightboxIndex.value - 1 + gallery.value.length) % gallery.value.length;
}
function nextImage() {
    lightboxIndex.value = (lightboxIndex.value + 1) % gallery.value.length;
}
function onKey(e) {
    if (lightboxIndex.value === null) return;
    if (e.key === 'Escape') closeLightbox();
    else if (e.key === 'ArrowLeft') prevImage();
    else if (e.key === 'ArrowRight') nextImage();
}
onMounted(() => document.addEventListener('keydown', onKey));
onUnmounted(() => document.removeEventListener('keydown', onKey));

const user = computed(() => usePage().props.auth?.user);

const quickLinks = [
    { name: 'Нүүр', href: '/', icon: 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10' },
    { name: 'Бүх мэдээ', href: '/news', icon: 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h9l5 5v9a2 2 0 01-2 2zM7 9h6M7 13h10M7 17h10' },
    { name: 'Зар', href: '/zar', icon: 'M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z' },
    { name: 'Эвент', href: '/events', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
];
</script>

<template>
    <Head :title="post.title" />

    <PublicLayout>
        <div>
            <Link href="/news" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">← Мэдээ рүү буцах</Link>

            <div class="mt-4 grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
                <!-- Гол нийтлэл -->
                <article class="min-w-0">
                    <span v-if="post.category" class="block text-sm font-medium text-brand-700">{{ post.category.name }}</span>
                    <h1 class="mt-1 text-3xl font-bold leading-tight text-gray-900">{{ post.title }}</h1>

                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-gray-500">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-700">{{ (post.author?.name || '?').charAt(0).toUpperCase() }}</span>
                            {{ post.author?.name }}
                        </span>
                        <span>•</span>
                        <span>{{ timeAgo(post.published_at) }}</span>
                        <span>•</span>
                        <span>{{ post.views }} үзсэн</span>
                    </div>

                    <img
                        v-if="post.cover_image"
                        :src="post.cover_image"
                        :alt="post.title"
                        class="mt-6 w-full rounded-2xl"
                    />

                    <div class="rich-content mt-6 max-w-none" v-html="post.body"></div>

                    <!-- Зургийн галерей (жижиг thumbnail) -->
                    <div v-if="gallery.length" class="mt-8">
                        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Зургийн цомог</h2>
                        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6">
                            <button
                                v-for="(img, i) in visibleGallery"
                                :key="i"
                                type="button"
                                class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100 ring-1 ring-gray-200"
                                @click="openLightbox(i)"
                            >
                                <img :src="img" alt="" loading="lazy" class="h-full w-full object-cover transition group-hover:scale-105" />
                                <span
                                    v-if="i === visibleGallery.length - 1 && hiddenCount > 0"
                                    class="absolute inset-0 flex items-center justify-center bg-black/60 text-base font-semibold text-white transition group-hover:bg-black/70"
                                >+{{ hiddenCount }}</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="post.tags?.length" class="mt-6 flex flex-wrap gap-2 border-t border-gray-100 pt-5">
                        <Link
                            v-for="t in post.tags"
                            :key="t.id"
                            :href="`/news?tag=${t.slug}`"
                            class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-600 transition hover:bg-brand-50 hover:text-brand-700"
                        >#{{ t.name }}</Link>
                    </div>

                    <!-- Сэтгэгдэл -->
                    <CommentsSection :post-slug="post.slug" :comments="comments" :enabled="commentsEnabled" />
                </article>

                <!-- Хажуугийн sidebar -->
                <aside class="space-y-6 lg:sticky lg:top-20 lg:self-start">
                    <!-- Хэрэгтэй холбоос -->
                    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-soft">
                        <h3 class="border-b border-gray-100 px-4 py-3 text-sm font-semibold text-gray-900">Хэрэгтэй холбоос</h3>
                        <Link
                            v-for="link in quickLinks"
                            :key="link.href"
                            :href="link.href"
                            class="group flex items-center gap-3 border-b border-gray-50 px-4 py-2.5 transition last:border-0 hover:bg-brand-50/40"
                        >
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                                <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" :d="link.icon" /></svg>
                            </span>
                            <span class="text-sm font-medium text-gray-700 group-hover:text-brand-700">{{ link.name }}</span>
                        </Link>
                        <Link :href="user ? '/zar/new' : '/register'" class="flex items-center justify-center gap-1.5 bg-brand-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                            Зар нэмэх
                        </Link>
                    </div>

                    <!-- Ангилал -->
                    <div v-if="categories.length" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-soft">
                        <h3 class="border-b border-gray-100 px-4 py-3 text-sm font-semibold text-gray-900">Ангилал</h3>
                        <Link
                            v-for="cat in categories"
                            :key="cat.id"
                            :href="`/news?category=${cat.slug}`"
                            class="flex items-center justify-between border-b border-gray-50 px-4 py-2.5 text-sm transition last:border-0 hover:bg-brand-50/40"
                            :class="post.category && post.category.slug === cat.slug ? 'font-semibold text-brand-700' : 'text-gray-700'"
                        >
                            <span>{{ cat.name }}</span>
                            <span class="rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ cat.posts_count }}</span>
                        </Link>
                    </div>

                    <!-- Сурталчилгаа (admin-аас удирддаг) -->
                    <BannerDisplay placement="home_sidebar" variant="box" :placeholder="true" />

                    <!-- Холбоотой мэдээ -->
                    <div v-if="related.length" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-soft">
                        <h3 class="mb-1 text-sm font-semibold text-gray-900">Холбоотой мэдээ</h3>
                        <Link
                            v-for="item in related"
                            :key="item.id"
                            :href="`/news/${item.slug}`"
                            class="group flex items-center gap-3 border-b border-gray-50 py-2.5 last:border-0"
                        >
                            <div class="h-12 w-16 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                <img v-if="item.cover_image" :src="item.cover_image" :alt="item.title" class="h-full w-full object-cover" />
                                <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="line-clamp-2 text-sm font-medium text-gray-800 group-hover:text-brand-700">{{ item.title }}</p>
                                <p class="mt-0.5 text-xs text-gray-400">{{ timeAgo(item.published_at) }}</p>
                            </div>
                        </Link>
                    </div>
                </aside>
            </div>
        </div>

        <!-- Lightbox -->
        <Teleport to="body">
            <div
                v-if="lightboxIndex !== null"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4"
                @click.self="closeLightbox"
            >
                <button
                    type="button"
                    class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-2xl text-white transition hover:bg-white/20"
                    aria-label="Хаах"
                    @click="closeLightbox"
                >✕</button>

                <button
                    v-if="gallery.length > 1"
                    type="button"
                    class="absolute left-3 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-3xl text-white transition hover:bg-white/20 sm:left-6"
                    aria-label="Өмнөх"
                    @click.stop="prevImage"
                >‹</button>

                <img :src="gallery[lightboxIndex]" alt="" class="max-h-[85vh] max-w-full rounded-lg object-contain" />

                <button
                    v-if="gallery.length > 1"
                    type="button"
                    class="absolute right-3 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-3xl text-white transition hover:bg-white/20 sm:right-6"
                    aria-label="Дараах"
                    @click.stop="nextImage"
                >›</button>

                <span class="absolute bottom-5 left-1/2 -translate-x-1/2 rounded-full bg-black/50 px-3 py-1 text-sm text-white">
                    {{ lightboxIndex + 1 }} / {{ gallery.length }}
                </span>
            </div>
        </Teleport>
    </PublicLayout>
</template>
