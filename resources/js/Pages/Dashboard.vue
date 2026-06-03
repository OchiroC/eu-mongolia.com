<script setup>
import StatCard from '@/Components/StatCard.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Button from '@/Components/ui/Button.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    stats: Object,
    myListings: Array,
    upcomingTickets: Array,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const statusLabel = { active: 'Идэвхтэй', sold: 'Зарагдсан', inactive: 'Нуусан' };
const statusClass = {
    active: 'bg-emerald-50 text-emerald-700',
    sold: 'bg-gray-100 text-gray-600',
    inactive: 'bg-amber-50 text-amber-700',
};

function priceLabel(l) {
    if (l.price_type === 'free') return 'Үнэгүй';
    if (l.price_type === 'giveaway') return 'Дайна';
    if (l.price === null || l.price === undefined) return 'Тохиролцоно';
    const p = Number(l.price).toLocaleString('mn-MN') + ' €';
    return l.price_type === 'negotiable' ? p + ' (VB)' : p;
}
function ticketDate(value) {
    if (!value) return '';
    return new Date(value).toLocaleDateString('mn-MN', { month: 'short', day: 'numeric' });
}
function setSold(l) {
    router.patch(`/zar/${l.id}/status`, { status: l.status === 'sold' ? 'active' : 'sold' }, { preserveScroll: true });
}
</script>

<template>
    <Head title="Миний самбар" />

    <PublicLayout>
        <!-- Мэндчилгээ -->
        <div class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 p-6 text-white">
            <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold">Сайн байна уу, {{ user?.name }} 👋</h1>
                    <p class="mt-1 text-brand-100">Таны зар, тасалбарын хураангуй.</p>
                </div>
                <Link href="/zar/new" class="inline-flex shrink-0 items-center rounded-full bg-white px-5 py-2.5 font-semibold text-brand-700 shadow-lg transition hover:bg-brand-50">
                    + Зар нэмэх
                </Link>
            </div>
        </div>

        <!-- Статистик -->
        <div class="grid gap-4 sm:grid-cols-3">
            <StatCard label="Идэвхтэй зар" :value="stats.listings_active" icon="ad" accent="blue" :sub="`${stats.listings} нийт`" />
            <StatCard label="Зарагдсан" :value="stats.listings_sold" icon="chart" accent="green" />
            <StatCard label="Авсан тасалбар" :value="stats.tickets" icon="ticket" accent="purple" />
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <!-- Гол: Миний зар -->
            <div class="lg:col-span-2">
                <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Миний зар</h2>
                        <Link href="/my/zar" class="text-sm font-semibold text-brand-700 hover:underline">Бүгдийг удирдах →</Link>
                    </div>

                    <div v-if="myListings.length" class="space-y-3">
                        <div v-for="l in myListings" :key="l.id" class="flex items-center gap-4 rounded-xl p-2 transition hover:bg-gray-50">
                            <Link :href="`/zar/${l.slug}`" class="h-16 w-20 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                <img v-if="l.cover" :src="l.cover" class="h-full w-full object-cover" />
                                <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                            </Link>
                            <div class="min-w-0 flex-1">
                                <Link :href="`/zar/${l.slug}`" class="block truncate font-medium text-gray-900 hover:text-brand-700">{{ l.title }}</Link>
                                <p class="text-sm font-bold text-gray-900">{{ priceLabel(l) }}</p>
                                <div class="mt-0.5 flex items-center gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass[l.status]">{{ statusLabel[l.status] }}</span>
                                    <span class="text-xs text-gray-400">👁 {{ l.views }}</span>
                                </div>
                            </div>
                            <div class="flex shrink-0 gap-1.5">
                                <Button variant="secondary" size="sm" @click="setSold(l)">
                                    {{ l.status === 'sold' ? 'Сэргээх' : 'Зарагдсан' }}
                                </Button>
                                <Button :as="Link" :href="`/zar/${l.id}/edit`" variant="secondary" size="sm">Засах</Button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-10 text-center">
                        <p class="text-gray-500">Та одоогоор зар нийтлээгүй байна.</p>
                        <Link href="/zar/new" class="mt-2 inline-block font-semibold text-brand-700 hover:underline">Анхны зараа нэмэх →</Link>
                    </div>
                </div>
            </div>

            <!-- Хажуу: тасалбар + холбоос -->
            <div class="space-y-6">
                <div class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-gray-100">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Миний тасалбар</h2>
                        <Link href="/my/tickets" class="text-xs font-semibold text-brand-700 hover:underline">Бүгд →</Link>
                    </div>
                    <div v-if="upcomingTickets.length" class="space-y-2">
                        <Link
                            v-for="o in upcomingTickets"
                            :key="o.id"
                            :href="`/orders/${o.id}`"
                            class="flex items-center gap-3 rounded-xl p-2 transition hover:bg-gray-50"
                        >
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-gray-800">{{ o.event }}</p>
                                <p class="text-xs text-gray-400">{{ ticketDate(o.starts_at) }} · {{ o.tickets_count }} тас.</p>
                            </div>
                        </Link>
                    </div>
                    <p v-else class="py-4 text-center text-sm text-gray-400">
                        Тасалбар алга.
                        <Link href="/events" class="text-brand-700 hover:underline">Эвент үзэх</Link>
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-gray-100">
                    <h2 class="mb-3 font-semibold text-gray-900">Хурдан холбоос</h2>
                    <div class="space-y-1.5 text-sm">
                        <Link href="/zar" class="block rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-50">🛒 Зар үзэх</Link>
                        <Link href="/events" class="block rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-50">🎫 Эвент үзэх</Link>
                        <Link href="/news" class="block rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-50">📰 Мэдээ унших</Link>
                        <Link href="/profile" class="block rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-50">⚙️ Профайл засах</Link>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
