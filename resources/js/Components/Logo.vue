<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    size: { type: String, default: 'md' },        // sm | md | lg
    badge: { type: String, default: 'solid' },    // solid | gradient | glass (glass = бараан дэвсгэр дээр)
    tone: { type: String, default: 'dark' },       // dark | white | brand — дугаарын өнгө
    subtitle: { type: String, default: '' },
    href: { type: String, default: '/' },
});

// Нэр .env-ийн APP_NAME-ээс ирнэ — нэр солиход зөвхөн .env-ийг засна.
const name = (import.meta.env.VITE_APP_NAME || 'OM137').trim();

// Нислэгийн код хэлбэртэй нэр (OM137): код нь самбарын хавтан, дугаар нь mono бичиг.
const code = name.match(/^([A-Za-z]{2})(\d+)$/);
const letters = computed(() => (code ? code[1].toUpperCase() : name.charAt(0).toUpperCase()).split(''));
const word = code ? code[2] : name;

const tile = {
    sm: 'h-[23px] w-4 text-[14px]',
    md: 'h-[26px] w-[18px] text-base',
    lg: 'h-8 w-[22px] text-[19px]',
};
const wordSize = { sm: 'text-[16px]', md: 'text-[18px]', lg: 'text-[22px]' };
const textTone = { dark: 'text-brand-600', white: 'text-white', brand: 'text-brand-600' };
const dark = computed(() => props.badge === 'glass');
</script>

<template>
    <Link :href="href" class="group inline-flex items-center gap-2" :aria-label="name">
        <span class="inline-flex gap-0.5" aria-hidden="true">
            <span
                v-for="(l, i) in letters"
                :key="i"
                class="logo-tile font-mono font-semibold leading-none"
                :class="[tile[size], dark ? 'logo-tile--dark' : '']"
            >{{ l }}</span>
        </span>
        <span class="leading-none">
            <span
                class="block font-semibold"
                :class="[wordSize[size], textTone[tone], code ? 'tabular font-mono tracking-[0.01em]' : 'tracking-tight']"
            >{{ word }}</span>
            <span v-if="subtitle" class="kicker mt-1 block">{{ subtitle }}</span>
        </span>
    </Link>
</template>
