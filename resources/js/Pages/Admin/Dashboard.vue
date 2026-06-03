<script setup>
import BarChart from '@/components/BarChart.vue';
import StatCard from '@/components/StatCard.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { timeAgo } from '@/lib/date';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    visitSeries: Array,
    recentOrders: Array,
    topEvents: Array,
    pendingBanners: Array,
    recentListings: { type: Array, default: () => [] },
    topPages: { type: Array, default: () => [] },
});

function priceLabel(l) {
    if (l.price_type === 'free') return 'Үнэгүй';
    if (l.price_type === 'giveaway') return 'Дайна';
    if (l.price === null || l.price === undefined) return 'Тохиролцоно';
    return Number(l.price).toLocaleString('mn-MN') + ' €';
}

const maxHits = computed(() => Math.max(1, ...(props.topPages || []).map((p) => p.hits)));

const page = usePage();
const adminName = computed(() => page.props.auth?.user?.name ?? 'Админ');

const placementLabels = {
    home_top: 'Нүүр-дээд',
    home_sidebar: 'Нүүр-хажуу',
    news_top: 'Мэдээ-дээд',
    footer: 'Хөл',
};

const ctr = computed(() => {
    if (!props.stats.banner_impressions) return 0;
    return ((props.stats.banner_clicks / props.stats.banner_impressions) * 100).toFixed(1);
});

const maxTopSold = computed(() => Math.max(1, ...(props.topEvents || []).map((e) => e.sold)));

function money(value) {
    return Number(value).toLocaleString('mn-MN') + '€';
}

const quickActions = [
    { name: 'Мэдээ нэмэх', href: '/admin/posts/create', icon: 'news' },
    { name: 'Эвент нэмэх', href: '/admin/events/create', icon: 'calendar' },
    { name: 'Баннер нэмэх', href: '/admin/banners/create', icon: 'ad' },
    { name: 'Тасалбар шалгах', href: '/admin/check-in', icon: 'ticket' },
];
</script>

<template>
    <Head title="Удирдлагын самбар" />

    <AdminLayout>
        <template #title>Хяналтын самбар</template>

        <!-- Мэндчилгээ + хурдан үйлдэл -->
        <div class="relative mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 p-6 text-white">
            <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-16 right-32 h-40 w-40 rounded-full bg-brand-400/20 blur-2xl"></div>
            <div class="relative flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <div>
                    <h2 class="text-2xl font-bold">Сайн байна уу, {{ adminName }} 👋</h2>
                    <p class="mt-1 text-brand-100">Платформын өнөөдрийн ерөнхий байдал.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        v-for="a in quickActions"
                        :key="a.href"
                        :href="a.href"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white/15 px-3 py-2 text-sm font-medium backdrop-blur transition hover:bg-white/25"
                    >
                        + {{ a.name }}
                    </Link>
                </div>
            </div>
        </div>

        <!-- Гол үзүүлэлтүүд -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard label="Зочид" :value="Number(stats.visitors).toLocaleString()" icon="chart" accent="green"
                :sub="`өнөөдөр ${stats.visits_today}`" />
            <StatCard label="Идэвхтэй зар" :value="Number(stats.listings).toLocaleString()" icon="tag" accent="blue"
                :sub="`өнөөдөр +${stats.listings_today} · нийт ${stats.listings_total}`" />
            <StatCard label="Хэрэглэгч" :value="stats.users" icon="users" accent="purple" />
            <StatCard label="Идэвхтэй баннер" :value="stats.banners_active" icon="ad" accent="amber"
                :sub="`${stats.banners_pending} хүлээгдэж буй`" />
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <!-- Зочдын график -->
            <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100 lg:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900">Зочдын урсгал</h3>
                        <p class="text-sm text-gray-400">Сүүлийн 14 хоног</p>
                    </div>
                    <span class="rounded-lg bg-brand-50 px-3 py-1 text-sm font-semibold text-brand-700">{{ Number(stats.visits_total).toLocaleString() }} нийт</span>
                </div>
                <BarChart :data="visitSeries" unit="" />
            </div>

            <!-- Контент + баннер -->
            <div class="space-y-4">
                <div class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-gray-100">
                    <h3 class="mb-3 font-semibold text-gray-900">Контент</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Нийтлэгдсэн мэдээ</span>
                            <span class="font-semibold text-gray-900">{{ stats.posts_published }} / {{ stats.posts }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Удахгүй болох эвент</span>
                            <span class="font-semibold text-gray-900">{{ stats.events_upcoming }} / {{ stats.events }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-2.5">
                            <span class="text-gray-500">Зарагдсан тасалбар</span>
                            <span class="font-semibold text-gray-900">{{ stats.tickets_sold }} <span class="text-xs font-normal text-gray-400">({{ stats.orders_pending }} хүлээгдэж буй)</span></span>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-soft ring-1 ring-gray-100">
                    <h3 class="mb-3 font-semibold text-gray-900">Баннерын гүйцэтгэл</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Нийт үзэлт</span>
                            <span class="font-semibold text-gray-900">{{ stats.banner_impressions.toLocaleString() }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Нийт клик</span>
                            <span class="font-semibold text-gray-900">{{ stats.banner_clicks.toLocaleString() }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 pt-2.5">
                            <span class="text-gray-500">CTR</span>
                            <span class="rounded-md bg-emerald-50 px-2 py-0.5 font-semibold text-emerald-700">{{ ctr }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <!-- Сүүлийн захиалга -->
            <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100">
                <h3 class="mb-4 font-semibold text-gray-900">Сүүлийн захиалга</h3>
                <div v-if="recentOrders.length" class="space-y-1">
                    <div v-for="o in recentOrders" :key="o.id" class="flex items-center justify-between rounded-xl px-3 py-2.5 transition hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-700">{{ (o.buyer || '?').charAt(0).toUpperCase() }}</span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ o.event ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ o.buyer }} · {{ o.created_at }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ money(o.total) }}</p>
                            <span
                                class="text-xs font-medium"
                                :class="o.status === 'paid' ? 'text-emerald-600' : 'text-amber-600'"
                            >
                                {{ o.status === 'paid' ? '● Төлөгдсөн' : '○ Хүлээгдэж буй' }}
                            </span>
                        </div>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-gray-400">Захиалга алга байна.</p>
            </div>

            <!-- Шилдэг эвент + хүлээгдэж буй баннер -->
            <div class="space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100">
                    <h3 class="mb-4 font-semibold text-gray-900">Шилдэг эвент</h3>
                    <div v-if="topEvents.length" class="space-y-4">
                        <div v-for="e in topEvents" :key="e.id">
                            <div class="mb-1.5 flex items-center justify-between text-sm">
                                <span class="font-medium text-gray-800">{{ e.title }}</span>
                                <span class="text-gray-500">{{ e.sold }} ш · {{ money(e.revenue) }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-700" :style="{ width: `${(e.sold / maxTopSold) * 100}%` }"></div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="py-6 text-center text-sm text-gray-400">Зарагдсан тасалбар алга.</p>
                </div>

                <div v-if="pendingBanners.length" class="rounded-2xl bg-amber-50 p-6 ring-1 ring-amber-200">
                    <h3 class="mb-3 flex items-center gap-2 font-semibold text-amber-800">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-200 text-xs">{{ pendingBanners.length }}</span>
                        Зөвшөөрөл хүлээж буй баннер
                    </h3>
                    <div class="space-y-2">
                        <Link
                            v-for="b in pendingBanners"
                            :key="b.id"
                            href="/admin/banners"
                            class="flex items-center justify-between rounded-xl bg-white px-3 py-2.5 text-sm transition hover:shadow-sm"
                        >
                            <span class="font-medium text-gray-800">{{ b.title }}</span>
                            <span class="text-gray-500">{{ placementLabels[b.placement] }} · {{ money(b.price) }}</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Шинэ зар + Хамгийн их зочилсон хуудас -->
        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <!-- Шинэ зар -->
            <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100 lg:col-span-2">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Шинэ зар</h3>
                    <Link href="/zar" class="text-sm font-medium text-brand-700 hover:underline">Бүгд →</Link>
                </div>
                <div v-if="recentListings.length" class="space-y-1">
                    <Link
                        v-for="l in recentListings"
                        :key="l.id"
                        :href="`/zar/${l.slug}`"
                        class="flex items-center gap-3 rounded-xl px-2 py-2 transition hover:bg-gray-50"
                    >
                        <div class="h-12 w-14 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                            <img v-if="l.cover" :src="l.cover" alt="" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900">{{ l.title }}</p>
                            <p class="truncate text-xs text-gray-400">{{ l.category ?? '—' }}<span v-if="l.city"> · {{ l.city }}</span> · {{ timeAgo(l.created_at) }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ priceLabel(l) }}</p>
                            <span class="text-xs" :class="l.status === 'active' ? 'text-emerald-600' : 'text-gray-400'">{{ l.status === 'active' ? 'Идэвхтэй' : l.status === 'sold' ? 'Зарагдсан' : 'Нуусан' }}</span>
                        </div>
                    </Link>
                </div>
                <p v-else class="py-8 text-center text-sm text-gray-400">Зар алга байна.</p>
            </div>

            <!-- Хамгийн их зочилсон хуудас -->
            <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100">
                <h3 class="mb-1 font-semibold text-gray-900">Их үзсэн хуудас</h3>
                <p class="mb-4 text-sm text-gray-400">Сүүлийн 30 хоног</p>
                <div v-if="topPages.length" class="space-y-3">
                    <div v-for="p in topPages" :key="p.label">
                        <div class="mb-1 flex items-center justify-between text-sm">
                            <span class="truncate font-medium text-gray-700">{{ p.label }}</span>
                            <span class="shrink-0 text-gray-500">{{ p.hits.toLocaleString() }}</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-gradient-to-r from-brand-400 to-brand-600" :style="{ width: `${(p.hits / maxHits) * 100}%` }"></div>
                        </div>
                    </div>
                </div>
                <p v-else class="py-8 text-center text-sm text-gray-400">Өгөгдөл хараахан алга.</p>
            </div>
        </div>
    </AdminLayout>
</template>
