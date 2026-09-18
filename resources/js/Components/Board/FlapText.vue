<script setup>
/*
 * Split-flap (Solari) самбарын текст. Үсэг бүр тусдаа хавтан; ачаалах үед
 * санамсаргүй үсгээр эргэлдээд байрандаа тогтоно. Текст өөрчлөгдөхөд
 * (цагийн секунд гэх мэт) зөвхөн өөрчлөгдсөн хавтан богино эргэлт хийнэ.
 */
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    text: { type: [String, Number], default: '' },
    // Тогтмол урттай талбар — дутуу хэсгийг хоосон хавтангаар дүүргэнэ (бодит самбар шиг).
    pad: { type: Number, default: 0 },
    // true бол үгээр мөр шилжинэ (том гарчигт).
    wrap: { type: Boolean, default: false },
    delay: { type: Number, default: 0 },
    cycles: { type: Number, default: 12 },
    // Утга өөрчлөгдөх бүрт бүх хавтан дахин эргэнэ (hover эффект).
    replay: { type: Number, default: 0 },
});

const POOL = 'АБВГДЕЖЗИЙКЛМНОӨПРСТУҮФХЦЧШЭЮЯ0123456789';
const rand = () => POOL[Math.floor(Math.random() * POOL.length)];

const target = computed(() => {
    const t = String(props.text ?? '').toUpperCase();
    return props.pad > t.length ? t.padEnd(props.pad, ' ') : t;
});

// Эхний утга = эцсийн текст: JS ачаалахаас өмнө ч, хөдөлгөөнгүй горимд ч зөв харагдана.
const shown = ref(target.value.split(''));
const ticking = ref(new Set());

let timer = null;
const reduced = () => typeof window !== 'undefined' && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

function animate(indices, startDelay, cycles, stagger = 28) {
    if (reduced() || !indices.length) {
        shown.value = target.value.split('');
        return;
    }
    const final = target.value.split('');
    const settleAt = new Map(indices.map((i, k) => [i, startDelay + k * stagger + cycles * 45]));
    const start = performance.now();
    ticking.value = new Set(indices);
    clearInterval(timer);
    timer = setInterval(() => {
        const t = performance.now() - start;
        const next = [...shown.value];
        final.forEach((_, i) => { if (next[i] === undefined) next[i] = ' '; });
        next.length = final.length;
        for (const i of [...ticking.value]) {
            if (t < startDelay) continue;
            if (t >= settleAt.get(i)) {
                next[i] = final[i];
                ticking.value.delete(i);
            } else {
                next[i] = rand();
            }
        }
        shown.value = next;
        if (!ticking.value.size) clearInterval(timer);
    }, 45);
}

onMounted(() => {
    const idx = target.value.split('').map((c, i) => (c === ' ' ? -1 : i)).filter((i) => i >= 0);
    animate(idx, props.delay, props.cycles);
});
onUnmounted(() => clearInterval(timer));

watch(target, (now, before) => {
    const changed = now.split('').map((c, i) => (c !== (before ?? '')[i] ? i : -1)).filter((i) => i >= 0 && now[i] !== ' ');
    if (shown.value.length !== now.length) shown.value = now.split('');
    animate(changed, 0, 3);
});

// Hover: эргэлт явагдаж байхад дахин эхлүүлэхгүй (анивчихаас сэргийлнэ).
watch(() => props.replay, () => {
    if (ticking.value.size) return;
    const idx = target.value.split('').map((c, i) => (c === ' ' ? -1 : i)).filter((i) => i >= 0);
    animate(idx, 0, 5, 16);
});

// Үгээр бүлэглэнэ (wrap горимд мөр шилжих боломжтой болгох).
const words = computed(() => {
    if (!props.wrap) return [{ start: 0, len: target.value.length }];
    const out = [];
    let i = 0;
    for (const w of target.value.split(' ')) {
        if (w.length) out.push({ start: i, len: w.length });
        i += w.length + 1;
    }
    return out;
});
</script>

<template>
    <span class="flap-line" :class="wrap ? 'flap-line--wrap' : ''" :aria-label="String(text)" role="text">
        <span v-for="(w, wi) in words" :key="wi" class="flap-word" aria-hidden="true">
            <span
                v-for="n in w.len"
                :key="n"
                class="flap"
                :class="ticking.has(w.start + n - 1) ? 'flap--tick' : ''"
            >{{ shown[w.start + n - 1] === ' ' ? '' : shown[w.start + n - 1] }}</span>
        </span>
    </span>
</template>
