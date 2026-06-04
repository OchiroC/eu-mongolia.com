<script setup>
import BannerDisplay from '@/Components/BannerDisplay.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDate } from '@/lib/date';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    guide: Object,
    related: { type: Array, default: () => [] },
});

const quickLinks = [
    { name: 'Бүх заавар', href: '/guides' },
    { name: 'Мэргэжлийн үйлчилгээ', href: '/professionals' },
    { name: 'Мэдээ', href: '/news' },
];
</script>

<template>
    <Head :title="guide.title" />

    <PublicLayout>
        <Link href="/guides" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">← Заавар руу буцах</Link>

        <div class="mt-4 grid gap-8 lg:grid-cols-[minmax(0,1fr)_300px]">
            <article class="min-w-0">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700">{{ guide.topic_label }}</span>
                    <span v-if="guide.country" class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-500">{{ guide.country }}</span>
                </div>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-gray-900">{{ guide.title }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-400">
                    <span v-if="guide.author">{{ guide.author }}</span>
                    <span v-if="guide.published_at">· {{ formatDate(guide.published_at) }}</span>
                    <span>· {{ guide.views }} үзсэн</span>
                </div>

                <img v-if="guide.cover_image" :src="guide.cover_image" :alt="guide.title" class="mt-6 w-full rounded-2xl" />

                <div class="rich-content mt-6 max-w-none" v-html="guide.body"></div>

                <div class="mt-8 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    ⚠️ Энэхүү заавар нь ерөнхий мэдээллийн зорилготой. Албан ёсны эх сурвалж, холбогдох байгууллагаас баталгаажуулна уу.
                </div>
            </article>

            <aside class="space-y-6 lg:sticky lg:top-20 lg:self-start">
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-soft">
                    <h3 class="border-b border-gray-100 px-4 py-3 text-sm font-semibold text-gray-900">Хэрэгтэй холбоос</h3>
                    <Link v-for="l in quickLinks" :key="l.href" :href="l.href" class="block border-b border-gray-50 px-4 py-2.5 text-sm text-gray-700 transition last:border-0 hover:bg-brand-50/40 hover:text-brand-700">{{ l.name }}</Link>
                </div>

                <BannerDisplay placement="home_sidebar" variant="box" :placeholder="true" />

                <div v-if="related.length" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-soft">
                    <h3 class="mb-2 text-sm font-semibold text-gray-900">Холбоотой заавар</h3>
                    <Link
                        v-for="r in related"
                        :key="r.id"
                        :href="`/guides/${r.slug}`"
                        class="block border-b border-gray-50 py-2.5 last:border-0"
                    >
                        <p class="line-clamp-2 text-sm font-medium text-gray-800 hover:text-brand-700">{{ r.title }}</p>
                        <p class="mt-0.5 text-xs text-gray-400">{{ r.topic_label }}<span v-if="r.country"> · {{ r.country }}</span></p>
                    </Link>
                </div>
            </aside>
        </div>
    </PublicLayout>
</template>
