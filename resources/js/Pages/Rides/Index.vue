<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDateTime } from '@/lib/date';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    rides: Object,
    filters: Object,
});

const user = computed(() => usePage().props.auth?.user);
const from = ref(props.filters.from ?? '');
const to = ref(props.filters.to ?? '');
const date = ref(props.filters.date ?? '');

let timer = null;
watch([from, to, date], () => {
    clearTimeout(timer);
    timer = setTimeout(() => router.get('/rides', {
        from: from.value || undefined,
        to: to.value || undefined,
        date: date.value || undefined,
    }, { preserveState: true, replace: true, preserveScroll: true }), 350);
});
</script>

<template>
    <Head title="Хамтдаа аялах" />

    <PublicLayout>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Хамтдаа аялах</h1>
                <p class="mt-1 text-sm text-gray-500">Хот, улс хооронд машин хуваалцан аялж, зардлаа хэмнэе.</p>
            </div>
            <Link :href="user ? '/rides/new' : '/login'" class="inline-flex w-fit items-center gap-1.5 rounded-full bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Аяллын зар нэмэх
            </Link>
        </div>

        <!-- Шүүлт -->
        <div class="mb-6 grid gap-2 sm:grid-cols-3">
            <input v-model="from" type="text" placeholder="Хаанаас (хот/улс)" class="rounded-lg border-gray-300 text-sm" />
            <input v-model="to" type="text" placeholder="Хаашаа (хот/улс)" class="rounded-lg border-gray-300 text-sm" />
            <input v-model="date" type="date" class="rounded-lg border-gray-300 text-sm" />
        </div>

        <div v-if="rides.data.length" class="space-y-3">
            <Link
                v-for="r in rides.data"
                :key="r.id"
                :href="`/rides/${r.id}`"
                class="group flex items-center justify-between gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-soft transition hover:-translate-y-0.5 hover:shadow-md"
            >
                <div class="min-w-0">
                    <div class="flex items-center gap-2 font-semibold text-gray-900">
                        <span>{{ r.from_city }}</span>
                        <svg class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        <span>{{ r.to_city }}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">🕒 {{ formatDateTime(r.depart_at) }} · {{ r.seats }} суудал · {{ r.user }}</p>
                </div>
                <div class="shrink-0 text-right">
                    <p v-if="r.price" class="font-semibold text-gray-900">{{ r.price }}</p>
                    <p class="text-xs text-gray-400">суудал</p>
                </div>
            </Link>
        </div>

        <div v-else class="rounded-3xl border border-dashed border-gray-200 bg-white py-16 text-center">
            <p class="font-medium text-gray-700">Аяллын зар олдсонгүй</p>
            <Link :href="user ? '/rides/new' : '/login'" class="mt-2 inline-block font-medium text-brand-700 hover:underline">Анхны зараа нэмэх →</Link>
        </div>

        <div v-if="rides.links && rides.links.length > 3" class="mt-8 flex flex-wrap justify-center gap-1">
            <Link
                v-for="link in rides.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </PublicLayout>
</template>
