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
    <div class="flex h-48 items-end gap-3">
        <div v-for="(d, i) in data" :key="i" class="flex flex-1 flex-col items-center gap-2">
            <div class="flex w-full flex-1 items-end">
                <div class="group relative w-full">
                    <div
                        class="w-full rounded-t-lg bg-gradient-to-t from-brand-600 to-brand-400 transition-all duration-300 hover:from-brand-700 hover:to-brand-500"
                        :style="{ height: `${Math.max(2, height(d.total))}%`, minHeight: '6px' }"
                    ></div>
                    <span
                        class="pointer-events-none absolute -top-7 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-md bg-gray-900 px-2 py-1 text-xs font-medium text-white opacity-0 shadow-lg transition group-hover:opacity-100"
                    >
                        {{ Number(d.total).toLocaleString() }}{{ unit }}
                    </span>
                </div>
            </div>
            <span class="text-xs font-medium text-gray-400">{{ d.label }}</span>
        </div>
    </div>
</template>
