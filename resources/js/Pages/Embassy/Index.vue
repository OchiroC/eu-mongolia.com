<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    missions: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
    filters: Object,
});

// Улсаар бүлэглэх
const grouped = computed(() => {
    const map = {};
    for (const m of props.missions) {
        (map[m.country] ??= []).push(m);
    }
    return Object.entries(map).map(([country, items]) => ({ country, items }));
});

function filterCountry(c) {
    router.get('/embassy', c ? { country: c } : {}, { preserveState: true, replace: true, preserveScroll: true });
}
</script>

<template>
    <Head title="Элчин сайдын яам / Яаралтай тусламж" />

    <PublicLayout>
        <h1 class="text-2xl font-bold text-gray-900">Элчин сайдын яам / Яаралтай тусламж</h1>
        <p class="mt-1 text-sm text-gray-500">Европ дахь Монгол улсын төлөөлөгчийн газрууд болон яаралтай тусламжийн дугаар.</p>

        <!-- Яаралтай тусламжийн банер -->
        <div class="mt-5 overflow-hidden rounded-2xl bg-gradient-to-br from-red-600 to-red-700 p-6 text-white">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-red-100">Европын нэгдсэн яаралтай тусламж</p>
                    <p class="mt-1 text-4xl font-extrabold tracking-tight">112</p>
                    <p class="mt-1 text-sm text-red-100">Цагдаа · Түргэн тусламж · Гал команд — ЕХ даяар үнэгүй, 24/7</p>
                </div>
                <a href="tel:112" class="inline-flex w-fit items-center gap-2 rounded-full bg-white px-6 py-3 font-bold text-red-700 transition hover:bg-red-50">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                    112 залгах
                </a>
            </div>
        </div>

        <!-- Улсаар шүүх -->
        <div v-if="countries.length" class="mt-6 flex flex-wrap gap-2">
            <button class="rounded-full px-3 py-1 text-sm transition" :class="!filters.country ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50'" @click="filterCountry(null)">Бүх улс</button>
            <button
                v-for="c in countries"
                :key="c"
                class="rounded-full px-3 py-1 text-sm transition"
                :class="filters.country === c ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50'"
                @click="filterCountry(c)"
            >{{ c }}</button>
        </div>

        <!-- Төлөөлөгчийн газрууд -->
        <div v-if="grouped.length" class="mt-6 space-y-8">
            <div v-for="group in grouped" :key="group.country">
                <h2 class="mb-3 text-lg font-bold text-gray-900">{{ group.country }}</h2>
                <div class="grid gap-4 lg:grid-cols-2">
                    <div v-for="m in group.items" :key="m.id" class="rounded-2xl border border-gray-100 bg-white p-5 shadow-soft">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ m.name }}</h3>
                                <p class="text-xs text-brand-700">{{ m.kind_label }}<span v-if="m.city"> · {{ m.city }}</span></p>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1.5 text-sm text-gray-600">
                            <p v-if="m.address" class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ m.address }}
                            </p>
                            <a v-if="m.phone" :href="`tel:${m.phone}`" class="flex items-center gap-2 hover:text-brand-700">
                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                {{ m.phone }}
                            </a>
                            <a v-if="m.emergency_phone" :href="`tel:${m.emergency_phone}`" class="flex items-center gap-2 font-medium text-red-600 hover:text-red-700">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.71-3L13.71 4a2 2 0 00-3.42 0L3.36 16a2 2 0 001.71 3z" /></svg>
                                Яаралтай: {{ m.emergency_phone }}
                            </a>
                            <a v-if="m.email" :href="`mailto:${m.email}`" class="flex items-center gap-2 hover:text-brand-700">
                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                {{ m.email }}
                            </a>
                            <a v-if="m.website" :href="m.website" target="_blank" rel="noopener" class="flex items-center gap-2 hover:text-brand-700">
                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18M12 3a15 15 0 000 18" /></svg>
                                Вэбсайт
                            </a>
                            <p v-if="m.hours" class="text-xs text-gray-400">🕒 {{ m.hours }}</p>
                            <p v-if="m.notes" class="mt-1 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-500">{{ m.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="mt-6 rounded-2xl border border-dashed border-gray-200 bg-white py-16 text-center">
            <p class="font-medium text-gray-700">Мэдээлэл оруулаагүй байна</p>
            <p class="text-sm text-gray-400">Удахгүй нэмэгдэнэ.</p>
        </div>

        <p class="mt-6 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">
            ⚠️ Холбоо барих мэдээллийг очихоосоо өмнө албан ёсны вэбсайтаас баталгаажуулна уу.
        </p>
    </PublicLayout>
</template>
