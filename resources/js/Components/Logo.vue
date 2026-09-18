<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    size: { type: String, default: 'md' },        // sm | md | lg
    badge: { type: String, default: 'solid' },    // solid | gradient | glass (glass = бараан дэвсгэр дээр)
    tone: { type: String, default: 'dark' },       // dark | white | brand — дугаарын өнгө
    subtitle: { type: String, default: '' },
    href: { type: String, default: '/' },
});

// Нэр .env-ийн APP_NAME-ээс ирнэ — нэр солиход зөвхөн .env-ийг засна.
const name = (import.meta.env.VITE_APP_NAME || 'OM137').trim();

// Нислэгийн код хэлбэртэй нэр (OM137): үсэг, дугаар хоёулаа самбарын хавтан. Үсэг шар, дугаар цагаан.
const code = name.match(/^([A-Za-z]{2})(\d+)$/);
const letters = computed(() => (code ? code[1].toUpperCase() : name.charAt(0).toUpperCase()).split(''));
const digits = code ? code[2].split('') : [];
const word = code ? '' : name.slice(1);

const tile = {
    sm: 'h-[23px] w-4 text-[14px]',
    md: 'h-[26px] w-[18px] text-base',
    lg: 'h-8 w-[22px] text-[19px]',
};
const wordSize = { sm: 'text-[16px]', md: 'text-[18px]', lg: 'text-[22px]' };
const textTone = { dark: 'text-brand-600', white: 'text-white', brand: 'text-brand-600' };
const dark = computed(() => props.badge === 'glass');

// Самбар шиг тогтмол давтамжтай эргэнэ: хавтан бүр санамсаргүй тэмдэгтээр эргэлдээд байрандаа тогтоно.
const INTERVAL = 9000;
const final = [...letters.value, ...digits];
const shown = ref([...final]);
const ticking = ref(new Set());
const pool = (i) => (i < letters.value.length ? 'ABCDEFGHJKLMNOPRSTUVWXYZ' : '0123456789');
let spinTimer = null;
let loopTimer = null;

function spin() {
    if (ticking.value.size || document.hidden) return;
    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) return;
    const start = performance.now();
    const settleAt = final.map((_, i) => 260 + i * 70);
    ticking.value = new Set(final.map((_, i) => i));
    spinTimer = setInterval(() => {
        const t = performance.now() - start;
        shown.value = final.map((c, i) => {
            if (t >= settleAt[i]) {
                ticking.value.delete(i);
                return c;
            }
            const p = pool(i);
            return p[Math.floor(Math.random() * p.length)];
        });
        if (!ticking.value.size) clearInterval(spinTimer);
    }, 45);
}

onMounted(() => {
    spin();
    loopTimer = setInterval(spin, INTERVAL);
});
onUnmounted(() => {
    clearInterval(spinTimer);
    clearInterval(loopTimer);
});
</script>

<template>
    <Link :href="href" class="group inline-flex items-center gap-2" :aria-label="name" @mouseenter="spin">
        <span class="inline-flex gap-0.5" aria-hidden="true">
            <span
                v-for="(c, i) in shown"
                :key="i"
                class="logo-tile font-mono font-semibold leading-none"
                :class="[tile[size], dark ? 'logo-tile--dark' : '', i >= letters.length ? 'logo-tile--digit' : '', ticking.has(i) ? 'opacity-70' : '']"
            >{{ c }}</span>
        </span>
        <span v-if="word || subtitle" class="leading-none">
            <span
                v-if="word"
                class="block font-semibold tracking-tight"
                :class="[wordSize[size], textTone[tone]]"
            >{{ word }}</span>
            <span v-if="subtitle" class="kicker mt-1 block">{{ subtitle }}</span>
        </span>
    </Link>
</template>
