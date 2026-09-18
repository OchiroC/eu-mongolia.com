<script setup>
import 'leaflet/dist/leaflet.css';
import PageHeader from '@/Components/PageHeader.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import L from 'leaflet';
import { ArrowUpRight, Briefcase, Store } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    places: { type: Array, default: () => [] },
});

const FRANKFURT = [50.1109, 8.6821];
const filter = ref('all');
const el = ref(null);
const selected = ref(null);
let map = null;
let layer = null;
const markers = new Map();

const visible = computed(() => props.places.filter((p) => filter.value === 'all' || p.kind === filter.value));
const kindLabel = { business: 'Бизнес', professional: 'Мэргэжилтэн' };
const icon = (kind) => L.divIcon({ className: `map-pin map-pin--${kind}`, html: '<span></span>', iconSize: [22, 22], iconAnchor: [11, 11], popupAnchor: [0, -10] });

function escape(s) {
    return String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function draw() {
    layer.clearLayers();
    markers.clear();
    visible.value.forEach((p, i) => {
        const m = L.marker([p.lat, p.lng], { icon: icon(p.kind) })
            .bindPopup(`<p class="map-popup-kicker">${kindLabel[p.kind]}</p><p class="map-popup-title">${escape(p.name)}</p><p class="map-popup-meta">${escape([p.meta, p.address || p.city].filter(Boolean).join(' · '))}</p><a class="map-popup-link" href="${p.href}">Дэлгэрэнгүй</a>`)
            .on('click', () => (selected.value = i));
        layer.addLayer(m);
        markers.set(i, m);
    });
    fitAll();
}

function fitAll() {
    if (visible.value.length) map.fitBounds(L.latLngBounds(visible.value.map((p) => [p.lat, p.lng])), { padding: [40, 40], maxZoom: 13 });
    else map.setView(FRANKFURT, 11);
}

function focus(i) {
    selected.value = i;
    const p = visible.value[i];
    map.flyTo([p.lat, p.lng], 15, { duration: 0.6 });
    markers.get(i)?.openPopup();
}

onMounted(() => {
    map = L.map(el.value, { scrollWheelZoom: true }).setView(FRANKFURT, 11);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);
    layer = L.layerGroup().addTo(map);
    draw();
});
onBeforeUnmount(() => map?.remove());
watch(filter, () => {
    selected.value = null;
    draw();
});

const chip = (on) => (on ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600');
</script>

<template>
    <Head title="Газрын зураг" />

    <PublicLayout>
        <PageHeader
            kicker="Газрын зураг"
            title="Монгол газрууд"
            subtitle="Монгол бизнес, монголоор үйлчилдэг мэргэжилтнүүдийн байршил. Өөрийн бизнес, үйлчилгээгээ нэмэхдээ байршлаа тэмдэглээрэй."
        />

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <button type="button" class="h-8 rounded-md border px-3 text-sm transition-colors" :class="chip(filter === 'all')" @click="filter = 'all'">Бүгд <span class="tabular font-mono text-xs opacity-60">{{ places.length }}</span></button>
            <button type="button" class="inline-flex h-8 items-center gap-1.5 rounded-md border px-3 text-sm transition-colors" :class="chip(filter === 'business')" @click="filter = 'business'"><Store class="h-3.5 w-3.5" /> Бизнес</button>
            <button type="button" class="inline-flex h-8 items-center gap-1.5 rounded-md border px-3 text-sm transition-colors" :class="chip(filter === 'professional')" @click="filter = 'professional'"><Briefcase class="h-3.5 w-3.5" /> Мэргэжилтэн</button>
            <button v-if="visible.length" type="button" class="ml-auto text-sm font-medium text-brand-500 underline-offset-4 hover:text-brand-600 hover:underline" @click="fitAll">Бүгдийг харах</button>
        </div>

        <div class="grid gap-6 lg:grid-cols-12">
            <ul class="order-2 max-h-[70vh] overflow-y-auto border-t border-brand-100 lg:order-1 lg:col-span-4">
                <li v-for="(p, i) in visible" :key="p.href" class="border-b border-brand-100">
                    <button type="button" class="flex w-full items-start gap-3 px-1 py-3.5 text-left transition-colors hover:bg-brand-50" :class="selected === i ? 'bg-brand-50' : ''" @click="focus(i)">
                        <component :is="p.kind === 'business' ? Store : Briefcase" class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-medium text-brand-600">{{ p.name }}</span>
                            <span class="block truncate text-sm text-brand-400">{{ [p.meta, p.city].filter(Boolean).join(' · ') }}</span>
                        </span>
                        <Link :href="p.href" class="mt-0.5 text-brand-300 hover:text-brand-600" :aria-label="`${p.name} дэлгэрэнгүй`" @click.stop><ArrowUpRight class="h-4 w-4" /></Link>
                    </button>
                </li>
                <li v-if="!visible.length" class="px-1 py-10 text-sm text-brand-400">Байршил оруулсан газар алга.</li>
            </ul>
            <div class="order-1 lg:order-2 lg:col-span-8">
                <div ref="el" class="h-[60vh] min-h-[360px] overflow-hidden rounded-md border border-brand-100 lg:h-[70vh]" />
            </div>
        </div>
    </PublicLayout>
</template>
