<script setup>
import FlapText from '@/Components/Board/FlapText.vue';
import CustomsNotice from '@/Components/CustomsNotice.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDateTime } from '@/lib/date';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, ArrowUpRight, Car, Plane, Repeat, TrainFront, UserRound, Users, WifiOff } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    city: { type: Object, required: true },
    flights: { type: Array, default: () => [] },
    rides: { type: Array, default: () => [] },
});

const user = computed(() => usePage().props.auth?.user);
const train = computed(() => props.city.mode === 'train');

// Буухаас эцсийн цэг хүртэлх алхам.
const steps = computed(() => {
    const c = props.city;
    const out = [
        c.schengen
            ? { title: '3-р терминалд буух', body: 'Франкфуртад Шенгений бүсэд орно. Паспортын хяналт, ачаа авах, гаалийг дараалан дамжина. ' + c.gen + ' Шенгений визээ бэлэн байлгаарай.' }
            : { title: '3-р терминалд буух', body: 'Нэг тасалбартай бол паспортын хяналт руу орохгүй, Transfer гэсэн тэмдгийг дагана. Тусдаа тасалбартай бол Шенгений виз хэрэгтэй болно.' },
    ];
    if (train.value) {
        out.push({ title: '1-р терминал руу', body: 'Sky Line галт тэргээр 1-р терминал руу үнэгүй очно. Холын галт тэрэгний буудал (Fernbahnhof) терминалын дэргэд байдаг.' });
        out.push({ title: c.to, body: c.how + ' Тасалбарыг DB Navigator аппаас эсвэл буудлын автомат машинаас авна.' });
    } else {
        out.push({ title: 'Дараагийн нислэг', body: 'Дараагийн нислэгийнхээ терминал, хаалгыг тасалбар болон самбараас шалгаад Transfer тэмдгийг дагана. Терминал солих бол дор хаяж 2 цагийн зай байлгаарай.' });
        out.push({ title: c.to, body: c.how });
    }
    if (c.note) out.push({ title: 'Анхаарах', body: c.note });
    return out;
});

function dayMonth(date) {
    const [, m, d] = date.split('-');
    return `${d}.${m}`;
}
</script>

<template>
    <Head :title="`Франкфуртаас ${city.to}`" />

    <PublicLayout bleed>
        <section class="bg-board text-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4 border-b border-board-line py-4 font-mono text-[11px] uppercase tracking-[0.14em]">
                    <span class="flex items-center gap-2.5 text-white/70">
                        <Repeat class="h-4 w-4 text-signal-400" />
                        Дамжих <span class="text-white/30">/ Umsteigen</span>
                    </span>
                    <Link href="/damjih" class="inline-flex items-center gap-1.5 text-white/50 transition-colors hover:text-white">
                        <ArrowLeft class="h-3.5 w-3.5" /> Бүх чиглэл
                    </Link>
                </div>

                <div class="grid gap-8 py-10 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="sm:col-span-2">
                        <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-white/40">FRA > {{ city.local }}</p>
                        <div class="mt-3 text-[clamp(26px,4vw,40px)]"><FlapText :text="city.name" /></div>
                    </div>
                    <div>
                        <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-white/40">Хэрхэн</p>
                        <p class="mt-3 flex items-center gap-2.5 text-xl font-medium">
                            <component :is="train ? TrainFront : Plane" class="h-5 w-5 text-signal-400" /> {{ city.mode_label }}
                        </p>
                        <p class="tabular mt-1 font-mono text-sm text-white/60">{{ city.time }}</p>
                    </div>
                    <div>
                        <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-white/40">Виз</p>
                        <p class="mt-3 text-xl font-medium">{{ city.schengen ? 'Шенгений виз' : city.gen + ' виз' }}</p>
                        <p class="mt-1 text-sm text-white/60">{{ city.schengen ? 'Франкфуртад Шенгенд орно' : 'Шенгенээс гадуур' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="mx-auto grid max-w-7xl gap-12 px-4 py-14 sm:px-6 lg:grid-cols-12 lg:px-8">
            <div class="space-y-14 lg:col-span-8">
                <section>
                    <p class="kicker">Замын дараалал</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight">Франкфуртаас {{ city.name }} хүртэл</h2>
                    <ol class="mt-6 border-t border-brand-100">
                        <li v-for="(s, i) in steps" :key="i" class="grid grid-cols-[2.5rem_minmax(0,1fr)] gap-4 border-b border-brand-100 py-5">
                            <span class="tabular font-mono text-sm text-brand-300">{{ String(i + 1).padStart(2, '0') }}</span>
                            <div>
                                <h3 class="font-medium text-brand-600">{{ s.title }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-brand-500">{{ s.body }}</p>
                            </div>
                        </li>
                    </ol>
                    <p class="mt-4 text-xs text-brand-400">Хугацаа ойролцоо. Хуваарь, виз, транзитын шаардлагыг албан ёсны эх сурвалжаас шалгаарай.</p>
                </section>

                <section>
                    <p class="kicker">Хамт явах</p>
                    <h2 class="mt-2 text-2xl font-semibold tracking-tight">{{ city.to }} явах машин</h2>
                    <ul v-if="rides.length" class="mt-6 border-t border-brand-100">
                        <li v-for="r in rides" :key="r.id" class="border-b border-brand-100">
                            <Link :href="`/rides/${r.id}`" class="group flex items-center gap-4 py-4">
                                <Car class="h-5 w-5 shrink-0 text-brand-400" />
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-medium text-brand-600 group-hover:underline group-hover:underline-offset-4">{{ r.from_city }} → {{ r.to_city }}</span>
                                    <span class="tabular mt-0.5 block font-mono text-xs text-brand-400">{{ formatDateTime(r.depart_at) }} · {{ r.seats }} суудал · {{ r.user }}</span>
                                </span>
                                <ArrowUpRight class="h-4 w-4 text-brand-300 group-hover:text-brand-600" />
                            </Link>
                        </li>
                    </ul>
                    <p v-else class="mt-6 border border-brand-100 px-5 py-8 text-sm text-brand-400">
                        {{ city.to }} явах машин одоогоор алга.
                        <Link href="/rides/new" class="font-medium text-brand-600 underline underline-offset-2">Машинаа санал болгох</Link>
                    </p>
                </section>

                <CustomsNotice compact />
            </div>

            <aside class="space-y-6 lg:col-span-4">
                <div class="overflow-hidden rounded-md bg-board text-white">
                    <div class="border-b border-board-line px-5 py-4">
                        <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-white/70">Ойрын нислэг · {{ city.to }}</p>
                    </div>
                    <ul v-if="flights.length">
                        <li v-for="f in flights" :key="f.id" class="border-b border-board-line last:border-b-0">
                            <Link :href="`/flights/${f.slug}`" class="group block px-5 py-3.5 transition-colors hover:bg-board-soft">
                                <span class="flex items-center gap-3">
                                    <span class="tabular font-mono text-[13px] text-white/60">{{ f.weekday }} {{ dayMonth(f.date) }}</span>
                                    <span class="tabular font-mono text-[14px] text-signal-400">{{ f.time }}</span>
                                    <span class="font-mono text-[13px]">{{ f.code }}</span>
                                    <span class="tabular ml-auto inline-flex items-center gap-1.5 font-mono text-[12px]" :class="f.travellers ? 'text-white' : 'text-white/25'">
                                        <Users class="h-3.5 w-3.5" /> {{ f.travellers }}
                                    </span>
                                </span>
                                <span v-if="f.names.length" class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-white/60">
                                    <span v-for="n in f.names" :key="n" class="inline-flex items-center gap-1"><UserRound class="h-3 w-3" /> {{ n }}</span>
                                </span>
                            </Link>
                        </li>
                    </ul>
                    <p v-else class="px-5 py-6 text-sm text-white/50">Ойрын хугацаанд нислэг алга.</p>
                    <p class="border-t border-board-line px-5 py-4 text-xs leading-relaxed text-white/50">
                        Нислэгээ нээгээд "Би энэ нислэгээр ирнэ" дарж, цааш нь {{ city.name }} гэж сонговол энд харагдана.
                        <template v-if="!user"> Нэрсийг харахын тулд <Link href="/login" class="text-white underline underline-offset-2">нэвтэрнэ үү</Link>.</template>
                    </p>
                </div>

                <a href="/ireh" class="group flex items-start justify-between gap-3 border border-brand-100 p-5">
                    <span>
                        <span class="kicker block">Офлайн</span>
                        <span class="mt-2 block font-medium text-brand-600 group-hover:underline group-hover:underline-offset-4">Ирэх өдрийн багц</span>
                        <span class="mt-1 block text-sm text-brand-400">Нисэх буудлын заавар, яаралтай утас. Интернэтгүй үед ч нээгдэнэ.</span>
                    </span>
                    <WifiOff class="mt-0.5 h-4 w-4 shrink-0 text-brand-300" />
                </a>

                <Link href="/damjih" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-500 hover:text-brand-600">
                    Бусад хот <ArrowRight class="h-4 w-4" />
                </Link>
            </aside>
        </div>
    </PublicLayout>
</template>
