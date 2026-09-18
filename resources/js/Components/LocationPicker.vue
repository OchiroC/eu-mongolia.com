<script setup>
/*
 * Байршил сонгох: хаягаар хайх (OpenStreetMap Nominatim) эсвэл газрын зураг дээр дарж тэмдэглэнэ.
 * Сонгосон координат нийтийн газрын зураг (/map) дээр харагдана.
 */
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { MapPin, Search, X } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    lat: { type: [Number, String], default: null },
    lng: { type: [Number, String], default: null },
    query: { type: String, default: '' },
});
const emit = defineEmits(['update:lat', 'update:lng']);

const FRANKFURT = [50.1109, 8.6821];
const el = ref(null);
const searching = ref(false);
const message = ref('');
let map = null;
let marker = null;

const pinIcon = L.divIcon({ className: 'map-pin', html: '<span></span>', iconSize: [22, 22], iconAnchor: [11, 11] });

function place(lat, lng, pan = true) {
    const pos = [Number(lat), Number(lng)];
    if (marker) marker.setLatLng(pos);
    else marker = L.marker(pos, { icon: pinIcon }).addTo(map);
    if (pan) map.setView(pos, Math.max(map.getZoom(), 15));
}

function set(lat, lng) {
    emit('update:lat', Number(lat.toFixed(6)));
    emit('update:lng', Number(lng.toFixed(6)));
}

async function search() {
    if (!props.query.trim()) {
        message.value = 'Эхлээд хаяг, хотоо оруулна уу.';
        return;
    }
    searching.value = true;
    message.value = '';
    try {
        const url = `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(props.query)}`;
        const res = await fetch(url, { headers: { 'Accept-Language': 'de' } });
        const [hit] = await res.json();
        if (hit) {
            set(Number(hit.lat), Number(hit.lon));
            place(hit.lat, hit.lon);
        } else {
            message.value = 'Хаяг олдсонгүй. Газрын зураг дээр дарж тэмдэглэнэ үү.';
        }
    } catch {
        message.value = 'Хайлт амжилтгүй боллоо. Газрын зураг дээр дарж тэмдэглэнэ үү.';
    } finally {
        searching.value = false;
    }
}

function clear() {
    emit('update:lat', null);
    emit('update:lng', null);
    if (marker) {
        marker.remove();
        marker = null;
    }
}

onMounted(() => {
    const has = props.lat && props.lng;
    map = L.map(el.value, { scrollWheelZoom: false }).setView(has ? [Number(props.lat), Number(props.lng)] : FRANKFURT, has ? 15 : 11);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);
    if (has) place(props.lat, props.lng, false);
    map.on('click', (e) => {
        set(e.latlng.lat, e.latlng.lng);
        place(e.latlng.lat, e.latlng.lng, false);
    });
});
onBeforeUnmount(() => map?.remove());

watch(() => [props.lat, props.lng], ([lat, lng]) => {
    if (lat && lng && map) place(lat, lng, false);
});
</script>

<template>
    <div class="space-y-2">
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" class="inline-flex h-9 items-center gap-1.5 rounded-md border border-brand-200 px-3 text-sm text-brand-600 transition-colors hover:border-brand-600 disabled:opacity-50" :disabled="searching" @click="search">
                <Search class="h-4 w-4" /> {{ searching ? 'Хайж байна…' : 'Хаягаар олох' }}
            </button>
            <button v-if="lat && lng" type="button" class="inline-flex h-9 items-center gap-1.5 rounded-md px-2 text-sm text-brand-500 hover:text-brand-600" @click="clear">
                <X class="h-4 w-4" /> Арилгах
            </button>
            <span v-if="lat && lng" class="tabular inline-flex items-center gap-1 font-mono text-xs text-brand-500">
                <MapPin class="h-3.5 w-3.5" /> {{ Number(lat).toFixed(5) }}, {{ Number(lng).toFixed(5) }}
            </span>
        </div>
        <div ref="el" class="h-60 overflow-hidden rounded-md border border-brand-200" />
        <p class="text-xs text-brand-400">{{ message || 'Газрын зураг дээр дарж байршлаа нарийвчлан тэмдэглэж болно. Заавал биш.' }}</p>
    </div>
</template>
