<script setup>
// Улаанбаатарын аялал жуулчлалын агентлаг, тасалбарын газарт тараах QR карт (A4 хуудсанд 4 ширхэг, A6 хэмжээтэй).
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import { onMounted, ref, watch } from 'vue';

const name = import.meta.env.VITE_APP_NAME || 'OM137';
const url = ref('https://om137.de/?ref=card');
const qr = ref('');

async function render() {
    qr.value = await QRCode.toDataURL(url.value, { margin: 0, width: 360, color: { dark: '#0b0c0e', light: '#ffffff' } });
}
onMounted(render);
watch(url, render);

function printPage() {
    window.print();
}

const domain = () => url.value.replace(/^https?:\/\//, '').split(/[/?#]/)[0];
</script>

<template>
    <Head title="Сурталчилгааны карт" />

    <AdminLayout>
        <template #title>Сурталчилгааны карт</template>

        <div class="no-print mb-6 flex flex-col gap-4 rounded-md border border-brand-100 bg-white p-5 sm:flex-row sm:items-end">
            <div class="flex-1 space-y-1.5">
                <Label>QR кодын холбоос</Label>
                <Input v-model="url" type="url" />
                <p class="text-xs text-gray-400">"?ref=card" нь хэдэн хүн картаар орж ирснийг ялгахад хэрэгтэй.</p>
            </div>
            <Button @click="printPage">Хэвлэх</Button>
        </div>

        <div class="print-sheet grid gap-4 sm:grid-cols-2">
            <div v-for="n in 4" :key="n" class="print-card flex flex-col justify-between border border-brand-200 bg-white p-6">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex gap-0.5">
                            <span v-for="c in ['O', 'M']" :key="c" class="logo-tile h-[26px] w-[18px] font-mono text-base font-semibold">{{ c }}</span>
                            <span v-for="d in ['1', '3', '7']" :key="d" class="logo-tile logo-tile--digit h-[26px] w-[18px] font-mono text-base font-semibold">{{ d }}</span>
                        </span>
                    </div>
                    <p class="mt-5 text-xl font-semibold leading-snug tracking-tight">Франкфурт руу нисэх гэж байна уу?</p>
                    <ul class="mt-3 space-y-1 text-sm text-gray-600">
                        <li>Виз, ачаа, гаалийн дүрэм</li>
                        <li>Нисэх буудлаас хот руу хэрхэн очих</li>
                        <li>Угтах хүн, хамт явах машин</li>
                        <li>Байр, ажил, хотын бүртгэл</li>
                    </ul>
                </div>
                <div class="mt-5 flex items-end justify-between gap-4">
                    <p class="font-mono text-sm font-semibold">{{ domain() }}</p>
                    <img v-if="qr" :src="qr" :alt="`${name} QR код`" class="h-24 w-24" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.print-card { aspect-ratio: 148 / 105; }
@media print {
    :global(aside), :global(header), .no-print { display: none !important; }
    .print-sheet { gap: 0; }
    .print-card { break-inside: avoid; border-style: dashed; }
}
</style>
