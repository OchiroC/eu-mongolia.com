<script setup>
import { BookOpen, Clapperboard, Dices, Landmark, Languages, Mail, MapPin, School, Shapes } from 'lucide-vue-next';
import PageHeader from '@/Components/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    resources: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    filters: Object,
});

function filterCategory(key) {
    router.get('/kids', key && key !== props.filters.category ? { category: key } : {}, { preserveState: true, replace: true, preserveScroll: true });
}

// Ангиллын icon.
const catIcon = {
    language: Languages, books: BookOpen, video: Clapperboard, school: School, culture: Landmark, games: Dices, other: Shapes,
};
</script>

<template>
    <Head title="Хүүхдийн булан" />

    <PublicLayout>
        <PageHeader kicker="Гэр бүл" title="Хүүхдийн булан" subtitle="Гадаадад өссөн монгол хүүхдүүдэд зориулсан монгол хэл сурах, ном, дуу, сургууль, соёлын нөөц." />

        <!-- Ангилал -->
        <div class="mb-6 flex flex-wrap gap-2">
            <button class="rounded-md px-3.5 py-1.5 text-sm transition" :class="!filters.category ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'" @click="filterCategory(null)">Бүгд</button>
            <button
                v-for="c in categories"
                :key="c.key"
                class="rounded-md px-3.5 py-1.5 text-sm transition"
                :class="filters.category === c.key ? 'border border-brand-600 bg-brand-600 text-white' : 'border border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600'"
                @click="filterCategory(c.key)"
            ><component :is="catIcon[c.key] || Shapes" class="mr-1 inline-block h-3.5 w-3.5 align-[-2px]" />{{ c.label }} <span class="text-xs opacity-70">{{ c.count }}</span></button>
        </div>

        <div v-if="resources.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <component
                :is="r.url ? 'a' : 'div'"
                v-for="r in resources"
                :key="r.id"
                :href="r.url || undefined"
                :target="r.url ? '_blank' : undefined"
                :rel="r.url ? 'noopener' : undefined"
                class="group flex flex-col rounded-2xl border bg-white p-5 shadow-card transition duration-300"
                :class="[r.is_featured ? 'border-amber-200 ring-1 ring-amber-100' : 'border-gray-100', r.url ? ' hover:shadow-card-lg' : '']"
            >
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-brand-50 text-brand-600"><component :is="catIcon[r.category] || Shapes" class="h-5 w-5" /></span>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-gray-900" :class="r.url ? 'group-hover:text-brand-700' : ''">{{ r.title }}</h3>
                        <p class="text-xs text-gray-400">{{ r.category_label }}<span v-if="r.age_range"> · {{ r.age_range }} нас</span></p>
                    </div>
                </div>
                <p v-if="r.description" class="mt-3 text-sm text-gray-600">{{ r.description }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-400">
                    <span v-if="r.city"><MapPin class="mr-1 inline-block h-3.5 w-3.5 align-[-2px]" />{{ r.city }}<span v-if="r.country">, {{ r.country }}</span></span>
                    <span v-if="r.contact"><Mail class="mr-1 inline-block h-3.5 w-3.5 align-[-2px]" />{{ r.contact }}</span>
                </div>
                <span v-if="r.url" class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-brand-700">Нээх <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg></span>
            </component>
        </div>

        <div v-else class="rounded-3xl border border-dashed border-gray-200 bg-white py-16 text-center">
            <p class="font-medium text-gray-700">Нөөц олдсонгүй</p>
            <p class="text-sm text-gray-400">Удахгүй нэмэгдэнэ.</p>
        </div>

        <p class="mt-6 rounded-xl bg-brand-50/60 px-4 py-3 text-sm text-gray-600">
            Сургууль, бүлгэм эсвэл хэрэгтэй нөөцөө нэмүүлэх бол <a href="/contact" class="font-medium text-brand-700 hover:underline">бидэнтэй холбогдоно</a> уу.
        </p>
    </PublicLayout>
</template>
