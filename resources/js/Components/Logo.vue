<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    size: { type: String, default: 'md' },        // sm | md | lg
    badge: { type: String, default: 'gradient' },  // gradient | glass | solid
    tone: { type: String, default: 'dark' },       // dark | white | brand (wordmark өнгө)
    subtitle: { type: String, default: '' },
    href: { type: String, default: '/' },
});

const badgeSize = {
    sm: 'h-8 w-8 rounded-lg text-base',
    md: 'h-9 w-9 rounded-xl text-lg',
    lg: 'h-10 w-10 rounded-xl text-xl',
};
const wordSize = { sm: 'text-base', md: 'text-lg', lg: 'text-xl' };
const badgeTone = {
    gradient: 'bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-sm transition duration-300 group-hover:shadow-brand-glow',
    glass: 'bg-white/15 text-white ring-1 ring-white/20 backdrop-blur',
    solid: 'bg-brand-600 text-white',
};
const textTone = { dark: 'text-gray-900', white: 'text-white', brand: 'text-brand-800' };
</script>

<template>
    <Link :href="href" class="group inline-flex items-center gap-2.5">
        <span
            class="flex shrink-0 items-center justify-center font-extrabold tracking-tight"
            :class="[badgeSize[size], badgeTone[badge]]"
        >Я</span>
        <span class="leading-none">
            <span class="block font-bold tracking-tight" :class="[wordSize[size], textTone[tone]]">Yazguur</span>
            <span v-if="subtitle" class="mt-0.5 block text-[11px] font-medium text-gray-400">{{ subtitle }}</span>
        </span>
    </Link>
</template>
