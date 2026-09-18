<script setup>
import CustomsNotice from '@/Components/CustomsNotice.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import Select from '@/Components/ui/Select.vue';
import SelectContent from '@/Components/ui/SelectContent.vue';
import SelectItem from '@/Components/ui/SelectItem.vue';
import SelectTrigger from '@/Components/ui/SelectTrigger.vue';
import SelectValue from '@/Components/ui/SelectValue.vue';
import Textarea from '@/Components/ui/Textarea.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    parcel: { type: Object, default: null },
    preset: { type: Object, default: null },
    types: Object,
    directions: Object,
    flights: { type: Array, default: () => [] },
});

const isEdit = computed(() => !!props.parcel);
const presetFlight = props.flights.find((f) => f.id === props.preset?.flight_id);

const form = useForm({
    _method: isEdit.value ? 'put' : 'post',
    type: props.parcel?.type ?? props.preset?.type ?? 'offer',
    direction: props.parcel?.direction ?? (presetFlight?.direction === 'departure' ? 'to_mongolia' : 'to_germany'),
    flight_id: props.parcel?.flight_id ?? props.preset?.flight_id ?? '',
    travel_date: props.parcel?.travel_date ?? '',
    from_city: props.parcel?.from_city ?? '',
    to_city: props.parcel?.to_city ?? '',
    weight_kg: props.parcel?.weight_kg ?? '',
    price: props.parcel?.price ?? '',
    description: props.parcel?.description ?? '',
    contact_phone: props.parcel?.contact_phone ?? '',
});

// Чиглэлд тохирох нислэг: Германд ирэх бол OM137 (arrival), Монгол руу бол OM138 (departure).
const flightOptions = computed(() => props.flights.filter((f) => f.direction === (form.direction === 'to_germany' ? 'arrival' : 'departure')));
watch(() => form.direction, () => {
    if (form.flight_id && !flightOptions.value.some((f) => f.id === form.flight_id)) form.flight_id = '';
});
const flightModel = computed({
    get: () => (form.flight_id ? String(form.flight_id) : 'none'),
    set: (v) => { form.flight_id = v === 'none' ? '' : Number(v); },
});

const seg = (on) => (on ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200 text-brand-500 hover:border-brand-600 hover:text-brand-600');

function submit() {
    form.post(isEdit.value ? `/achaa/${props.parcel.id}` : '/achaa');
}
</script>

<template>
    <Head :title="isEdit ? 'Ачааны зар засах' : 'Ачааны зар нэмэх'" />

    <PublicLayout>
        <div class="mx-auto max-w-2xl">
            <p class="kicker">Ачаа</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-brand-600">{{ isEdit ? 'Ачааны зар засах' : 'Ачааны зар нэмэх' }}</h1>

            <div class="mt-6"><CustomsNotice /></div>

            <form class="mt-8 space-y-6" @submit.prevent="submit">
                <div class="space-y-2">
                    <Label>Зарын төрөл</Label>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <button v-for="(label, key) in types" :key="key" type="button" class="h-11 rounded-md border px-3 text-sm transition-colors" :class="seg(form.type === key)" @click="form.type = key">{{ label }}</button>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label>Чиглэл</Label>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <button v-for="(label, key) in directions" :key="key" type="button" class="h-11 rounded-md border px-3 text-sm transition-colors" :class="seg(form.direction === key)" @click="form.direction = key">{{ label }}</button>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label>Нислэг</Label>
                        <Select v-model="flightModel">
                            <SelectTrigger><SelectValue placeholder="Нислэг сонгоогүй" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">Нислэг сонгоогүй</SelectItem>
                                <SelectItem v-for="f in flightOptions" :key="f.id" :value="String(f.id)">{{ f.label }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div v-if="!form.flight_id" class="space-y-1.5">
                        <Label>Явах өдөр</Label>
                        <Input v-model="form.travel_date" type="date" />
                        <p v-if="form.errors.travel_date" class="text-sm text-destructive">{{ form.errors.travel_date }}</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label>Хаанаас (хот)</Label>
                        <Input v-model="form.from_city" type="text" placeholder="Улаанбаатар" />
                        <p v-if="form.errors.from_city" class="text-sm text-destructive">{{ form.errors.from_city }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Хаашаа (хот)</Label>
                        <Input v-model="form.to_city" type="text" placeholder="Франкфурт" />
                        <p v-if="form.errors.to_city" class="text-sm text-destructive">{{ form.errors.to_city }}</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label>{{ form.type === 'offer' ? 'Сул зай (кг)' : 'Ачааны жин (кг)' }}</Label>
                        <Input v-model="form.weight_kg" type="number" min="0" max="100" step="0.5" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Үнэ</Label>
                        <Input v-model="form.price" type="text" placeholder="Жишээ нь 5 €/кг, эсвэл Тохиролцоно" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label>Тайлбар</Label>
                    <Textarea v-model="form.description" rows="5" :placeholder="form.type === 'offer' ? 'Хэдэн кг, ямар төрлийн ачаа авч явах боломжтой, хаана хүлээлгэж өгөх вэ.' : 'Юу явуулах, хэмжээ, жин, хэзээ хүргэх шаардлагатай вэ.'" />
                    <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                </div>

                <div class="space-y-1.5">
                    <Label>Холбоо барих утас (нэвтэрсэн хэрэглэгчид харагдана)</Label>
                    <Input v-model="form.contact_phone" type="tel" placeholder="+49 ..." />
                </div>

                <Button type="submit" :disabled="form.processing">{{ isEdit ? 'Хадгалах' : 'Нийтлэх' }}</Button>
            </form>
        </div>
    </PublicLayout>
</template>
