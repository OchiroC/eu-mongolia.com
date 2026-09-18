<script setup>
import FlapText from '@/Components/Board/FlapText.vue';
import CustomsNotice from '@/Components/CustomsNotice.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDateTime } from '@/lib/date';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, ArrowUpRight, Car, Check, Package, PlaneLanding, PlaneTakeoff, Plus, UserRound, Users, WifiOff } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    flight: { type: Object, required: true },
    onBoard: { type: Boolean, default: false },
    passengers: { type: Array, default: () => [] },
    rides: { type: Array, default: () => [] },
    parcels: { type: Array, default: () => [] },
    arrivalGuide: { type: Object, default: null },
});

const user = computed(() => usePage().props.auth?.user);
const arrival = computed(() => props.flight.direction === 'arrival');
const cancelled = computed(() => props.flight.status === 'cancelled');
const [y, m, d] = props.flight.date.split('-');
const dateLabel = `${d}.${m}.${y}`;

function toggleBoard() {
    if (!user.value) {
        router.visit('/login');
        return;
    }
    router.post(`/flights/${props.flight.slug}/board`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="`${flight.code} ${dateLabel}`" />

    <PublicLayout bleed>
        <!-- Нислэгийн самбар -->
        <section class="bg-board text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4 border-b border-board-line py-4 font-mono text-[11px] uppercase tracking-[0.14em]">
                    <span class="flex items-center gap-2.5 text-white/70">
                        <component :is="arrival ? PlaneLanding : PlaneTakeoff" class="h-4 w-4 text-signal-400" />
                        {{ arrival ? 'Ирэх' : 'Явах' }} <span class="text-white/30">/ {{ arrival ? 'Ankunft' : 'Abflug' }}</span>
                    </span>
                    <Link href="/flights" class="inline-flex items-center gap-1.5 text-white/50 transition-colors hover:text-white">
                        <ArrowLeft class="h-3.5 w-3.5" /> Нислэгийн самбар
                    </Link>
                </div>

                <div class="grid gap-8 py-10 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-white/40">Нислэг</p>
                        <div class="mt-3 text-[clamp(26px,4vw,40px)]"><FlapText :text="flight.code" /></div>
                    </div>
                    <div>
                        <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-white/40">Чиглэл</p>
                        <div class="mt-3 text-[clamp(26px,4vw,40px)]"><FlapText :text="`${flight.origin}>${flight.destination}`" :delay="150" /></div>
                    </div>
                    <div>
                        <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-white/40">Огноо · {{ flight.weekday }}</p>
                        <div class="mt-3 text-[clamp(26px,4vw,40px)]"><FlapText :text="`${d}.${m}`" :delay="300" /></div>
                    </div>
                    <div>
                        <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-white/40">{{ arrival ? 'Буух цаг' : 'Хөөрөх цаг' }} · Франкфурт</p>
                        <div class="mt-3 text-[clamp(26px,4vw,40px)]" :class="cancelled ? 'opacity-40' : ''"><FlapText :text="flight.time" :delay="450" /></div>
                    </div>
                </div>

                <div v-if="flight.status !== 'scheduled' || flight.note" class="mb-8 flex flex-wrap items-center gap-3 border border-board-line px-4 py-3 text-sm">
                    <span class="font-mono text-[11px] uppercase tracking-[0.14em]" :class="cancelled ? 'text-red-400' : 'text-signal-300'">{{ flight.status_label }}</span>
                    <span v-if="flight.note" class="text-white/70">{{ flight.note }}</span>
                </div>

                <div class="flex flex-col gap-6 border-t border-board-line py-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="tabular flex flex-wrap gap-6 font-mono text-sm text-white/60">
                        <span class="inline-flex items-center gap-2"><Users class="h-4 w-4 text-signal-400" /><b class="font-medium text-white">{{ flight.passengers_count ?? passengers.length }}</b> хүн {{ arrival ? 'ирнэ' : 'явна' }}</span>
                        <span class="inline-flex items-center gap-2"><Car class="h-4 w-4 text-signal-400" /><b class="font-medium text-white">{{ rides.length }}</b> машин</span>
                        <span class="inline-flex items-center gap-2"><Package class="h-4 w-4 text-signal-400" /><b class="font-medium text-white">{{ parcels.length }}</b> ачаа</span>
                    </div>
                    <div v-if="!cancelled" class="flex flex-wrap gap-3">
                        <button
                            v-if="!onBoard"
                            type="button"
                            class="inline-flex h-11 items-center gap-2 rounded-md bg-signal-400 px-5 text-sm font-semibold text-brand-600 transition-colors hover:bg-signal-300"
                            @click="toggleBoard"
                        >
                            <Plus class="h-4 w-4" /> Би энэ нислэгээр {{ arrival ? 'ирнэ' : 'явна' }}
                        </button>
                        <template v-else>
                            <span class="inline-flex h-11 items-center gap-2 rounded-md border border-signal-400/40 px-4 text-sm text-signal-300">
                                <Check class="h-4 w-4" /> Та жагсаалтад байна
                            </span>
                            <button type="button" class="inline-flex h-11 items-center rounded-md border border-white/20 px-4 text-sm text-white/80 transition-colors hover:border-white" @click="toggleBoard">
                                Хасах
                            </button>
                        </template>
                    </div>
                </div>
                <p class="border-t border-board-line py-4 text-xs text-white/40">
                    Энэ нь МИАТ-ын албан ёсны хуудас биш. Нислэгийн цаг, төлөвийг тасалбар эсвэл МИАТ-ын вэбсайтаас шалгаарай.
                </p>
            </div>
        </section>

        <div class="mx-auto grid max-w-7xl gap-12 px-4 py-14 sm:px-6 lg:grid-cols-12 lg:px-8">
            <div class="space-y-14 lg:col-span-8">
                <!-- Хамт явах -->
                <section>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="kicker">Хамт явах</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight">{{ arrival ? 'Нисэх буудлаас хот руу' : 'Нисэх буудал руу' }}</h2>
                        </div>
                        <Link :href="`/rides/new?flight=${flight.slug}`" class="inline-flex h-10 items-center gap-1.5 rounded-md bg-brand-600 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-500">
                            <Plus class="h-4 w-4" /> Машинаа санал болгох
                        </Link>
                    </div>
                    <ul v-if="rides.length" class="mt-6 border-t border-brand-100">
                        <li v-for="r in rides" :key="r.id" class="border-b border-brand-100">
                            <Link :href="`/rides/${r.id}`" class="group flex items-center gap-4 py-4">
                                <Car class="h-5 w-5 shrink-0 text-brand-400" />
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-medium text-brand-600 group-hover:underline group-hover:underline-offset-4">{{ r.from_city }} → {{ r.to_city }}</span>
                                    <span class="tabular mt-0.5 block font-mono text-xs text-brand-400">{{ formatDateTime(r.depart_at) }} · {{ r.seats }} суудал · {{ r.user }}</span>
                                </span>
                                <span v-if="r.price" class="tabular font-mono text-sm text-brand-600">{{ r.price }}</span>
                                <ArrowUpRight class="h-4 w-4 text-brand-300 group-hover:text-brand-600" />
                            </Link>
                        </li>
                    </ul>
                    <p v-else class="mt-6 border border-brand-100 px-5 py-8 text-sm text-brand-400">
                        Энэ нислэгт одоогоор машин санал болгосон хүн алга. {{ arrival ? 'Нисэх буудлаас хот руу' : 'Нисэх буудал руу' }} машинаар явах бол эхэлж нийтлээрэй.
                    </p>
                </section>

                <!-- Ачаа -->
                <section>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="kicker">Ачаа, илгээмж</p>
                            <h2 class="mt-2 text-2xl font-semibold tracking-tight">Энэ нислэгийн ачаа</h2>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Link :href="`/achaa/new?flight=${flight.slug}&type=offer`" class="inline-flex h-10 items-center rounded-md border border-brand-200 px-4 text-sm font-medium text-brand-600 transition-colors hover:border-brand-600">Сул зай байна</Link>
                            <Link :href="`/achaa/new?flight=${flight.slug}&type=request`" class="inline-flex h-10 items-center rounded-md border border-brand-200 px-4 text-sm font-medium text-brand-600 transition-colors hover:border-brand-600">Ачаа явуулна</Link>
                        </div>
                    </div>
                    <div class="mt-6"><CustomsNotice compact /></div>
                    <ul v-if="parcels.length" class="mt-4 border-t border-brand-100">
                        <li v-for="p in parcels" :key="p.id" class="border-b border-brand-100">
                            <Link :href="`/achaa/${p.id}`" class="group flex items-start gap-4 py-4">
                                <Package class="mt-0.5 h-5 w-5 shrink-0 text-brand-400" />
                                <span class="min-w-0 flex-1">
                                    <span class="kicker block">{{ p.type_label }}</span>
                                    <span class="mt-1 block font-medium text-brand-600 group-hover:underline group-hover:underline-offset-4">{{ p.from_city }} → {{ p.to_city }}<template v-if="p.weight_kg"> · {{ p.weight_kg }} кг</template></span>
                                    <span class="mt-1 line-clamp-2 block text-sm text-brand-400">{{ p.excerpt }}</span>
                                </span>
                                <span v-if="p.price" class="tabular font-mono text-sm text-brand-600">{{ p.price }}</span>
                            </Link>
                        </li>
                    </ul>
                    <p v-else class="mt-4 border border-brand-100 px-5 py-8 text-sm text-brand-400">Энэ нислэгт ачааны зар алга.</p>
                </section>
            </div>

            <aside class="space-y-6 lg:col-span-4">
                <div v-if="arrival" class="border border-brand-100 p-5">
                    <p class="kicker">Буух өдөр</p>
                    <ul class="mt-3 space-y-3 text-sm">
                        <li v-if="arrivalGuide">
                            <Link :href="`/guides/${arrivalGuide.slug}`" class="group flex items-start justify-between gap-3 font-medium text-brand-600">
                                <span class="group-hover:underline group-hover:underline-offset-4">{{ arrivalGuide.title }}</span>
                                <ArrowRight class="mt-0.5 h-4 w-4 shrink-0 text-brand-300" />
                            </Link>
                        </li>
                        <li>
                            <a href="/ireh" class="group flex items-start justify-between gap-3 font-medium text-brand-600">
                                <span class="group-hover:underline group-hover:underline-offset-4">Ирэх өдрийн багц</span>
                                <WifiOff class="mt-0.5 h-4 w-4 shrink-0 text-brand-300" />
                            </a>
                            <p class="mt-1 text-brand-400">Нэг удаа нээхэд утсанд хадгалагдаж, интернэтгүй үед ч нээгдэнэ.</p>
                        </li>
                    </ul>
                </div>

                <div class="border border-brand-100 p-5">
                    <p class="kicker">Энэ нислэгээр {{ arrival ? 'ирэх' : 'явах' }}</p>
                    <ul v-if="passengers.length" class="mt-3 space-y-2">
                        <li v-for="p in passengers" :key="p.id" class="flex items-center gap-2.5 text-sm text-brand-600">
                            <UserRound class="h-4 w-4 text-brand-300" /> {{ p.name }}
                        </li>
                    </ul>
                    <p v-else-if="user" class="mt-3 text-sm text-brand-400">Одоогоор хэн ч тэмдэглээгүй байна.</p>
                    <p v-else class="mt-3 text-sm text-brand-400">
                        Нэрсийг харахын тулд <Link href="/login" class="font-medium text-brand-600 underline underline-offset-2">нэвтэрнэ үү</Link>.
                    </p>
                </div>
            </aside>
        </div>
    </PublicLayout>
</template>
