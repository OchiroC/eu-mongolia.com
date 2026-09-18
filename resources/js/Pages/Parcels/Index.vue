<script setup>
import CustomsNotice from '@/Components/CustomsNotice.vue';
import PageHeader from '@/Components/PageHeader.vue';
import Pagination from '@/Components/Pagination.vue';
import TravelTabs from '@/Components/TravelTabs.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDate } from '@/lib/date';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, Package, Plane, Plus } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    parcels: Object,
    filters: Object,
    types: Object,
    directions: Object,
});

const user = computed(() => usePage().props.auth?.user);

function go(params) {
    router.get('/achaa', { ...props.filters, ...params }, { preserveState: true, preserveScroll: true, replace: true });
}
const chip = (on) => (on ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600');
</script>

<template>
    <Head title="Ачаа, илгээмж" />

    <PublicLayout>
        <PageHeader
            kicker="Ачаа"
            title="Ачаа, илгээмж"
            subtitle="Монгол, Германы хооронд нисэх хүмүүс ачааныхаа сул зайг санал болгож, илгээмж явуулах хүн авч явах хүнээ олно."
        >
            <template #actions>
                <Link :href="user ? '/achaa/new' : '/login'" class="inline-flex h-10 items-center gap-1.5 rounded-md bg-brand-600 px-4 text-sm font-medium text-white transition-colors hover:bg-brand-500">
                    <Plus class="h-4 w-4" /> Зар нэмэх
                </Link>
            </template>
        </PageHeader>

        <TravelTabs />

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-wrap gap-2">
                <button type="button" class="h-8 rounded-md border px-3 text-sm transition-colors" :class="chip(!filters.type)" @click="go({ type: undefined })">Бүгд</button>
                <button v-for="(label, key) in types" :key="key" type="button" class="h-8 rounded-md border px-3 text-sm transition-colors" :class="chip(filters.type === key)" @click="go({ type: key })">{{ label }}</button>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="h-8 rounded-md border px-3 text-sm transition-colors" :class="chip(!filters.direction)" @click="go({ direction: undefined })">Бүх чиглэл</button>
                <button v-for="(label, key) in directions" :key="key" type="button" class="h-8 rounded-md border px-3 text-sm transition-colors" :class="chip(filters.direction === key)" @click="go({ direction: key })">{{ label }}</button>
            </div>
        </div>

        <div class="mt-6"><CustomsNotice /></div>

        <ul v-if="parcels.data.length" class="mt-8 border-t border-brand-100">
            <li v-for="p in parcels.data" :key="p.id" class="border-b border-brand-100">
                <Link :href="`/achaa/${p.id}`" class="group grid gap-3 py-5 sm:grid-cols-[1fr_auto] sm:items-center">
                    <span class="flex min-w-0 gap-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md" :class="p.type === 'offer' ? 'bg-signal-100 text-signal-700' : 'bg-brand-50 text-brand-600'">
                            <Package class="h-5 w-5" />
                        </span>
                        <span class="min-w-0">
                            <span class="kicker block">{{ p.type_label }} · {{ p.direction_label }}</span>
                            <span class="mt-1 block font-medium text-brand-600 group-hover:underline group-hover:underline-offset-4">
                                {{ p.from_city }} → {{ p.to_city }}<template v-if="p.weight_kg"> · {{ p.weight_kg }} кг</template>
                            </span>
                            <span class="mt-1 line-clamp-1 block text-sm text-brand-400">{{ p.excerpt }}</span>
                        </span>
                    </span>
                    <span class="flex items-center gap-4 pl-14 sm:pl-0">
                        <span class="tabular inline-flex items-center gap-1.5 font-mono text-xs text-brand-500">
                            <Plane v-if="p.flight" class="h-3.5 w-3.5" />
                            <template v-if="p.flight">{{ p.flight.code }} · </template>{{ formatDate(p.date) }}
                        </span>
                        <span v-if="p.price" class="tabular font-mono text-sm text-brand-600">{{ p.price }}</span>
                        <ArrowUpRight class="h-4 w-4 text-brand-300 group-hover:text-brand-600" />
                    </span>
                </Link>
            </li>
        </ul>
        <div v-else class="mt-8 border border-brand-100 px-6 py-16 text-center">
            <p class="font-medium text-brand-600">Ачааны зар алга</p>
            <p class="mt-1 text-sm text-brand-400">Нисэх гэж байгаа бол ачааныхаа сул зайг санал болгоорой.</p>
        </div>

        <Pagination :links="parcels.links" />
    </PublicLayout>
</template>
