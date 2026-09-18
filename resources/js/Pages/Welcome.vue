<script setup>
import BannerDisplay from '@/Components/BannerDisplay.vue';
import FlapText from '@/Components/Board/FlapText.vue';
import ListingCard from '@/Components/ListingCard.vue';
import ProfessionalCard from '@/Components/ProfessionalCard.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { monthLabel } from '@/lib/date';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowRight, ArrowUpRight, Car, Check, House, ImageOff, Package, PlaneLanding, PlaneTakeoff, Plus, Users } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';

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
    stats: { type: Object, default: () => ({}) },
    counts: { type: Object, default: () => ({}) },
    upcomingRides: { type: Array, default: () => [] },
    guides: { type: Array, default: () => [] },
    journey: { type: Array, default: () => [] },
    journeyDone: { type: Array, default: () => [] },
    upcomingFlights: { type: Array, default: () => [] },
});

const user = computed(() => usePage().props.auth?.user);

/*
 * Ирэх самбар — Франкфуртын нисэх буудлын ирэх заалны самбар шиг.
 * Мөр бүр бодит тоон дээр суурилсан навигаци тул хэзээ ч хоосон харагдахгүй.
 */
const directions = computed(() => [
    { gate: 'A1', label: 'Виз, гааль, Anmeldung', dest: 'Гарын авлага', href: '/guides', count: props.counts.guides, unit: 'заавар' },
    { gate: 'A2', label: 'Нислэг, угтах хүн, ачаа', dest: 'Нислэг', href: '/flights', count: props.counts.flights, unit: 'нислэг' },
    { gate: 'A3', label: 'Франкфуртаар дамжих', dest: 'Транзит', href: '/damjih', count: props.counts.transit, unit: 'хот' },
    { gate: 'A4', label: 'Байр хайх', dest: 'Орон сууц', href: '/housing', count: props.counts.housing, unit: 'зар' },
    { gate: 'A5', label: 'Ажил хайх', dest: 'Ажил', href: '/jobs', count: props.counts.jobs, unit: 'зар' },
    { gate: 'A6', label: 'Бараа худалдах, авах', dest: 'Зар', href: '/zar', count: props.counts.listings, unit: 'зар' },
    { gate: 'A7', label: 'Арга хэмжээ, уулзалт', dest: 'Эвент', href: '/events', count: props.counts.events, unit: 'эвент' },
].map((d) => ({ ...d, status: `${d.count || 0} ${d.unit}` })));

// Самбарын hover: мөр бүрийн хавтанг дахин эргүүлэх тоолуур.
const flip = reactive({});
const bump = (key) => { flip[key] = (flip[key] || 0) + 1; };

// Франкфурт, Улаанбаатарын цаг — секунд тутамд хавтан эргэнэ.
const now = ref(new Date());
let clockTimer;
onMounted(() => { clockTimer = setInterval(() => (now.value = new Date()), 1000); });
onUnmounted(() => clearInterval(clockTimer));
function clock(timeZone, seconds = false) {
    return new Intl.DateTimeFormat('en-GB', {
        timeZone, hour: '2-digit', minute: '2-digit', ...(seconds ? { second: '2-digit' } : {}), hour12: false,
    }).format(now.value);
}
const today = computed(() => new Intl.DateTimeFormat('en-GB', { timeZone: 'Europe/Berlin', day: '2-digit', month: '2-digit', year: 'numeric' })
    .format(now.value)
    .replaceAll('/', '.'));

function priceShort(l) {
    if (l.price_type === 'free') return 'үнэгүй';
    if (l.price === null || l.price === undefined) return 'тохиролцоно';
    return Number(l.price).toLocaleString('mn-MN') + ' €';
}

// Мэдэгдлийн мөр — шинэ гарын авлага, зар, эвент.
const notices = computed(() => [
    ...props.guides.slice(0, 4).map((g) => ({ tag: 'Гарын авлага', text: g.title, href: `/guides/${g.slug}` })),
    ...[...props.featuredListings, ...props.latestListings].slice(0, 5).map((l) => ({ tag: 'Зар', text: `${l.title}, ${priceShort(l)}`, href: `/zar/${l.slug}` })),
    ...[...props.featuredEvents, ...props.upcomingEvents].slice(0, 3).map((e) => ({ tag: 'Эвент', text: e.title, href: `/events/${e.slug}` })),
]);

// Аяллын зам — зөвхөн гарын авлагатай үе шатыг харуулна.
const journeyStages = computed(() => props.journey.filter((s) => s.guides.length));
const stageIcon = { before: PlaneTakeoff, arrival: PlaneLanding, first_weeks: House };

/*
 * Бэлтгэлийн жагсаалт: нэвтэрсэн хэрэглэгчийнх серверт, зочныхыг хөтчид хадгална.
 */
const JOURNEY_KEY = 'om137.journey';
const done = ref(new Set(props.journeyDone));
watch(() => props.journeyDone, (v) => { if (user.value) done.value = new Set(v); });
onMounted(() => {
    if (user.value) return;
    try { done.value = new Set(JSON.parse(localStorage.getItem(JOURNEY_KEY) || '[]')); } catch { /* хадгалах боломжгүй */ }
});
function toggleStep(slug) {
    const next = new Set(done.value);
    next.has(slug) ? next.delete(slug) : next.add(slug);
    done.value = next;
    if (user.value) {
        router.post(`/journey/${slug}`, {}, { preserveScroll: true, preserveState: true, only: ['journeyDone'] });
    } else {
        try { localStorage.setItem(JOURNEY_KEY, JSON.stringify([...next])); } catch { /* хадгалах боломжгүй */ }
    }
}
const totalSteps = computed(() => journeyStages.value.reduce((n, s) => n + s.guides.length, 0));
const doneSteps = computed(() => journeyStages.value.reduce((n, s) => n + s.guides.filter((g) => done.value.has(g.slug)).length, 0));

const listings = computed(() => [...props.featuredListings, ...props.latestListings].slice(0, 8));
const events = computed(() => [...props.featuredEvents, ...props.upcomingEvents].slice(0, 4));

// Хэсгийн дугаар — хоосон хэсэг нуугдахад дугаар үсрэхгүйн тулд харагдаж буйгаас тооцно.
const sectionNo = computed(() => {
    const visible = [
        ['journey', journeyStages.value.length > 0],
        ['flights', true],
        ['listings', listings.value.length > 0],
        ['events', events.value.length > 0],
        ['news', props.featuredNews.length > 0],
    ].filter(([, shown]) => shown);
    return Object.fromEntries(visible.map(([key], i) => [key, String(i + 1).padStart(2, '0')]));
});

function pad(n) { return String(n).padStart(2, '0'); }
function dayMonth(v) {
    const d = new Date(v);
    return `${pad(d.getDate())}.${pad(d.getMonth() + 1)}`;
}
function hm(v) {
    const d = new Date(v);
    return `${pad(d.getHours())}:${pad(d.getMinutes())}`;
}
function monthShort(v) {
    return monthLabel(v);
}
</script>

<template>
    <Head title="Нүүр" />

    <PublicLayout bleed>
        <!-- ─── HERO: ирэх заалны самбар ─────────────────────────── -->
        <section class="bg-board text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Самбарын дээд мөр -->
                <div class="flex items-center justify-between gap-4 border-b border-board-line py-4 font-mono text-[11px] uppercase tracking-[0.14em]">
                    <span class="flex items-center gap-2.5 text-white/70">
                        <PlaneLanding class="h-4 w-4 text-signal-400" />
                        Ирэх <span class="text-white/30">/ Ankunft</span>
                    </span>
                    <span class="tabular text-white/40"><span class="hidden sm:inline">FRA · Frankfurt am Main · </span>{{ today }}</span>
                </div>

                <!-- Гарчиг + цаг -->
                <div class="grid gap-10 pb-10 pt-10 md:pt-14 lg:grid-cols-12 lg:items-end">
                    <div class="lg:col-span-8">
                        <h1 class="space-y-[0.14em] text-[clamp(26px,8.2vw,72px)] leading-none" @mouseenter="bump('headline')">
                            <span class="sr-only">Франкфуртад буусан монгол хүний эхний зогсоол.</span>
                            <FlapText text="Франкфуртад" wrap :replay="flip.headline || 0" />
                            <FlapText text="тавтай морил" wrap :delay="250" :replay="flip.headline || 0" />
                        </h1>
                        <p class="mt-8 max-w-xl text-[17px] leading-relaxed text-white/60">
                            Франкфурт орчимд амьдардаг, Монголоос шинээр ирж буй, Франкфуртаар дамжин Европын бусад хот руу явах монголчуудын мэдээллийн сайт. Байр, ажил хайх, бичиг баримтаа бүрдүүлэх, хамт аялах хүн олоход тань тусална.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="#zam" class="inline-flex h-12 items-center gap-2 rounded-md bg-signal-400 px-5 text-[15px] font-semibold text-brand-600 transition-colors hover:bg-signal-300">
                                Ирэхэд бэлтгэх <ArrowRight class="h-4 w-4" />
                            </a>
                            <Link href="/rides" class="inline-flex h-12 items-center rounded-md border border-white/20 px-5 text-[15px] font-medium text-white transition-colors hover:border-white">
                                Хамт аялах хүн олох
                            </Link>
                        </div>
                    </div>

                    <!-- Цаг -->
                    <div class="flex gap-8 lg:col-span-4 lg:justify-end">
                        <div>
                            <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-white/40">Франкфурт</p>
                            <div class="mt-2 text-[26px]"><FlapText :text="clock('Europe/Berlin', true)" /></div>
                        </div>
                        <div>
                            <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-white/40">Улаанбаатар</p>
                            <div class="mt-2 text-[26px]"><FlapText :text="clock('Asia/Ulaanbaatar')" /></div>
                        </div>
                    </div>
                </div>

                <!-- Самбарын баганууд — монгол / герман (FRA-ийн самбар шиг). -->
                <div class="hidden grid-cols-[5rem_24rem_minmax(0,1fr)_10rem_1.5rem] items-center gap-6 border-y border-board-line py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-white/40 md:grid">
                    <span class="whitespace-nowrap">Хаалга <span class="text-white/25">/ Gate</span></span>
                    <span>Чиглэл <span class="text-white/25">/ Ziel</span></span>
                    <span>Хэсэг</span>
                    <span>Төлөв <span class="text-white/25">/ Bemerkung</span></span>
                    <span />
                </div>
                <ul class="border-t border-board-line md:border-t-0">
                    <li v-for="(d, i) in directions" :key="d.href" class="border-b border-board-line">
                        <Link
                            :href="d.href"
                            @mouseenter="bump(d.href)"
                            @focus="bump(d.href)"
                            class="group grid grid-cols-[2.5rem_minmax(0,1fr)_auto] items-center gap-4 py-4 transition-colors hover:bg-board-soft md:grid-cols-[5rem_24rem_minmax(0,1fr)_10rem_1.5rem] md:gap-6 md:py-3"
                        >
                            <!-- Хаалга -->
                            <span class="text-[15px] md:text-[18px]"><FlapText :text="d.gate" :delay="500 + i * 90" :cycles="6" :replay="flip[d.href] || 0" /></span>
                            <!-- Чиглэл: том дэлгэцэнд хавтан, утсанд энгийн текст -->
                            <span class="hidden overflow-hidden text-[18px] md:block"><FlapText :text="d.label" :pad="24" :delay="500 + i * 90" :replay="flip[d.href] || 0" /></span>
                            <span class="truncate font-mono text-[14px] uppercase text-signal-400 md:hidden">{{ d.label }}</span>
                            <span class="hidden font-mono text-[11px] uppercase tracking-[0.1em] text-white/40 md:block">{{ d.dest }}</span>
                            <!-- Төлөв -->
                            <span class="hidden text-[18px] md:block" :class="d.count ? '' : 'opacity-40'"><FlapText :text="d.status" :pad="10" :delay="700 + i * 90" :replay="flip[d.href] || 0" /></span>
                            <span class="tabular font-mono text-[12px] uppercase md:hidden" :class="d.count ? 'text-white' : 'text-white/30'">{{ d.status }}</span>
                            <ArrowRight class="hidden h-4 w-4 text-white/30 transition-all group-hover:translate-x-0.5 group-hover:text-signal-400 md:block" />
                        </Link>
                    </li>
                </ul>
            </div>

            <!-- Мэдэгдлийн гүйдэг мөр -->
            <div v-if="notices.length" class="flex items-stretch border-t border-board-line">
                <span class="flex shrink-0 items-center bg-signal-400 px-4 font-mono text-[11px] font-semibold uppercase tracking-[0.14em] text-brand-600">Мэдэгдэл</span>
                <div class="relative min-w-0 flex-1 overflow-hidden">
                    <div class="flex w-max animate-marquee hover:[animation-play-state:paused]">
                        <template v-for="copy in 2" :key="copy">
                            <Link
                                v-for="(n, i) in notices"
                                :key="`${copy}-${i}`"
                                :href="n.href"
                                :tabindex="copy === 2 ? -1 : 0"
                                :aria-hidden="copy === 2"
                                class="flex items-center gap-3 whitespace-nowrap px-6 py-3.5 text-sm text-white/70 transition-colors hover:text-white"
                            >
                                <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-signal-400">{{ n.tag }}</span>
                                {{ n.text }}
                                <span class="text-white/20">•</span>
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <!-- ─── АЯЛЛЫН ЗАМ: нисэхээс өмнө, буух өдөр, эхний 14 хоног ─ -->
        <section v-if="journeyStages.length" id="zam" class="scroll-mt-20 border-t border-brand-100">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 md:py-20 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <p class="kicker">{{ sectionNo.journey }} · Аяллын зам</p>
                        <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Монголоос Франкфурт хүртэл</h2>
                        <p class="mt-4 text-[15px] leading-relaxed text-brand-500">
                            Нислэгийн өмнө бэлдэх зүйлээс эхлээд ирснийхээ дараах эхний 14 хоногт хийх ажил хүртэл, дарааллаар нь.
                        </p>
                    </div>
                    <div class="w-full shrink-0 sm:w-64">
                        <div class="flex items-baseline justify-between text-sm">
                            <span class="text-brand-500">Таны бэлтгэл</span>
                            <span class="tabular font-mono text-brand-600">{{ doneSteps }} / {{ totalSteps }}</span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-brand-100">
                            <div class="h-full rounded-full bg-signal-400 transition-[width] duration-500" :style="{ width: `${totalSteps ? (doneSteps / totalSteps) * 100 : 0}%` }" />
                        </div>
                        <p class="mt-2 text-xs text-brand-400">Хийсэн алхмаа чагтлаад явцаа хянаарай.</p>
                        <Link href="/guides" class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-brand-500 transition-colors hover:text-brand-600">Бүх гарын авлага <ArrowRight class="h-4 w-4" /></Link>
                    </div>
                </div>

                <ol class="mt-12 grid gap-12 md:grid-cols-3 md:gap-8">
                    <li v-for="(stage, si) in journeyStages" :key="stage.key">
                        <!-- Цаг хугацааны шугам: цэг ба дараагийн үе шат руу үргэлжлэх зураас -->
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-board text-signal-400">
                                <component :is="stageIcon[stage.key]" class="h-4 w-4" />
                            </span>
                            <span class="h-px flex-1 bg-brand-200" :class="si === journeyStages.length - 1 ? 'md:bg-transparent' : ''" />
                        </div>
                        <p class="kicker mt-5">Үе шат {{ si + 1 }}</p>
                        <h3 class="mt-2 text-xl font-semibold tracking-tight text-brand-600">{{ stage.label }}</h3>

                        <ul class="mt-5 border-t border-brand-100">
                            <li v-for="(g, gi) in stage.guides" :key="g.slug" class="flex gap-3 border-b border-brand-100">
                                <button
                                    type="button"
                                    class="mt-4 flex h-5 w-5 shrink-0 items-center justify-center rounded-[3px] border transition-colors"
                                    :class="done.has(g.slug) ? 'border-brand-600 bg-brand-600 text-signal-400' : 'border-brand-300 hover:border-brand-600'"
                                    :aria-pressed="done.has(g.slug)"
                                    :aria-label="done.has(g.slug) ? 'Хийгээгүй болгох' : 'Хийсэн гэж тэмдэглэх'"
                                    @click="toggleStep(g.slug)"
                                >
                                    <Check v-if="done.has(g.slug)" class="h-3.5 w-3.5" stroke-width="3" />
                                </button>
                                <Link :href="`/guides/${g.slug}`" class="group flex min-w-0 flex-1 gap-4 py-4">
                                    <span class="tabular pt-0.5 font-mono text-xs text-brand-300">{{ si + 1 }}.{{ gi + 1 }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex items-start justify-between gap-3">
                                            <span class="text-[15px] font-medium leading-snug group-hover:underline group-hover:underline-offset-4" :class="done.has(g.slug) ? 'text-brand-400' : 'text-brand-600'">{{ g.title }}</span>
                                            <ArrowUpRight class="mt-0.5 h-4 w-4 shrink-0 text-brand-300 transition-colors group-hover:text-brand-600" />
                                        </span>
                                        <span v-if="g.excerpt" class="mt-1.5 line-clamp-2 block text-sm leading-relaxed text-brand-400">{{ g.excerpt }}</span>
                                    </span>
                                </Link>
                            </li>
                        </ul>
                    </li>
                </ol>
            </div>
        </section>

        <!-- ─── НИСЛЭГИЙН САМБАР ─────────────────────────────────── -->
        <section class="border-t border-brand-100">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 md:py-20 lg:px-8">
                <div class="flex items-end justify-between gap-6">
                    <div>
                        <p class="kicker">{{ sectionNo.flights }} · Нислэг / Flüge</p>
                        <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Ойрын нислэгүүд</h2>
                    </div>
                    <Link href="/flights" class="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-brand-500 transition-colors hover:text-brand-600">
                        Нислэгийн самбар <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>

                <div class="mt-8 overflow-hidden rounded-md bg-board text-white">
                    <div class="hidden grid-cols-[7rem_5rem_minmax(0,1fr)_minmax(0,1fr)_1rem] gap-4 border-b border-board-line px-5 py-3 font-mono text-[10px] uppercase tracking-[0.14em] text-white/40 sm:grid">
                        <span>Огноо <span class="text-white/25">/ Datum</span></span>
                        <span>Нислэг</span>
                        <span>Чиглэл <span class="text-white/25">/ Route</span></span>
                        <span>Хүн · машин · ачаа</span>
                        <span />
                    </div>

                    <template v-if="upcomingFlights.length">
                        <Link
                            v-for="f in upcomingFlights"
                            :key="f.slug"
                            :href="`/flights/${f.slug}`"
                            class="group grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-1 border-b border-board-line px-5 py-4 transition-colors last:border-b-0 hover:bg-board-soft sm:grid-cols-[7rem_5rem_minmax(0,1fr)_minmax(0,1fr)_1rem]"
                        >
                            <span class="tabular font-mono text-[13px] text-white/60">
                                {{ f.weekday }} {{ f.date.slice(8, 10) }}.{{ f.date.slice(5, 7) }}
                                <span class="text-signal-400" :class="f.status === 'cancelled' ? 'line-through opacity-50' : ''">{{ f.time }}</span>
                            </span>
                            <span class="font-mono text-[15px] font-medium sm:order-none">{{ f.code }}</span>
                            <span class="col-span-2 flex min-w-0 items-center gap-2 text-[15px] sm:col-span-1">
                                <component :is="f.direction === 'arrival' ? PlaneLanding : PlaneTakeoff" class="h-4 w-4 shrink-0 text-signal-400" />
                                <span class="truncate">{{ f.origin }} → {{ f.destination }}</span>
                                <span v-if="f.status !== 'scheduled'" class="font-mono text-[11px] uppercase tracking-wider" :class="f.status === 'cancelled' ? 'text-red-400' : 'text-signal-300'">{{ f.status_label }}</span>
                            </span>
                            <span class="tabular col-span-2 flex items-center gap-4 font-mono text-[12px] text-white/45 sm:col-span-1">
                                <span class="inline-flex items-center gap-1" :class="f.passengers_count ? 'text-white' : ''"><Users class="h-3.5 w-3.5" />{{ f.passengers_count }}</span>
                                <span class="inline-flex items-center gap-1" :class="f.rides_count ? 'text-white' : ''"><Car class="h-3.5 w-3.5" />{{ f.rides_count }}</span>
                                <span class="inline-flex items-center gap-1" :class="f.parcels_count ? 'text-white' : ''"><Package class="h-3.5 w-3.5" />{{ f.parcels_count }}</span>
                            </span>
                            <ArrowRight class="hidden h-4 w-4 text-white/25 transition-colors group-hover:text-signal-400 sm:block" />
                        </Link>
                    </template>
                    <p v-else class="px-5 py-12 text-sm text-white/55">Нислэгийн хуваарь оруулаагүй байна.</p>
                </div>

                <p class="mt-5 text-sm text-brand-500">
                    Нислэгээ олоод "Би энэ нислэгээр ирнэ" гэж тэмдэглэвэл тантай нэг нислэгээр ирэх хүмүүс, угтах машин, ачаа авч явах хүн нэг дор харагдана.
                </p>
            </div>
        </section>

        <!-- Сурталчилгааны байршил -->
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <BannerDisplay placement="home_top" variant="leaderboard" :placeholder="true" />
        </div>

        <!-- ─── 03 · ШИНЭ ЗАР ────────────────────────────────────── -->
        <section v-if="listings.length" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 md:py-20 lg:px-8">
            <div class="flex items-end justify-between gap-6">
                <div>
                    <p class="kicker">{{ sectionNo.listings }} · Зар</p>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Шинэ зар</h2>
                </div>
                <Link href="/zar" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-500 transition-colors hover:text-brand-600">
                    Бүгдийг харах <ArrowRight class="h-4 w-4" />
                </Link>
            </div>
            <div class="mt-8 grid gap-10 lg:grid-cols-12">
                <div class="grid grid-cols-2 gap-x-5 gap-y-10 sm:grid-cols-3 lg:col-span-9 lg:grid-cols-4">
                    <ListingCard v-for="l in listings" :key="l.id" :listing="l" />
                </div>
                <aside class="lg:col-span-3">
                    <BannerDisplay placement="home_sidebar" variant="box" :placeholder="true" />
                </aside>
            </div>
        </section>

        <!-- ─── 04 · ЭВЕНТ ───────────────────────────────────────── -->
        <section v-if="events.length" class="border-t border-brand-100">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 md:py-20 lg:px-8">
                <div class="flex items-end justify-between gap-6">
                    <div>
                        <p class="kicker">{{ sectionNo.events }} · Эвент</p>
                        <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Удахгүй болох арга хэмжээ</h2>
                    </div>
                    <Link href="/events" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-500 transition-colors hover:text-brand-600">
                        Бүгдийг харах <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
                <ul class="mt-8 border-t border-brand-100">
                    <li v-for="e in events" :key="e.id" class="border-b border-brand-100">
                        <Link :href="`/events/${e.slug}`" class="group grid grid-cols-12 items-center gap-4 py-5">
                            <span class="col-span-3 sm:col-span-2">
                                <span class="tabular block font-mono text-3xl font-medium leading-none text-brand-600">{{ String(new Date(e.starts_at).getDate()).padStart(2, '0') }}</span>
                                <span class="kicker mt-1.5 block">{{ monthShort(e.starts_at) }} · {{ hm(e.starts_at) }}</span>
                            </span>
                            <span class="col-span-9 min-w-0 sm:col-span-7">
                                <span class="block truncate text-[16px] font-medium text-brand-600 group-hover:underline group-hover:underline-offset-4">{{ e.title }}</span>
                                <span class="mt-1 block truncate text-sm text-brand-400">{{ [e.venue, e.city].filter(Boolean).join(' · ') }}</span>
                            </span>
                            <span class="col-span-3 hidden justify-end sm:flex">
                                <ArrowUpRight class="h-5 w-5 text-brand-300 transition-colors group-hover:text-brand-600" />
                            </span>
                        </Link>
                    </li>
                </ul>
            </div>
        </section>

        <!-- ─── 05 · МЭДЭЭ ───────────────────────────────────────── -->
        <section v-if="featuredNews.length" class="border-t border-brand-100">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 md:py-20 lg:px-8">
                <div class="flex items-end justify-between gap-6">
                    <div>
                        <p class="kicker">{{ sectionNo.news }} · Мэдээ</p>
                        <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Сүүлийн мэдээ</h2>
                    </div>
                    <Link href="/news" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-500 transition-colors hover:text-brand-600">
                        Бүгдийг харах <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
                <div class="mt-8 grid gap-10 md:grid-cols-3">
                    <Link v-for="n in featuredNews" :key="n.id" :href="`/news/${n.slug}`" class="group block">
                        <div class="aspect-[16/10] overflow-hidden rounded-[3px] bg-brand-50">
                            <img v-if="n.cover_image" :src="n.cover_image" :alt="n.title" class="h-full w-full object-cover transition-opacity group-hover:opacity-90" />
                            <div v-else class="flex h-full w-full items-center justify-center text-brand-300">
                                <ImageOff class="h-8 w-8" stroke-width="1.25" />
                            </div>
                        </div>
                        <p class="kicker mt-4">{{ dayMonth(n.published_at) }}.{{ new Date(n.published_at).getFullYear() }}</p>
                        <h3 class="mt-2 text-[17px] font-medium leading-snug text-brand-600 group-hover:underline group-hover:underline-offset-4">{{ n.title }}</h3>
                        <p v-if="n.excerpt" class="mt-2 line-clamp-2 text-sm leading-relaxed text-brand-400">{{ n.excerpt }}</p>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ─── МЭРГЭЖЛИЙН ТУСЛАХ ────────────────────────────────── -->
        <section v-if="featuredProfessionals.length" class="border-t border-brand-100">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 md:py-20 lg:px-8">
                <div class="flex items-end justify-between gap-6">
                    <div>
                        <p class="kicker">Мэргэжлийн туслах</p>
                        <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Монголоор ярьдаг мэргэжилтэн</h2>
                    </div>
                    <Link href="/professionals" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-500 transition-colors hover:text-brand-600">
                        Бүгдийг харах <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <ProfessionalCard v-for="p in featuredProfessionals" :key="p.id" :pro="p" />
                </div>
            </div>
        </section>

        <!-- ─── НЭГДЭХ ──────────────────────────────────────────── -->
        <section class="bg-board text-white">
            <div class="mx-auto flex max-w-7xl flex-col items-start gap-8 px-4 py-16 sm:px-6 md:flex-row md:items-center md:justify-between md:py-20 lg:px-8">
                <div>
                    <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-signal-400">
                        {{ user ? 'Нийтлэх' : 'Бүртгэл' }}
                    </p>
                    <h2 class="mt-3 max-w-xl text-2xl font-semibold leading-tight tracking-tight sm:text-3xl">
                        {{ user ? 'Зар, аяллаа нийтэлж бусадтай хуваалцаарай.' : 'Бүртгүүлээд зар тавьж, аялал нэмж, бусадтай шууд бичилцээрэй.' }}
                    </h2>
                </div>
                <div class="flex flex-wrap gap-3">
                    <template v-if="user">
                        <Link href="/rides/new" class="inline-flex h-12 items-center gap-1.5 rounded-md bg-signal-400 px-5 text-[15px] font-semibold text-brand-600 transition-colors hover:bg-signal-300">
                            <Plus class="h-4 w-4" /> Аялал нэмэх
                        </Link>
                        <Link href="/zar/new" class="inline-flex h-12 items-center rounded-md border border-white/20 px-5 text-[15px] font-medium text-white transition-colors hover:border-white">
                            Зар нэмэх
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/register" class="inline-flex h-12 items-center gap-2 rounded-md bg-signal-400 px-5 text-[15px] font-semibold text-brand-600 transition-colors hover:bg-signal-300">
                            Бүртгүүлэх <ArrowRight class="h-4 w-4" />
                        </Link>
                        <Link href="/login" class="inline-flex h-12 items-center rounded-md border border-white/20 px-5 text-[15px] font-medium text-white transition-colors hover:border-white">
                            Нэвтрэх
                        </Link>
                    </template>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
