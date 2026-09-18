<script setup>
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const props = defineProps({
    schedules: { type: Array, default: () => [] },
    flights: { type: Array, default: () => [] },
    weekdays: { type: Object, default: () => ({}) },
    statuses: { type: Object, default: () => ({}) },
});

const blank = () => ({ code: 'OM137', direction: 'arrival', origin: 'UBN', destination: 'FRA', weekdays: [], local_time: '12:50', valid_from: new Date().toISOString().slice(0, 10), valid_to: '' });
const editingId = ref(null);
const form = useForm(blank());

function edit(s) {
    editingId.value = s.id;
    form.defaults({ ...s, valid_to: s.valid_to ?? '' });
    form.reset();
}
function cancel() {
    editingId.value = null;
    form.defaults(blank());
    form.reset();
}
function toggleDay(d) {
    const n = Number(d);
    form.weekdays = form.weekdays.includes(n) ? form.weekdays.filter((x) => x !== n) : [...form.weekdays, n].sort();
}
function save() {
    const opts = { preserveScroll: true, onSuccess: cancel };
    if (editingId.value) form.put(`/admin/flights/schedules/${editingId.value}`, opts);
    else form.post('/admin/flights/schedules', opts);
}
function removeSchedule(s) {
    if (confirm(`${s.code} хуваарийг устгах уу? Хэн ч холбогдоогүй ирээдүйн нислэгүүд устана.`)) {
        router.delete(`/admin/flights/schedules/${s.id}`, { preserveScroll: true });
    }
}

// Нислэг бүрийн засварыг тусад нь хадгална.
const rows = reactive(Object.fromEntries(props.flights.map((f) => [f.slug, { status: f.status, local: f.local, note: f.note ?? '' }])));
function saveFlight(f) {
    router.put(`/admin/flights/${f.slug}`, rows[f.slug], { preserveScroll: true });
}
function generate() {
    router.post('/admin/flights/generate', {}, { preserveScroll: true });
}
const dayNames = (days) => days.map((d) => props.weekdays[d]).join(', ');
</script>

<template>
    <Head title="Нислэгийн хуваарь" />

    <AdminLayout>
        <template #title>Нислэгийн хуваарь</template>

        <p class="mb-6 border border-l-[3px] border-amber-200 border-l-amber-500 bg-amber-50/60 px-4 py-3 text-sm text-amber-950">
            МИАТ-ын хуваарь улирлаар өөрчлөгддөг. Дүрмийг МИАТ-ын албан ёсны хуваариар шалгаж засаарай. Цагийг Франкфуртын цагаар оруулна.
        </p>

        <div class="grid gap-6 xl:grid-cols-5">
            <section class="rounded-md border border-brand-100 bg-white xl:col-span-3">
                <div class="flex items-center justify-between border-b border-brand-100 px-5 py-4">
                    <h2 class="font-semibold">Давтамжийн дүрэм</h2>
                    <Button variant="outline" size="sm" @click="generate">Нислэг үүсгэх</Button>
                </div>
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase text-gray-500">
                        <tr class="border-b border-brand-100"><th class="px-5 py-2.5">Нислэг</th><th class="px-3 py-2.5">Өдрүүд</th><th class="px-3 py-2.5">Цаг</th><th class="px-3 py-2.5">Хүчинтэй</th><th /></tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in schedules" :key="s.id" class="border-b border-brand-100 last:border-b-0">
                            <td class="px-5 py-3 font-mono">{{ s.code }} <span class="text-gray-400">{{ s.origin }} → {{ s.destination }}</span></td>
                            <td class="px-3 py-3 text-gray-600">{{ dayNames(s.weekdays) }}</td>
                            <td class="tabular px-3 py-3 font-mono">{{ s.local_time }}</td>
                            <td class="tabular px-3 py-3 font-mono text-xs text-gray-500">{{ s.valid_from }}<template v-if="s.valid_to"> / {{ s.valid_to }}</template></td>
                            <td class="px-3 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button variant="outline" size="sm" @click="edit(s)">Засах</Button>
                                    <Button variant="destructive" size="sm" @click="removeSchedule(s)">Устгах</Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!schedules.length"><td colspan="5" class="px-5 py-8 text-center text-gray-400">Хуваарь алга.</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="rounded-md border border-brand-100 bg-white p-5 xl:col-span-2">
                <h2 class="font-semibold">{{ editingId ? 'Дүрэм засах' : 'Шинэ дүрэм' }}</h2>
                <form class="mt-4 space-y-4" @submit.prevent="save">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5"><Label>Нислэгийн дугаар</Label><Input v-model="form.code" /></div>
                        <div class="space-y-1.5">
                            <Label>Чиглэл</Label>
                            <select v-model="form.direction" class="h-10 w-full rounded-md border-brand-200 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600">
                                <option value="arrival">Франкфуртад ирэх</option>
                                <option value="departure">Франкфуртаас хөдлөх</option>
                            </select>
                        </div>
                        <div class="space-y-1.5"><Label>Хаанаас</Label><Input v-model="form.origin" maxlength="3" /></div>
                        <div class="space-y-1.5"><Label>Хаашаа</Label><Input v-model="form.destination" maxlength="3" /></div>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Нисэх өдрүүд</Label>
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="(label, d) in weekdays"
                                :key="d"
                                type="button"
                                class="h-9 w-10 rounded-md border text-sm transition-colors"
                                :class="form.weekdays.includes(Number(d)) ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200 text-brand-500 hover:border-brand-600'"
                                @click="toggleDay(d)"
                            >{{ label }}</button>
                        </div>
                        <p v-if="form.errors.weekdays" class="text-sm text-destructive">{{ form.errors.weekdays }}</p>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="space-y-1.5"><Label>Цаг</Label><Input v-model="form.local_time" type="time" /></div>
                        <div class="space-y-1.5"><Label>Эхлэх</Label><Input v-model="form.valid_from" type="date" /></div>
                        <div class="space-y-1.5"><Label>Дуусах</Label><Input v-model="form.valid_to" type="date" /></div>
                    </div>
                    <p v-for="(e, k) in form.errors" :key="k" class="text-sm text-destructive">{{ e }}</p>
                    <div class="flex gap-2">
                        <Button type="submit" :disabled="form.processing">{{ editingId ? 'Хадгалах' : 'Нэмэх' }}</Button>
                        <Button v-if="editingId" type="button" variant="outline" @click="cancel">Болих</Button>
                    </div>
                </form>
            </section>
        </div>

        <section class="mt-6 overflow-x-auto rounded-md border border-brand-100 bg-white">
            <h2 class="border-b border-brand-100 px-5 py-4 font-semibold">Ойрын нислэгүүд</h2>
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase text-gray-500">
                    <tr class="border-b border-brand-100"><th class="px-5 py-2.5">Нислэг</th><th class="px-3 py-2.5">Франкфуртын цаг</th><th class="px-3 py-2.5">Төлөв</th><th class="px-3 py-2.5">Тайлбар</th><th class="px-3 py-2.5">Хүн</th><th /></tr>
                </thead>
                <tbody>
                    <tr v-for="f in flights" :key="f.slug" class="border-b border-brand-100 last:border-b-0">
                        <td class="whitespace-nowrap px-5 py-2.5 font-mono">
                            <a :href="`/flights/${f.slug}`" target="_blank" class="hover:underline">{{ f.code }}</a>
                            <span class="text-gray-400"> {{ f.weekday }}</span>
                        </td>
                        <td class="px-3 py-2.5"><input v-model="rows[f.slug].local" type="datetime-local" class="h-9 rounded-md border-brand-200 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600" /></td>
                        <td class="px-3 py-2.5">
                            <select v-model="rows[f.slug].status" class="h-9 rounded-md border-brand-200 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600">
                                <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </td>
                        <td class="px-3 py-2.5"><input v-model="rows[f.slug].note" type="text" placeholder="Жишээ нь 2 цаг хойшилсон" class="h-9 w-56 rounded-md border-brand-200 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600" /></td>
                        <td class="tabular px-3 py-2.5 font-mono text-gray-500">{{ f.passengers_count }}</td>
                        <td class="px-3 py-2.5 text-right"><Button size="sm" variant="outline" @click="saveFlight(f)">Хадгалах</Button></td>
                    </tr>
                    <tr v-if="!flights.length"><td colspan="6" class="px-5 py-8 text-center text-gray-400">Ойрын нислэг алга. Дүрэм нэмээд "Нислэг үүсгэх" дарна уу.</td></tr>
                </tbody>
            </table>
        </section>
    </AdminLayout>
</template>
