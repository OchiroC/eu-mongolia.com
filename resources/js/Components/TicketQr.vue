<script setup>
import QRCode from 'qrcode';
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
    value: { type: String, required: true },
    size: { type: Number, default: 160 },
});

const dataUrl = ref('');

async function render() {
    dataUrl.value = await QRCode.toDataURL(props.value, {
        width: props.size,
        margin: 1,
    });
}

onMounted(render);
watch(() => props.value, render);
</script>

<template>
    <img v-if="dataUrl" :src="dataUrl" :width="size" :height="size" alt="QR" class="rounded" />
</template>
