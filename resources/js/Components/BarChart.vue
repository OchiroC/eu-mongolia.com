<script setup>
import { computed } from 'vue';

const props = defineProps({
    // [{ label: 'Jan', total: 120 }, ...]
    data: { type: Array, default: () => [] },
    unit: { type: String, default: '€' },
});

const max = computed(() => Math.max(1, ...props.data.map((d) => Number(d.total))));

function height(value) {
    return Math.round((Number(value) / max.value) * 100);
}
</script>

<template>
    <!-- Өндрийн %-ийг тооцох боломжтой болгохын тулд багана бүр контейнерийн бүтэн өндрийг эзэлнэ. -->
    <div class="flex h-48 gap-3">
        <div v-for="(d, i) in data" :key="i" class="flex flex-1 flex-col items-center gap-2">
            <div class="flex w-full flex-1 items-end">
                <div class="group relative flex h-full w-full items-end">
                    <div
                        class="w-full rounded-t-[2px] bg-brand-600 transition-colors hover:bg-signal-400"
                        :style="{ height: `${Math.max(2, height(d.total))}%`, minHeight: '6px' }"
                    ></div>
                    <span
                        class="pointer-events-none absolute -top-7 left-1/2 -translate-x-1/2 whitespace-nowrap tabular rounded-[3px] bg-board px-2 py-1 font-mono text-xs text-white opacity-0 transition group-hover:opacity-100"
                    >
                        {{ Number(d.total).toLocaleString() }}{{ unit }}
                    </span>
                </div>
            </div>
            <span class="tabular font-mono text-[11px] text-gray-400">{{ d.label }}</span>
        </div>
    </div>
</template>
