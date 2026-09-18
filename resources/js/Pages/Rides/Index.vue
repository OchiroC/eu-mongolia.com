<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import TravelTabs from '@/Components/TravelTabs.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Clock, Plus } from 'lucide-vue-next';
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
        <PageHeader kicker="Хамт аялах" title="Хамтдаа аялах" subtitle="Хот, улс хооронд машинаар хамт явах хүн олж, замын зардлаа хуваана.">
            <template #actions>
                <Link :href="user ? '/rides/new' : '/login'" class="inline-flex h-10 items-center gap-1.5 rounded-md bg-brand-600 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-500">
                    <Plus class="h-4 w-4" /> Аяллын зар нэмэх
                </Link>
            </template>
        </PageHeader>

        <TravelTabs />

        <!-- Шүүлт -->
        <div class="mb-6 grid gap-2 sm:grid-cols-3">
            <input v-model="from" type="text" placeholder="Хаанаас (хот/улс)" class="rounded-md border-brand-200 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600" />
            <input v-model="to" type="text" placeholder="Хаашаа (хот/улс)" class="rounded-md border-brand-200 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600" />
            <input v-model="date" type="date" class="rounded-md border-brand-200 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600" />
        </div>

        <div v-if="rides.data.length" class="space-y-3">
            <Link
                v-for="r in rides.data"
                :key="r.id"
                :href="`/rides/${r.id}`"
                class="group flex items-center justify-between gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-card transition duration-300 hover:shadow-card-lg"
            >
                <div class="min-w-0">
                    <div class="flex items-center gap-2 font-semibold text-gray-900">
                        <span>{{ r.from_city }}</span>
                        <svg class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        <span>{{ r.to_city }}</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500"><Clock class="mr-1 inline-block h-3.5 w-3.5 align-[-2px]" />{{ formatDateTime(r.depart_at) }} · {{ r.seats }} суудал · {{ r.user }}<span v-if="r.flight"> · <span class="font-mono">{{ r.flight.code }}</span> нислэгтэй</span></p>
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
                :class="[link.active ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>
    </PublicLayout>
</template>
