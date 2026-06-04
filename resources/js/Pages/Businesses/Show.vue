<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    business: Object,
    similar: { type: Array, default: () => [] },
});

const mapSrc = computed(() => props.business.map_query
    ? `https://maps.google.com/maps?q=${encodeURIComponent(props.business.map_query)}&output=embed`
    : null);
const mapsLink = computed(() => props.business.map_query
    ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(props.business.map_query)}`
    : null);

function initial(name) { return (name || '?').charAt(0).toUpperCase(); }
</script>

<template>
    <Head :title="business.name" />

    <PublicLayout>
        <Link href="/businesses" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">← Бизнес лавлах руу буцах</Link>

        <div class="mt-4 grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
            <article class="min-w-0">
                <div class="flex flex-col items-center gap-4 rounded-2xl border border-gray-100 bg-white p-6 text-center shadow-soft sm:flex-row sm:text-left">
                    <span class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-brand-100 text-3xl font-bold text-brand-700">
                        <img v-if="business.photo" :src="business.photo" :alt="business.name" class="h-full w-full object-cover" /><template v-else>{{ initial(business.name) }}</template>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-center gap-1.5 sm:justify-start">
                            <h1 class="text-2xl font-bold text-gray-900">{{ business.name }}</h1>
                            <span v-if="business.is_featured" class="rounded-full bg-amber-400 px-2 py-0.5 text-[10px] font-bold text-amber-900">Онцлох</span>
                        </div>
                        <p class="mt-0.5 font-medium text-brand-700">{{ business.category_label }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ business.address ? business.address + ', ' : '' }}{{ business.city }}<span v-if="business.country">, {{ business.country }}</span></p>
                    </div>
                </div>

                <div v-if="business.description" class="mt-6">
                    <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Танилцуулга</h2>
                    <p class="whitespace-pre-line text-gray-700">{{ business.description }}</p>
                </div>

                <!-- Газрын зураг -->
                <div v-if="mapSrc" class="mt-6">
                    <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Байршил</h2>
                    <div class="overflow-hidden rounded-2xl border border-gray-100">
                        <iframe :src="mapSrc" class="h-72 w-full" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                    <a :href="mapsLink" target="_blank" rel="noopener" class="mt-2 inline-block text-sm text-brand-700 hover:underline">Google Maps дээр нээх →</a>
                </div>

                <div v-if="similar.length" class="mt-8">
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Ижил төрлийн</h2>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <Link v-for="s in similar" :key="s.id" :href="`/businesses/${s.slug}`" class="rounded-xl border border-gray-100 bg-white p-3 text-center">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center overflow-hidden rounded-lg bg-brand-100 text-sm font-bold text-brand-700">
                                <img v-if="s.photo" :src="s.photo" alt="" class="h-full w-full object-cover" /><template v-else>{{ initial(s.name) }}</template>
                            </span>
                            <p class="mt-1.5 line-clamp-1 text-xs font-medium text-gray-800">{{ s.name }}</p>
                            <p class="text-[11px] text-gray-400">{{ s.city }}</p>
                        </Link>
                    </div>
                </div>
            </article>

            <!-- Холбоо барих -->
            <aside class="space-y-6 lg:sticky lg:top-20 lg:self-start">
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-soft">
                    <h3 class="border-b border-gray-100 px-4 py-3 text-sm font-semibold text-gray-900">Холбоо барих</h3>
                    <div class="space-y-2.5 p-4 text-sm">
                        <a v-if="business.phone" :href="`tel:${business.phone}`" class="flex items-center gap-2.5 text-gray-700 hover:text-brand-700">
                            <svg class="h-4 w-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            {{ business.phone }}
                        </a>
                        <a v-if="business.email" :href="`mailto:${business.email}`" class="flex items-center gap-2.5 text-gray-700 hover:text-brand-700">
                            <svg class="h-4 w-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            {{ business.email }}
                        </a>
                        <a v-if="business.website" :href="business.website" target="_blank" rel="noopener" class="flex items-center gap-2.5 text-gray-700 hover:text-brand-700">
                            <svg class="h-4 w-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18M12 3a15 15 0 000 18" /></svg>
                            Вэбсайт
                        </a>
                        <a v-if="business.facebook" :href="business.facebook" target="_blank" rel="noopener" class="flex items-center gap-2.5 text-gray-700 hover:text-brand-700">
                            <svg class="h-4 w-4 text-brand-600" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 10-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0022 12z" /></svg>
                            Facebook
                        </a>
                        <p v-if="business.hours" class="flex items-center gap-2.5 text-gray-500">🕒 {{ business.hours }}</p>
                        <p v-if="!business.phone && !business.email && !business.website && !business.facebook" class="text-gray-400">Холбоо барих мэдээлэл оруулаагүй.</p>
                    </div>
                    <Link v-if="business.owned" href="/my/businesses" class="block border-t border-gray-100 py-2.5 text-center text-xs text-brand-700 hover:underline">Миний бизнес засах</Link>
                </div>
            </aside>
        </div>
    </PublicLayout>
</template>
