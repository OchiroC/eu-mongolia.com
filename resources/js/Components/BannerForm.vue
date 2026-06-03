<script setup>
import Button from '@/Components/ui/Button.vue';
import Checkbox from '@/Components/ui/Checkbox.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import Select from '@/Components/ui/Select.vue';
import SelectContent from '@/Components/ui/SelectContent.vue';
import SelectItem from '@/Components/ui/SelectItem.vue';
import SelectTrigger from '@/Components/ui/SelectTrigger.vue';
import SelectValue from '@/Components/ui/SelectValue.vue';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    banner: { type: Object, default: null },
    advertisers: { type: Array, default: () => [] },
    submitUrl: String,
    method: { type: String, default: 'post' },
});

const placements = [
    { value: 'home_top', label: 'Нүүр — дээд (өргөн)' },
    { value: 'home_sidebar', label: 'Нүүр — хажуу' },
    { value: 'news_top', label: 'Мэдээ — дээд' },
    { value: 'footer', label: 'Хөл хэсэг' },
];

const form = useForm({
    title: props.banner?.title ?? '',
    advertiser_id: props.banner?.advertiser_id ?? null,
    image_path: props.banner?.image_path ?? '',
    link_url: props.banner?.link_url ?? '',
    placement: props.banner?.placement ?? 'home_top',
    status: props.banner?.status ?? 'pending',
    price: props.banner?.price ?? 0,
    is_paid: props.banner?.is_paid ?? false,
    starts_at: props.banner?.starts_at ? String(props.banner.starts_at).slice(0, 10) : '',
    ends_at: props.banner?.ends_at ? String(props.banner.ends_at).slice(0, 10) : '',
    sort_order: props.banner?.sort_order ?? 0,
});

// Зар сурталчлагч сонгоогүй (null) бол 'none' sentinel.
const advertiserModel = computed({
    get: () => form.advertiser_id ?? 'none',
    set: (v) => { form.advertiser_id = v === 'none' ? null : v; },
});

function submit() {
    const opts = { preserveScroll: true };
    props.method === 'put' ? form.put(props.submitUrl, opts) : form.post(props.submitUrl, opts);
}
</script>

<template>
    <form class="max-w-2xl space-y-5" @submit.prevent="submit">
        <div class="space-y-1.5">
            <Label>Гарчиг</Label>
            <Input v-model="form.title" type="text" />
            <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
        </div>

        <div class="space-y-1.5">
            <Label>Зургийн URL</Label>
            <Input v-model="form.image_path" type="text" placeholder="https://..." />
            <p v-if="form.errors.image_path" class="text-sm text-destructive">{{ form.errors.image_path }}</p>
            <img v-if="form.image_path" :src="form.image_path" alt="" class="mt-2 max-h-32 rounded-md ring-1 ring-gray-200" />
        </div>

        <div class="space-y-1.5">
            <Label>Холбоос (дарахад очих)</Label>
            <Input v-model="form.link_url" type="text" placeholder="https://..." />
            <p v-if="form.errors.link_url" class="text-sm text-destructive">{{ form.errors.link_url }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1.5">
                <Label>Байршил</Label>
                <Select v-model="form.placement">
                    <SelectTrigger><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="p in placements" :key="p.value" :value="p.value">{{ p.label }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>
            <div class="space-y-1.5">
                <Label>Зар сурталчлагч</Label>
                <Select v-model="advertiserModel">
                    <SelectTrigger><SelectValue placeholder="—" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">—</SelectItem>
                        <SelectItem v-for="a in advertisers" :key="a.id" :value="a.id">{{ a.name }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1.5">
                <Label>Эхлэх огноо</Label>
                <Input v-model="form.starts_at" type="date" />
            </div>
            <div class="space-y-1.5">
                <Label>Дуусах огноо</Label>
                <Input v-model="form.ends_at" type="date" />
                <p v-if="form.errors.ends_at" class="text-sm text-destructive">{{ form.errors.ends_at }}</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="space-y-1.5">
                <Label>Үнэ (EUR)</Label>
                <Input v-model="form.price" type="number" step="0.01" min="0" />
            </div>
            <div class="space-y-1.5">
                <Label>Эрэмбэ</Label>
                <Input v-model="form.sort_order" type="number" min="0" />
            </div>
            <div class="space-y-1.5">
                <Label>Төлөв</Label>
                <Select v-model="form.status">
                    <SelectTrigger><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="pending">Хүлээгдэж буй</SelectItem>
                        <SelectItem value="active">Идэвхтэй</SelectItem>
                        <SelectItem value="rejected">Татгалзсан</SelectItem>
                        <SelectItem value="expired">Хугацаа дууссан</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <label class="flex w-fit items-center gap-2 text-sm text-gray-700">
            <Checkbox v-model="form.is_paid" />
            Төлбөр төлөгдсөн
        </label>

        <div class="flex gap-3">
            <Button type="submit" :disabled="form.processing">Хадгалах</Button>
            <Button as="a" href="/admin/banners" variant="secondary">Цуцлах</Button>
        </div>
    </form>
</template>
