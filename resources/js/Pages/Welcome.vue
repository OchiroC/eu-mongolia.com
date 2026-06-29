<script setup>
import BannerDisplay from '@/Components/BannerDisplay.vue';
import ListingCard from '@/Components/ListingCard.vue';
import Logo from '@/Components/Logo.vue';
import ProfessionalCard from '@/Components/ProfessionalCard.vue';
import Select from '@/Components/ui/Select.vue';
import SelectContent from '@/Components/ui/SelectContent.vue';
import SelectItem from '@/Components/ui/SelectItem.vue';
import SelectTrigger from '@/Components/ui/SelectTrigger.vue';
import SelectValue from '@/Components/ui/SelectValue.vue';
import { formatDateTime, timeAgo } from '@/lib/date';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    categories: { type: Array, default: () => [] },
    latestListings: { type: Array, default: () => [] },
    featuredListings: { type: Array, default: () => [] },
    featuredNews: { type: Array, default: () => [] },
    featuredEvents: { type: Array, default: () => [] },
    upcomingEvents: { type: Array, default: () => [] },
    featuredProfessionals: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ listings: 0, news: 0 }) },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const heroEvent = computed(() => props.featuredEvents[0] ?? null);
const restEvents = computed(() => props.featuredEvents.slice(1));

const search = ref('');
const category = ref('');
const location = ref('');

// shadcn Select-д зориулсан проксик ('all' = бүх ангилал).
const catModel = computed({
    get: () => category.value || 'all',
    set: (v) => { category.value = v === 'all' ? '' : v; },
});

function doSearch() {
    const params = new URLSearchParams();
    if (search.value.trim()) params.set('search', search.value.trim());
    if (category.value) params.set('category', category.value);
    if (location.value.trim()) params.set('location', location.value.trim());
    window.location.href = '/zar' + (params.toString() ? `?${params}` : '');
}

const popularSearches = ['Машин', 'iPhone', 'Орон сууц', 'Ажил', 'Тавилга'];
function chipSearch(term) {
    window.location.href = `/zar?search=${encodeURIComponent(term)}`;
}

const catIcon = {
    car: 'M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13m-14 0h14m-14 0v4a1 1 0 001 1h1a1 1 0 001-1v-1h8v1a1 1 0 001 1h1a1 1 0 001-1v-4M7 16h.01M17 16h.01',
    home: 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h12a1 1 0 001-1V10',
    briefcase: 'M21 13.255A23.9 23.9 0 0112 15c-3.18 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    device: 'M9 17.25v1.007a3 3 0 01-.879 2.122L7 21.5h10l-1.121-1.121A3 3 0 0115 18.257V17.25m6-12V15a2 2 0 01-2 2H5a2 2 0 01-2-2V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2 2 0 01-2 2H5a2 2 0 01-2-2V5.25',
    sofa: 'M3 10V7a2 2 0 012-2h14a2 2 0 012 2v3M3 10a2 2 0 012 2v3h14v-3a2 2 0 012-2M3 10h18M5 18v2m14-2v2',
    shirt: 'M16 4l4 4-3 2v10H7V10L4 8l4-4 4 2 4-2z',
    wrench: 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63',
    tag: 'M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z',
};

function fmt(n) {
    return Number(n || 0).toLocaleString('mn-MN');
}

function priceLabel(l) {
    if (l.price_type === 'free') return 'Үнэгүй';
    if (l.price_type === 'giveaway') return 'Дайна';
    if (l.price === null || l.price === undefined) return 'Тохиролцоно';
    const p = Number(l.price).toLocaleString('mn-MN') + ' €';
    return l.price_type === 'negotiable' ? p + ' (VB)' : p;
}

function eventDay(value) {
    return value ? new Date(value).getDate() : '';
}
function eventMonth(value) {
    return value ? new Date(value).toLocaleDateString('mn-MN', { month: 'short' }) : '';
}
</script>

<template>
    <Head title="Нүүр" />

    <div class="min-h-screen bg-gray-50 text-gray-900">
        <!-- Дээд туслах мөр -->
        <div class="bg-gray-900 text-gray-300">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-2 text-xs">
                <span>
                    <span class="font-semibold text-white">{{ fmt(stats.listings) }}+</span> зар нийтлэгдсэн
                </span>
                <div class="flex items-center gap-4">
                    <template v-if="user">
                        <Link href="/my/favorites" class="hover:text-white">Хадгалсан</Link>
                        <Link href="/dashboard" class="font-medium text-white hover:text-brand-300">Самбар</Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="hover:text-white">Нэвтрэх</Link>
                        <Link href="/register" class="font-medium text-white hover:text-brand-300">Бүртгүүлэх</Link>
                    </template>
                </div>
            </div>
        </div>

        <!-- Толгой -->
        <header class="sticky top-0 z-30 border-b border-gray-100/80 bg-white/75 backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-3.5">
                <Logo size="md" badge="gradient" tone="dark" class="shrink-0" />

                <nav class="hidden items-center gap-1 text-sm md:flex">
                    <Link href="/zar" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">Зар</Link>
                    <Link href="/housing" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">Орон сууц</Link>
                    <Link href="/jobs" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">Ажил</Link>
                    <Link href="/rides" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">Аялал</Link>
                    <Link href="/questions" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">Асуулт</Link>
                    <Link href="/guides" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">Guide</Link>
                    <Link href="/news" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">Мэдээ</Link>
                    <Link href="/events" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">Эвент</Link>
                    <Link href="/professionals" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">Туслах</Link>
                    <Link href="/businesses" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">Бизнес</Link>
                    <Link href="/kids" class="rounded-lg px-3 py-2 font-medium text-gray-600 hover:text-gray-900">Хүүхэд</Link>
                </nav>

                <Link :href="user ? '/zar/new' : '/register'" class="flex shrink-0 items-center gap-1.5 rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-brand-700 hover:shadow-brand-glow active:translate-y-0">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                    Зар нэмэх
                </Link>
            </div>
        </header>

        <!-- Хайлтын мөр -->
        <section class="border-b border-gray-100 bg-white">
            <div class="mx-auto max-w-7xl px-5 py-4">
                <div class="flex flex-col items-stretch gap-1.5 rounded-2xl border border-gray-200/80 bg-white p-2 shadow-card-md ring-1 ring-transparent transition focus-within:border-brand-300 focus-within:shadow-card-lg focus-within:ring-brand-100 sm:flex-row sm:items-center">
                    <Select v-model="catModel">
                        <SelectTrigger class="h-12 border-0 text-gray-600 focus:ring-0 focus:ring-offset-0 sm:w-48 sm:border-r sm:border-gray-100">
                            <SelectValue placeholder="Бүх ангилал" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Бүх ангилал</SelectItem>
                            <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.slug">{{ cat.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <div class="flex min-w-0 flex-1 items-center gap-2 px-3">
                        <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Юу хайж байна?"
                            class="min-w-0 flex-1 border-0 bg-transparent py-3 text-gray-900 placeholder-gray-400 focus:ring-0"
                            @keydown.enter="doSearch"
                        />
                    </div>
                    <input
                        v-model="location"
                        type="text"
                        placeholder="Хот"
                        class="hidden w-36 border-0 border-l border-gray-100 bg-transparent px-3 py-3 text-gray-900 placeholder-gray-400 focus:ring-0 lg:block"
                        @keydown.enter="doSearch"
                    />
                    <button class="shrink-0 rounded-xl bg-brand-600 px-7 py-3 font-semibold text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-brand-700 hover:shadow-brand-glow active:translate-y-0" @click="doSearch">
                        Хайх
                    </button>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-gray-400">Түгээмэл:</span>
                    <button
                        v-for="term in popularSearches"
                        :key="term"
                        class="rounded-full border border-gray-200 px-3 py-1 text-gray-600 transition hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700"
                        @click="chipSearch(term)"
                    >
                        {{ term }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Их бие: хоёр багана -->
        <div class="mx-auto grid max-w-7xl gap-6 px-5 py-6 lg:grid-cols-[290px_1fr]">
            <!-- Зүүн sidebar -->
            <aside class="order-last space-y-5 lg:order-first lg:sticky lg:top-20 lg:self-start">
                <!-- Ангилал -->
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-soft">
                    <h3 class="border-b border-gray-100 px-4 py-3 text-sm font-semibold text-gray-900">Ангилал</h3>
                    <Link
                        v-for="cat in categories"
                        :key="cat.id"
                        :href="`/zar?category=${cat.slug}`"
                        class="group flex items-center gap-3 border-b border-gray-50 px-4 py-3 transition last:border-0 hover:bg-brand-50/40"
                    >
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" :d="catIcon[cat.icon] || catIcon.tag" /></svg>
                        </span>
                        <span class="flex-1 truncate text-sm font-medium text-gray-700 group-hover:text-brand-700">{{ cat.name }}</span>
                        <span class="shrink-0 rounded-md bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">{{ fmt(cat.listings_count) }}</span>
                    </Link>
                </div>

                <!-- Сурталчилгаа (admin-аас удирддаг: home_sidebar) -->
                <BannerDisplay placement="home_sidebar" variant="box" :placeholder="true" />
            </aside>

            <!-- Баруун үндсэн контент -->
            <main class="space-y-12">
                <!-- Онцлох эвент — том зурагтай hero -->
                <section v-if="heroEvent" class="space-y-4">
                    <!-- Гол том эвент -->
                    <Link :href="`/events/${heroEvent.slug}`" class="group relative block overflow-hidden rounded-[2rem] bg-gray-900 shadow-card-lg ring-1 ring-black/5">
                        <img
                            v-if="heroEvent.cover_image"
                            :src="heroEvent.cover_image"
                            :alt="heroEvent.title"
                            class="h-80 w-full object-cover opacity-95 transition duration-700 group-hover:scale-105 sm:h-[440px]"
                        />
                        <div v-else class="h-80 w-full bg-gradient-to-br from-brand-500 via-brand-600 to-brand-800 sm:h-[440px]"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/30 to-transparent"></div>

                        <!-- Огнооны тэмдэг -->
                        <div class="absolute right-5 top-5 flex h-16 w-16 flex-col items-center justify-center rounded-2xl bg-white/95 text-gray-900 shadow-lg backdrop-blur">
                            <span class="text-2xl font-bold leading-none">{{ eventDay(heroEvent.starts_at) }}</span>
                            <span class="mt-0.5 text-[10px] uppercase text-gray-500">{{ eventMonth(heroEvent.starts_at) }}</span>
                        </div>

                        <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400 px-3 py-1 text-xs font-bold text-amber-900">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                                Онцлох эвент
                            </span>
                            <h2 class="mt-3 line-clamp-2 max-w-2xl text-2xl font-bold text-white sm:text-3xl">{{ heroEvent.title }}</h2>
                            <div class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-gray-200">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ formatDateTime(heroEvent.starts_at) }}
                                </span>
                                <span v-if="heroEvent.venue || heroEvent.city" class="inline-flex items-center gap-1.5">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    {{ heroEvent.venue }}<span v-if="heroEvent.city">, {{ heroEvent.city }}</span>
                                </span>
                            </div>
                            <span class="mt-4 inline-flex w-fit items-center gap-1.5 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-gray-900 transition group-hover:bg-brand-600 group-hover:text-white">
                                Дэлгэрэнгүй
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                            </span>
                        </div>
                    </Link>

                    <!-- Бусад онцлох эвент -->
                    <div v-if="restEvents.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="event in restEvents"
                            :key="event.id"
                            :href="`/events/${event.slug}`"
                            class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-card-lg"
                        >
                            <div class="relative aspect-video overflow-hidden bg-gray-100">
                                <img v-if="event.cover_image" :src="event.cover_image" :alt="event.title" class="h-full w-full object-cover" />
                                <div v-else class="h-full w-full bg-gradient-to-br from-brand-500 to-brand-700"></div>
                                <div class="absolute left-3 top-3 flex h-12 w-12 flex-col items-center justify-center rounded-xl bg-white/95 text-gray-900 shadow-sm backdrop-blur">
                                    <span class="text-base font-bold leading-none">{{ eventDay(event.starts_at) }}</span>
                                    <span class="text-[9px] uppercase text-gray-500">{{ eventMonth(event.starts_at) }}</span>
                                </div>
                            </div>
                            <div class="flex flex-1 flex-col p-4">
                                <h3 class="line-clamp-2 font-semibold text-gray-900 group-hover:text-brand-700">{{ event.title }}</h3>
                                <p class="mt-1 truncate text-sm text-gray-400">{{ event.venue }}<span v-if="event.city">, {{ event.city }}</span></p>
                            </div>
                        </Link>
                    </div>
                </section>

                <!-- Онцлох эвент байхгүй бол: сайтын өөрийн hero -->
                <section v-else>
                    <div class="hero-aurora relative overflow-hidden rounded-[2rem] shadow-card-lg ring-1 ring-white/10">
                        <!-- Grid texture + хөвөх гэрлүүд -->
                        <div class="pointer-events-none absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)]"></div>
                        <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 animate-float rounded-full bg-white/20 blur-3xl"></div>
                        <div class="pointer-events-none absolute -bottom-24 -left-16 h-72 w-72 animate-float-slow rounded-full bg-brand-300/30 blur-3xl"></div>

                        <div class="relative flex min-h-[22rem] flex-col justify-center px-6 py-12 sm:min-h-[28rem] sm:px-14">
                            <span class="inline-flex w-fit animate-fade-up items-center gap-1.5 rounded-full bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-white ring-1 ring-white/20 backdrop-blur">
                                <span class="relative flex h-2 w-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-300 opacity-75"></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                                </span>
                                Yazguur
                            </span>
                            <h2 class="mt-5 max-w-3xl animate-fade-up text-4xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-5xl lg:text-6xl" style="animation-delay: 60ms">
                                Европ дахь монголчуудын
                                <span class="text-gradient-light">нэгдсэн платформ</span>
                            </h2>
                            <p class="mt-4 max-w-xl animate-fade-up text-base leading-relaxed text-brand-100 sm:text-lg" style="animation-delay: 120ms">
                                Худалдаа, ажил, орон сууц, арга хэмжээ, мэдээлэл, зөвлөгөө — хэрэгтэй бүхнээ нэг дороос.
                            </p>
                            <div class="mt-7 flex animate-fade-up flex-wrap gap-3" style="animation-delay: 180ms">
                                <Link href="/zar" class="group inline-flex items-center gap-1.5 rounded-full bg-white px-6 py-3 text-sm font-semibold text-brand-700 shadow-lg transition duration-200 hover:-translate-y-0.5 hover:shadow-xl active:translate-y-0">
                                    Зар үзэх
                                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </Link>
                                <Link :href="user ? '/zar/new' : '/register'" class="glass-dark inline-flex items-center gap-1.5 rounded-full px-6 py-3 text-sm font-semibold text-white transition duration-200 hover:-translate-y-0.5 hover:bg-white/20 active:translate-y-0">
                                    {{ user ? 'Зар нэмэх' : 'Нэгдэх' }}
                                </Link>
                            </div>

                            <!-- Итгэлийн дохио -->
                            <div class="mt-8 flex animate-fade-up flex-wrap items-center gap-x-7 gap-y-2 text-sm text-brand-100/90" style="animation-delay: 240ms">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 text-brand-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    Үнэгүй зар нийтлэх
                                </span>
                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 text-brand-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                    Европ даяар
                                </span>
                                <span class="inline-flex items-center gap-2">
                                    <svg class="h-4 w-4 text-brand-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Хотоор хайх
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Сурталчилгаа (admin-аас удирддаг: home_top) -->
                <BannerDisplay placement="home_top" variant="leaderboard" :placeholder="true" />

                <!-- Шинэ зар -->
                <section>
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="flex items-center gap-2.5 text-xl font-bold text-gray-900">
                            <span class="h-5 w-1.5 rounded-full bg-brand-600"></span>
                            Шинэ зар
                        </h2>
                        <Link href="/zar" class="group inline-flex items-center gap-1 text-sm font-medium text-brand-700">
                            Бүгд
                            <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </Link>
                    </div>
                    <div v-if="latestListings.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <ListingCard v-for="l in latestListings" :key="l.id" :listing="l" />
                    </div>
                    <div v-else class="rounded-2xl border border-dashed border-gray-200 bg-white py-16 text-center">
                        <p class="text-gray-500">Одоогоор зар алга байна.</p>
                        <Link href="/zar/new" class="mt-3 inline-block font-medium text-brand-700 hover:underline">Анхны зараа нэмэх →</Link>
                    </div>
                </section>

                <!-- Мэдээ -->
                <section v-if="featuredNews.length">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="flex items-center gap-2.5 text-xl font-bold text-gray-900">
                            <span class="h-5 w-1.5 rounded-full bg-brand-600"></span>
                            Мэдээ
                        </h2>
                        <Link href="/news" class="group inline-flex items-center gap-1 text-sm font-medium text-brand-700">
                            Бүгд
                            <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </Link>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="post in featuredNews"
                            :key="post.id"
                            :href="`/news/${post.slug}`"
                            class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-card-lg"
                        >
                            <div class="relative aspect-video overflow-hidden bg-gray-100">
                                <img v-if="post.cover_image" :src="post.cover_image" :alt="post.title" class="h-full w-full object-cover" />
                                <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m-6 8h6m-6-4h6m-6 8h6m4-12v12a2 2 0 01-2 2" /></svg>
                                </div>
                                <span v-if="post.is_featured" class="absolute left-3 top-3 rounded-md bg-amber-400 px-2 py-0.5 text-[11px] font-bold text-amber-900">ОНЦЛОХ</span>
                            </div>
                            <div class="flex flex-1 flex-col p-4">
                                <p class="text-xs text-gray-400">{{ timeAgo(post.published_at) }}</p>
                                <h3 class="mt-1 line-clamp-2 font-semibold text-gray-900 group-hover:text-brand-700">{{ post.title }}</h3>
                                <p v-if="post.excerpt" class="mt-1 line-clamp-2 text-sm text-gray-500">{{ post.excerpt }}</p>
                            </div>
                        </Link>
                    </div>
                </section>

                <!-- Онцлох мэргэжилтэн -->
                <section v-if="featuredProfessionals.length">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="flex items-center gap-2.5 text-xl font-bold text-gray-900">
                            <span class="h-5 w-1.5 rounded-full bg-brand-600"></span>
                            Онцлох мэргэжлийн үйлчилгээ
                        </h2>
                        <Link href="/professionals" class="group inline-flex items-center gap-1 text-sm font-medium text-brand-700">
                            Бүгд
                            <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </Link>
                    </div>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        <ProfessionalCard v-for="p in featuredProfessionals" :key="p.id" :pro="p" />
                    </div>
                </section>

                <!-- Удахгүй болох эвент -->
                <section v-if="upcomingEvents.length">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="flex items-center gap-2.5 text-xl font-bold text-gray-900">
                            <span class="h-5 w-1.5 rounded-full bg-brand-600"></span>
                            Удахгүй болох эвент
                        </h2>
                        <Link href="/events" class="group inline-flex items-center gap-1 text-sm font-medium text-brand-700">
                            Бүгд
                            <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </Link>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <Link
                            v-for="event in upcomingEvents"
                            :key="event.id"
                            :href="`/events/${event.slug}`"
                            class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-card-lg"
                        >
                            <div class="relative aspect-video overflow-hidden bg-gray-100">
                                <img v-if="event.cover_image" :src="event.cover_image" :alt="event.title" class="h-full w-full object-cover" />
                                <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-500 to-brand-700 text-white/50">
                                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <div class="absolute left-3 top-3 flex h-12 w-12 flex-col items-center justify-center rounded-xl bg-white/95 text-gray-900 shadow-sm backdrop-blur">
                                    <span class="text-base font-bold leading-none">{{ eventDay(event.starts_at) }}</span>
                                    <span class="text-[9px] uppercase text-gray-500">{{ eventMonth(event.starts_at) }}</span>
                                </div>
                                <span v-if="event.is_featured" class="absolute right-3 top-3 rounded-md bg-amber-400 px-2 py-0.5 text-[11px] font-bold text-amber-900">ОНЦЛОХ</span>
                            </div>
                            <div class="flex flex-1 flex-col p-4">
                                <h3 class="line-clamp-2 font-semibold text-gray-900 group-hover:text-brand-700">{{ event.title }}</h3>
                                <p class="mt-1 truncate text-sm text-gray-400">{{ event.venue }}<span v-if="event.city">, {{ event.city }}</span></p>
                            </div>
                        </Link>
                    </div>
                </section>

                <!-- Зар нэмэх CTA -->
                <section>
                    <div class="hero-aurora relative flex flex-col items-center justify-between gap-5 overflow-hidden rounded-[2rem] px-8 py-12 text-center shadow-card-lg ring-1 ring-white/10 sm:flex-row sm:text-left">
                        <div class="pointer-events-none absolute inset-0 bg-grid-light [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)]"></div>
                        <div class="pointer-events-none absolute -right-12 -top-12 h-48 w-48 animate-float rounded-full bg-white/15 blur-3xl"></div>
                        <div class="pointer-events-none absolute -bottom-16 left-1/4 h-48 w-48 animate-float-slow rounded-full bg-brand-300/20 blur-3xl"></div>
                        <div class="relative">
                            <h2 class="text-2xl font-bold text-white sm:text-3xl">Зараа үнэгүй нийтэлээрэй</h2>
                            <p class="mt-1.5 text-brand-100">Хэдхэн минутын дотор олон мянган монголчуудад хүргэ.</p>
                        </div>
                        <Link :href="user ? '/zar/new' : '/register'" class="relative shrink-0 rounded-full bg-white px-7 py-3 font-semibold text-gray-900 shadow-lg transition duration-200 hover:-translate-y-0.5 hover:bg-brand-50 hover:text-brand-700 active:translate-y-0">
                            + Зар нэмэх
                        </Link>
                    </div>
                </section>
            </main>
        </div>

        <!-- Хөл -->
        <footer class="border-t border-gray-100 bg-white">
            <div class="mx-auto max-w-7xl px-5 py-10">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <Logo size="sm" badge="solid" tone="dark" />
                    <div class="flex gap-6 text-sm text-gray-500">
                        <Link href="/zar" class="hover:text-gray-900">Зар</Link>
                        <Link href="/news" class="hover:text-gray-900">Мэдээ</Link>
                        <Link href="/events" class="hover:text-gray-900">Эвент</Link>
                    </div>
                </div>
                <p class="mt-8 text-center text-xs leading-relaxed text-gray-400">
                    Yazguur — хараат бус олон нийтийн платформ.<br class="sm:hidden" />
                    Европын Холбоо (EU)-той албан ёсны хамааралгүй. © 2026
                </p>
            </div>
        </footer>
    </div>
</template>
