<script setup>
import PageHeader from '@/Components/PageHeader.vue';
import TravelTabs from '@/Components/TravelTabs.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Luggage, Plane, Repeat, ShieldCheck, TrainFront, Users } from 'lucide-vue-next';

defineProps({
    cities: { type: Array, default: () => [] },
});

const rules = [
    {
        icon: ShieldCheck,
        title: 'Шенгенд хаана орох вэ',
        body: 'Шенгений бүсийн хот руу явж байгаа бол паспортын хяналтыг Франкфуртад хийнэ. Очих улсынхаа Шенгений визийг гартаа бэлэн байлгаарай.',
    },
    {
        icon: Plane,
        title: 'Шенгенээс гадуур',
        body: 'Их Британи зэрэг Шенгенд ордоггүй улс руу нэг тасалбараар дамжиж байгаа бол транзит бүсээс гарахгүй. Нисэх буудлын транзит визийн шаардлагыг нисэхээсээ өмнө Германы ЭСЯ-наас шалгаарай.',
    },
    {
        icon: Repeat,
        title: 'Терминал солих',
        body: 'МИАТ 3-р терминалд буудаг. Дараагийн нислэг 1, 2-р терминалаас хөөрөх бол терминал хооронд явах, паспортын хяналтад зогсох хугацааг тооцоод дор хаяж 2 цагийн зайтай тасалбар аваарай.',
    },
    {
        icon: Luggage,
        title: 'Ачаа',
        body: 'Нэг тасалбартай бол ачаа ихэвчлэн эцсийн цэг хүртэл шууд явна. Тусдаа тасалбартай бол Франкфуртад ачаагаа авч дахин бүртгүүлнэ. Ачаа ирээгүй бол ачаа олгох танхимаас гарахаасаа өмнө мэдүүлж, лавлах дугаар аваарай.',
    },
    {
        icon: TrainFront,
        title: 'Галт тэрэг',
        body: 'Холын галт тэрэгний буудал (Fernbahnhof) 1-р терминалын дэргэд байдаг. 3-р терминалаас Sky Line галт тэргээр үнэгүй очно. Тасалбарыг DB Navigator аппаас эрт авбал хямд.',
    },
];
</script>

<template>
    <Head title="Франкфуртаар дамжих" />

    <PublicLayout>
        <PageHeader
            kicker="Транзит"
            title="Франкфуртаар дамжих"
            subtitle="Монголоос Европ руу нисэх хүмүүсийн ихэнх нь Франкфуртаар дамждаг. Очих хотоо сонгоод нисэх буудлаас цааш хэрхэн явах, нэг хот руу явах монголчуудыг хараарай."
        />

        <TravelTabs />

        <section class="overflow-hidden rounded-md bg-board text-white">
            <div class="flex items-center justify-between border-b border-board-line px-5 py-4">
                <span class="flex items-center gap-2.5 font-mono text-[11px] uppercase tracking-[0.14em] text-white/70">
                    <Repeat class="h-4 w-4 text-signal-400" />
                    Дамжих <span class="text-white/30">/ Umsteigen</span>
                </span>
                <span class="text-xs text-white/40">FRA-аас цааш</span>
            </div>
            <div class="hidden grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_7rem_8rem_6rem_1rem] gap-4 border-b border-board-line px-5 py-2.5 font-mono text-[10px] uppercase tracking-[0.14em] text-white/35 md:grid">
                <span>Хот / Ziel</span><span>Улс</span><span>Хэрхэн</span><span>Хугацаа</span><span>Хамт явах</span><span />
            </div>
            <ul>
                <li v-for="c in cities" :key="c.slug" class="border-b border-board-line last:border-b-0">
                    <Link
                        :href="`/damjih/${c.slug}`"
                        class="group grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-4 gap-y-1 px-5 py-3.5 transition-colors hover:bg-board-soft md:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_7rem_8rem_6rem_1rem]"
                    >
                        <span class="min-w-0">
                            <span class="font-mono text-[15px] font-medium uppercase text-signal-400">{{ c.name }}</span>
                            <span class="ml-2 font-mono text-[11px] uppercase text-white/35">{{ c.local }}</span>
                        </span>
                        <span class="text-right text-[13px] text-white/60 md:text-left">
                            {{ c.country }}<span v-if="!c.schengen" class="ml-2 font-mono text-[10px] uppercase tracking-[0.1em] text-signal-300">Шенгенээс гадуур</span>
                        </span>
                        <span class="inline-flex items-center gap-2 font-mono text-[12px] uppercase text-white/70">
                            <component :is="c.mode === 'train' ? TrainFront : Plane" class="h-3.5 w-3.5 text-white/40" />
                            {{ c.mode_label }}
                        </span>
                        <span class="tabular text-right font-mono text-[12px] text-white/70 md:text-left">{{ c.time }}</span>
                        <span class="tabular hidden items-center gap-1.5 font-mono text-[12px] md:inline-flex" :class="c.travellers ? 'text-white' : 'text-white/25'">
                            <Users class="h-3.5 w-3.5" /> {{ c.travellers }}
                        </span>
                        <ArrowRight class="hidden h-4 w-4 text-white/30 transition-colors group-hover:text-signal-400 md:block" />
                    </Link>
                </li>
            </ul>
        </section>

        <section class="mt-14">
            <p class="kicker">Буухаасаа өмнө</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight">Дамжин явахад мэдэх зүйл</h2>
            <div class="mt-6 grid gap-px overflow-hidden rounded-md border border-brand-100 bg-brand-100 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="r in rules" :key="r.title" class="bg-white p-6">
                    <component :is="r.icon" class="h-5 w-5 text-brand-600" />
                    <h3 class="mt-4 font-semibold text-brand-600">{{ r.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-brand-500">{{ r.body }}</p>
                </div>
                <div class="flex flex-col justify-between bg-brand-600 p-6 text-white">
                    <div>
                        <Users class="h-5 w-5 text-signal-400" />
                        <h3 class="mt-4 font-semibold">Хамт явах хүнээ ол</h3>
                        <p class="mt-2 text-sm leading-relaxed text-white/70">Нислэгээ тэмдэглэхдээ цааш хаашаа явахаа сонговол нэг хот руу явах монголчууд таныг харна.</p>
                    </div>
                    <Link href="/flights" class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-signal-400 hover:text-signal-300">
                        Нислэгээ сонгох <ArrowRight class="h-4 w-4" />
                    </Link>
                </div>
            </div>
            <p class="mt-4 text-xs text-brand-400">
                Энэ нь ерөнхий зөвлөгөө. Виз, транзитын шаардлага, галт тэрэгний хуваарийг албан ёсны эх сурвалжаас шалгаарай.
            </p>
        </section>
    </PublicLayout>
</template>
