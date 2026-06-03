<script setup>
defineProps({
    label: String,
    value: [String, Number],
    sub: { type: String, default: '' },
    icon: { type: String, default: 'chart' },
    accent: { type: String, default: 'blue' },
});

const accents = {
    blue: 'from-brand-500 to-brand-700',
    green: 'from-emerald-500 to-emerald-700',
    amber: 'from-amber-500 to-amber-600',
    purple: 'from-violet-500 to-violet-700',
    rose: 'from-rose-500 to-rose-700',
    slate: 'from-slate-500 to-slate-700',
};
</script>

<template>
    <div class="group relative overflow-hidden rounded-2xl bg-white p-5 shadow-soft ring-1 ring-gray-100 transition hover:shadow-lg">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">{{ label }}</p>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900">{{ value }}</p>
            </div>
            <span
                class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-md"
                :class="accents[accent]"
            >
                <svg v-if="icon === 'money'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <svg v-else-if="icon === 'ticket'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                <svg v-else-if="icon === 'users'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z" /></svg>
                <svg v-else-if="icon === 'ad'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                <svg v-else-if="icon === 'tag'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z" /></svg>
                <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
            </span>
        </div>
        <p v-if="sub" class="mt-3 text-xs text-gray-400">{{ sub }}</p>
    </div>
</template>
