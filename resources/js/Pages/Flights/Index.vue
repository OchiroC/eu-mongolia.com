<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import TravelTabs from '@/Components/TravelTabs.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Car, Info, Package, PlaneLanding, PlaneTakeoff, Users } from 'lucide-vue-next';

defineProps({
    arrivals: { type: Array, default: () => [] },
    departures: { type: Array, default: () => [] },
});

const boards = [
    { key: 'arrivals', title: 'Ирэх', de: 'Ankunft', icon: PlaneLanding, note: 'Улаанбаатараас Франкфурт руу' },
    { key: 'departures', title: 'Явах', de: 'Abflug', icon: PlaneTakeoff, note: 'Франкфуртаас Улаанбаатар руу' },
];

function dayMonth(date) {
    const [, m, d] = date.split('-');
    return `${d}.${m}`;
}
</script>

<template>
    <Head title="Нислэгийн самбар" />

    <PublicLayout>
        <PageHeader
            kicker="Нислэг"
            title="Нислэгийн самбар"
            subtitle="МИАТ-ын Улаанбаатар, Франкфуртын хоорондох нислэг. Нислэг бүрээр угтах хүн, хамт явах машин, ачаа авч явах хүн олоорой."
        >
            <template #actions>
                <a href="/ireh" class="inline-flex h-10 items-center gap-1.5 rounded-md border border-brand-200 px-4 text-sm font-medium text-brand-600 transition-colors hover:border-brand-600">
                    Ирэх өдрийн багц
                </a>
            </template>
        </PageHeader>

        <TravelTabs />

        <div class="grid gap-8 lg:grid-cols-2">
            <section v-for="b in boards" :key="b.key" class="overflow-hidden rounded-md bg-board text-white">
                <div class="flex items-center justify-between border-b border-board-line px-5 py-4">
                    <span class="flex items-center gap-2.5 font-mono text-[11px] uppercase tracking-[0.14em] text-white/70">
                        <component :is="b.icon" class="h-4 w-4 text-signal-400" />
                        {{ b.title }} <span class="text-white/30">/ {{ b.de }}</span>
                    </span>
                    <span class="text-xs text-white/40">{{ b.note }}</span>
                </div>
                <div class="grid grid-cols-[4.5rem_3.5rem_4.5rem_minmax(0,1fr)_1rem] gap-3 border-b border-board-line px-5 py-2.5 font-mono text-[10px] uppercase tracking-[0.14em] text-white/35">
                    <span>Огноо</span><span>Цаг</span><span>Нислэг</span><span>Төлөв</span><span />
                </div>
                <ul v-if="$props[b.key].length">
                    <li v-for="f in $props[b.key]" :key="f.id" class="border-b border-board-line last:border-b-0">
                        <Link :href="`/flights/${f.slug}`" class="group grid grid-cols-[4.5rem_3.5rem_4.5rem_minmax(0,1fr)_1rem] items-center gap-3 px-5 py-3.5 transition-colors hover:bg-board-soft">
                            <span class="tabular font-mono text-[13px] text-white/60">{{ f.weekday }} {{ dayMonth(f.date) }}</span>
                            <span class="tabular font-mono text-[15px] text-signal-400" :class="f.status === 'cancelled' ? 'line-through opacity-50' : ''">{{ f.time }}</span>
                            <span class="font-mono text-[14px] font-medium">{{ f.code }}</span>
                            <span class="flex min-w-0 items-center gap-3">
                                <span v-if="f.status !== 'scheduled'" class="font-mono text-[11px] uppercase tracking-wider" :class="f.status === 'cancelled' ? 'text-red-400' : 'text-signal-300'">{{ f.status_label }}</span>
                                <span class="tabular flex items-center gap-3 font-mono text-[12px] text-white/50">
                                    <span class="inline-flex items-center gap-1" :class="f.passengers_count ? 'text-white' : ''" title="Энэ нислэгээр явах хүн"><Users class="h-3.5 w-3.5" />{{ f.passengers_count }}</span>
                                    <span class="inline-flex items-center gap-1" :class="f.rides_count ? 'text-white' : ''" title="Хамт явах машин"><Car class="h-3.5 w-3.5" />{{ f.rides_count }}</span>
                                    <span class="inline-flex items-center gap-1" :class="f.parcels_count ? 'text-white' : ''" title="Ачааны зар"><Package class="h-3.5 w-3.5" />{{ f.parcels_count }}</span>
                                </span>
                            </span>
                            <ArrowRight class="h-4 w-4 text-white/25 transition-colors group-hover:text-signal-400" />
                        </Link>
                    </li>
                </ul>
                <p v-else class="px-5 py-10 text-sm text-white/50">Ойрын хугацаанд нислэг алга.</p>
            </section>
        </div>

        <p class="mt-6 flex items-start gap-2 text-sm text-brand-400">
            <Info class="mt-0.5 h-4 w-4 shrink-0" />
            Цаг нь Франкфуртын цагаар. Энэ самбар МИАТ-ын албан ёсны мэдээлэл биш бөгөөд хуваарь улирлаар өөрчлөгддөг. Нислэгийн өмнө тасалбар эсвэл МИАТ-ын вэбсайтаас шалгаарай.
        </p>
    </PublicLayout>
</template>
