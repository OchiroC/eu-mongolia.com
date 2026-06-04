<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    q: { type: String, default: '' },
    groups: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
});

const term = ref(props.q);
function doSearch() {
    router.get('/search', { q: term.value || undefined }, { preserveState: true });
}
</script>

<template>
    <Head :title="q ? `Хайлт: ${q}` : 'Хайлт'" />

    <PublicLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-2xl font-bold text-gray-900">Хайлт</h1>

            <div class="mt-4 flex gap-2 rounded-2xl bg-white p-1.5 shadow-soft ring-1 ring-gray-100">
                <input
                    v-model="term"
                    type="search"
                    placeholder="Зар, ажил, бизнес, мэдээ... бүгдээс хай"
                    class="min-w-0 flex-1 rounded-xl border-0 px-3 py-2.5 text-gray-900 placeholder-gray-400 focus:ring-0"
                    autofocus
                    @keydown.enter="doSearch"
                />
                <button class="shrink-0 rounded-xl bg-brand-600 px-5 py-2.5 font-semibold text-white hover:bg-brand-700" @click="doSearch">Хайх</button>
            </div>

            <p v-if="q" class="mt-3 text-sm text-gray-400">"{{ q }}" — {{ total }} илэрц</p>

            <div v-if="groups.length" class="mt-6 space-y-6">
                <div v-for="g in groups" :key="g.label" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-soft">
                    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2.5">
                        <h2 class="text-sm font-semibold text-gray-900">{{ g.label }}</h2>
                        <Link :href="g.href" class="text-xs text-brand-700 hover:underline">Бүгд →</Link>
                    </div>
                    <Link
                        v-for="(item, i) in g.items"
                        :key="i"
                        :href="item.url"
                        class="flex items-center justify-between gap-3 border-b border-gray-50 px-4 py-2.5 transition last:border-0 hover:bg-brand-50/40"
                    >
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-gray-800">{{ item.title }}</span>
                            <span v-if="item.subtitle" class="block truncate text-xs text-gray-400">{{ item.subtitle }}</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </Link>
                </div>
            </div>

            <div v-else-if="q.length >= 2" class="mt-8 rounded-2xl border border-dashed border-gray-200 bg-white py-16 text-center">
                <p class="font-medium text-gray-700">Илэрц олдсонгүй</p>
                <p class="text-sm text-gray-400">Өөр түлхүүр үгээр оролдоно уу.</p>
            </div>

            <p v-else class="mt-8 text-center text-sm text-gray-400">Хайх үгээ оруулна уу (2-оос дээш үсэг).</p>
        </div>
    </PublicLayout>
</template>
