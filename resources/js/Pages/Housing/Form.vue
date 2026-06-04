<script setup>
import MultiImageUpload from '@/Components/MultiImageUpload.vue';
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
import { computed, ref } from 'vue';

const props = defineProps({
    post: { type: Object, default: null },
    types: { type: Array, default: () => [] },
    genders: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
});

const isEdit = computed(() => !!props.post);

const form = useForm({
    _method: isEdit.value ? 'put' : 'post',
    title: props.post?.title ?? '',
    type: props.post?.type ?? 'room',
    city: props.post?.city ?? '',
    country: props.post?.country ?? '',
    district: props.post?.district ?? '',
    price: props.post?.price ?? null,
    deposit: props.post?.deposit ?? null,
    rooms: props.post?.rooms ?? '',
    size: props.post?.size ?? null,
    available_from: props.post?.available_from ?? '',
    furnished: props.post?.furnished ?? false,
    gender_pref: props.post?.gender_pref ?? 'any',
    description: props.post?.description ?? '',
    contact_phone: props.post?.contact_phone ?? '',
    image_order: [],
    new_images: [],
});

const imageItems = ref((props.post?.images ?? []).map((url) => ({ url, file: null, preview: null })));

const countryModel = computed({
    get: () => form.country || 'none',
    set: (v) => { form.country = v === 'none' ? '' : v; },
});

function submit() {
    const files = [];
    form.image_order = imageItems.value.map((it) => {
        if (it.file) { const idx = files.length; files.push(it.file); return 'new:' + idx; }
        return it.url;
    });
    form.new_images = files;

    if (isEdit.value) form.post(`/housing/${props.post.id}`);
    else form.post('/housing');
}
</script>

<template>
    <Head :title="isEdit ? 'Орон сууц засах' : 'Орон сууцны зар нэмэх'" />

    <PublicLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Зар засах' : 'Орон сууцны зар нэмэх' }}</h1>

            <form class="mt-6 space-y-5" @submit.prevent="submit">
                <div class="space-y-1.5">
                    <Label>Гарчиг</Label>
                    <Input v-model="form.title" type="text" placeholder="жнь: Берлинд гэрэлтэй өрөө түрээслүүлнэ" />
                    <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label>Төрөл</Label>
                        <Select v-model="form.type">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="t in types" :key="t.key" :value="t.key">{{ t.label }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Хүйсийн сонголт</Label>
                        <Select v-model="form.gender_pref">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="g in genders" :key="g.key" :value="g.key">{{ g.label }}</SelectItem>
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
                        <Label>Дүүрэг / Хороолол</Label>
                        <Input v-model="form.district" type="text" />
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
                </div>

                <div class="grid gap-4 sm:grid-cols-4">
                    <div class="space-y-1.5">
                        <Label>Үнэ (€/сар)</Label>
                        <Input v-model.number="form.price" type="number" min="0" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Барьцаа (€)</Label>
                        <Input v-model.number="form.deposit" type="number" min="0" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Өрөө</Label>
                        <Input v-model="form.rooms" type="text" placeholder="2" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Талбай (м²)</Label>
                        <Input v-model.number="form.size" type="number" min="0" />
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-6">
                    <div class="space-y-1.5">
                        <Label>Орох боломжтой огноо</Label>
                        <Input v-model="form.available_from" type="date" />
                    </div>
                    <label class="mt-5 flex items-center gap-2 text-sm text-gray-700">
                        <input v-model="form.furnished" type="checkbox" class="rounded border-gray-300 text-primary focus:ring-ring" />
                        Тавилгатай
                    </label>
                </div>

                <div class="space-y-1.5">
                    <Label>Тайлбар</Label>
                    <Textarea v-model="form.description" rows="4" placeholder="Нөхцөл, ойролцоо орчин, дүрэм журам..." />
                </div>

                <div class="space-y-1.5">
                    <Label>Зураг</Label>
                    <MultiImageUpload v-model:items="imageItems" :max="10" />
                    <p v-if="form.errors.new_images || form.errors.image_order" class="text-sm text-destructive">{{ form.errors.new_images || form.errors.image_order }}</p>
                </div>

                <div class="space-y-1.5">
                    <Label>Холбоо барих утас (нэвтэрсэн хүнд харагдана)</Label>
                    <Input v-model="form.contact_phone" type="text" placeholder="+49 ..." />
                </div>

                <div class="flex gap-3">
                    <Button type="submit" :disabled="form.processing">{{ isEdit ? 'Хадгалах' : 'Нийтлэх' }}</Button>
                    <Button as="a" href="/housing" variant="secondary">Цуцлах</Button>
                </div>
            </form>
        </div>
    </PublicLayout>
</template>
