<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, default: () => [] },
});
</script>

<template>
    <div v-if="links && links.length > 3" class="mt-10 flex flex-wrap justify-center gap-1">
        <Link
            v-for="link in links"
            :key="link.label"
            :href="link.url || ''"
            class="tabular flex h-9 min-w-9 items-center justify-center rounded-md border px-3 font-mono text-sm"
            :class="[link.active ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200 text-brand-500 hover:border-brand-600', !link.url ? 'pointer-events-none opacity-40' : '']"
            preserve-scroll
            v-html="link.label"
        />
    </div>
</template>
