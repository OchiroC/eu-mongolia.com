<script setup>
// Нислэг, хамт аялах, ачаа гурвыг нэг төв болгон холбосон таб.
import { Link, usePage } from '@inertiajs/vue3';
import { Car, Package, Plane } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const tabs = [
    { name: 'Нислэг', href: '/flights', icon: Plane },
    { name: 'Хамт аялах', href: '/rides', icon: Car },
    { name: 'Ачаа', href: '/achaa', icon: Package },
];
const active = computed(() => tabs.find((t) => page.url === t.href || page.url.startsWith(t.href + '?') || page.url.startsWith(t.href + '/'))?.href);
</script>

<template>
    <nav class="mb-8 flex gap-1 border-b border-brand-100" aria-label="Нислэг, аялал, ачаа">
        <Link
            v-for="t in tabs"
            :key="t.href"
            :href="t.href"
            class="relative -mb-px inline-flex items-center gap-2 border-b-2 px-3 pb-3 text-sm font-medium transition-colors"
            :class="active === t.href ? 'border-brand-600 text-brand-600' : 'border-transparent text-brand-400 hover:text-brand-600'"
        >
            <component :is="t.icon" class="h-4 w-4" />
            {{ t.name }}
        </Link>
    </nav>
</template>
