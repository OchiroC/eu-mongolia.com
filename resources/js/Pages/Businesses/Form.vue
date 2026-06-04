<script setup>
import ImageUpload from '@/Components/ImageUpload.vue';
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
    business: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
});

const isEdit = computed(() => !!props.business);

const form = useForm({
    _method: isEdit.value ? 'put' : 'post',
    name: props.business?.name ?? '',
    category: props.business?.category ?? 'restaurant',
    description: props.business?.description ?? '',
    city: props.business?.city ?? '',
    country: props.business?.country ?? '',
    address: props.business?.address ?? '',
    phone: props.business?.phone ?? '',
    email: props.business?.email ?? '',
    website: props.business?.website ?? '',
    facebook: props.business?.facebook ?? '',
    hours: props.business?.hours ?? '',
    photo: null,
    remove_photo: false,
});

const countryModel = computed({
    get: () => form.country || 'none',
    set: (v) => { form.country = v === 'none' ? '' : v; },
});

function submit() {
    if (isEdit.value) form.post(`/businesses/${props.business.id}`);
    else form.post('/businesses');
}
</script>

<template>
    <Head :title="isEdit ? 'Бизнес засах' : 'Бизнес нэмэх'" />

    <PublicLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Бизнес засах' : 'Бизнесээ нэмэх' }}</h1>
            <p class="mt-1 text-sm text-gray-500">Бөглөж илгээсний дараа админ шалгаж нийтэлнэ.</p>

            <form class="mt-6 space-y-5" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label>Нэр</Label>
                        <Input v-model="form.name" type="text" placeholder="жнь: Хаан Буузны газар" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Ангилал</Label>
                        <Select v-model="form.category">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in categories" :key="c.key" :value="c.key">{{ c.label }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="space-y-1.5">
                        <Label>Хот</Label>
                        <Input v-model="form.city" type="text" placeholder="Берлин" />
                        <p v-if="form.errors.city" class="text-sm text-destructive">{{ form.errors.city }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Улс</Label>
                        <Select v-model="countryModel">
                            <SelectTrigger><SelectValue placeholder="— Сонгох —" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">— Сонгох —</SelectItem>
                                <SelectItem v-for="c in countries" :key="c" :value="c">{{ c }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Ажиллах цаг</Label>
                        <Input v-model="form.hours" type="text" placeholder="Да–Ня 10:00–22:00" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label>Хаяг (газрын зурагт ашиглана)</Label>
                    <Input v-model="form.address" type="text" placeholder="Гудамж, дугаар, шуудангийн код" />
                </div>

                <div class="space-y-1.5">
                    <Label>Танилцуулга</Label>
                    <Textarea v-model="form.description" rows="4" placeholder="Юу санал болгодог, онцлог..." />
                </div>

                <div class="space-y-1.5">
                    <Label>Зураг / Лого</Label>
                    <ImageUpload v-model:file="form.photo" v-model:remove="form.remove_photo" :current="business?.photo" label="Зураг" />
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-4">
                    <p class="mb-3 text-sm font-medium text-gray-700">Холбоо барих</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5"><Label>Утас</Label><Input v-model="form.phone" type="text" /></div>
                        <div class="space-y-1.5"><Label>И-мэйл</Label><Input v-model="form.email" type="email" /></div>
                        <div class="space-y-1.5"><Label>Вэбсайт</Label><Input v-model="form.website" type="text" placeholder="https://" /></div>
                        <div class="space-y-1.5"><Label>Facebook</Label><Input v-model="form.facebook" type="text" /></div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <Button type="submit" :disabled="form.processing">{{ isEdit ? 'Хадгалах' : 'Илгээх' }}</Button>
                    <Button as="a" href="/businesses" variant="secondary">Цуцлах</Button>
                </div>
            </form>
        </div>
    </PublicLayout>
</template>
