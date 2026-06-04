<script setup>
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
import { computed } from 'vue';

const props = defineProps({
    ride: { type: Object, default: null },
    countries: { type: Array, default: () => [] },
});

const isEdit = computed(() => !!props.ride);

const form = useForm({
    _method: isEdit.value ? 'put' : 'post',
    from_city: props.ride?.from_city ?? '',
    from_country: props.ride?.from_country ?? '',
    to_city: props.ride?.to_city ?? '',
    to_country: props.ride?.to_country ?? '',
    depart_at: props.ride?.depart_at ?? '',
    seats: props.ride?.seats ?? 1,
    price: props.ride?.price ?? '',
    notes: props.ride?.notes ?? '',
    contact_phone: props.ride?.contact_phone ?? '',
});

function model(field) {
    return computed({
        get: () => form[field] || 'none',
        set: (v) => { form[field] = v === 'none' ? '' : v; },
    });
}
const fromCountryModel = model('from_country');
const toCountryModel = model('to_country');

function submit() {
    if (isEdit.value) form.post(`/rides/${props.ride.id}`);
    else form.post('/rides');
}
</script>

<template>
    <Head :title="isEdit ? 'Аялал засах' : 'Аяллын зар нэмэх'" />

    <PublicLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Аялал засах' : 'Аяллын зар нэмэх' }}</h1>

            <form class="mt-6 space-y-5" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label>Хаанаас (хот)</Label>
                        <Input v-model="form.from_city" type="text" placeholder="Берлин" />
                        <p v-if="form.errors.from_city" class="text-sm text-destructive">{{ form.errors.from_city }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Улс</Label>
                        <Select v-model="fromCountryModel">
                            <SelectTrigger><SelectValue placeholder="— Сонгох —" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">— Сонгох —</SelectItem>
                                <SelectItem v-for="c in countries" :key="c" :value="c">{{ c }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Хаашаа (хот)</Label>
                        <Input v-model="form.to_city" type="text" placeholder="Мюнхен" />
                        <p v-if="form.errors.to_city" class="text-sm text-destructive">{{ form.errors.to_city }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Улс</Label>
                        <Select v-model="toCountryModel">
                            <SelectTrigger><SelectValue placeholder="— Сонгох —" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">— Сонгох —</SelectItem>
                                <SelectItem v-for="c in countries" :key="c" :value="c">{{ c }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="space-y-1.5">
                        <Label>Хөдлөх огноо, цаг</Label>
                        <Input v-model="form.depart_at" type="datetime-local" />
                        <p v-if="form.errors.depart_at" class="text-sm text-destructive">{{ form.errors.depart_at }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Сул суудал</Label>
                        <Input v-model.number="form.seats" type="number" min="1" max="8" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Нэг суудлын үнэ</Label>
                        <Input v-model="form.price" type="text" placeholder="€20" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label>Нэмэлт мэдээлэл</Label>
                    <Textarea v-model="form.notes" rows="3" placeholder="Цуглах цэг, ачаа тээш, тамхи татдаг эсэх г.м" />
                </div>

                <div class="space-y-1.5">
                    <Label>Холбоо барих утас (нэвтэрсэн хүнд харагдана)</Label>
                    <Input v-model="form.contact_phone" type="text" placeholder="+49 ..." />
                </div>

                <div class="flex gap-3">
                    <Button type="submit" :disabled="form.processing">{{ isEdit ? 'Хадгалах' : 'Нийтлэх' }}</Button>
                    <Button as="a" href="/rides" variant="secondary">Цуцлах</Button>
                </div>
            </form>
        </div>
    </PublicLayout>
</template>
