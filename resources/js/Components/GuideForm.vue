<script setup>
import ImageUpload from '@/Components/ImageUpload.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import Select from '@/Components/ui/Select.vue';
import SelectContent from '@/Components/ui/SelectContent.vue';
import SelectItem from '@/Components/ui/SelectItem.vue';
import SelectTrigger from '@/Components/ui/SelectTrigger.vue';
import SelectValue from '@/Components/ui/SelectValue.vue';
import Textarea from '@/Components/ui/Textarea.vue';
import { uploadInlineImages } from '@/lib/uploadInlineImages';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    guide: { type: Object, default: null },
    topics: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
    submitUrl: String,
    method: { type: String, default: 'post' },
});

const form = useForm({
    _method: props.method,
    title: props.guide?.title ?? '',
    excerpt: props.guide?.excerpt ?? '',
    body: props.guide?.body ?? '',
    cover: null,
    remove_cover: false,
    topic: props.guide?.topic ?? 'visa',
    country: props.guide?.country ?? '',
    is_featured: props.guide?.is_featured ?? false,
    status: props.guide?.status ?? 'draft',
});

const countryModel = computed({
    get: () => form.country || 'none',
    set: (v) => { form.country = v === 'none' ? '' : v; },
});

const submitting = ref(false);
async function submit() {
    submitting.value = true;
    try {
        form.body = await uploadInlineImages(form.body);
    } catch {
        submitting.value = false;
        window.alert('Агуулгын зураг оруулахад алдаа гарлаа.');
        return;
    }
    submitting.value = false;
    form.post(props.submitUrl);
}
</script>

<template>
    <form class="max-w-2xl space-y-5" @submit.prevent="submit">
        <div class="space-y-1.5">
            <Label>Гарчиг</Label>
            <Input v-model="form.title" type="text" placeholder="жнь: Германд ВНЖ хэрхэн сунгах вэ?" />
            <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1.5">
                <Label>Сэдэв</Label>
                <Select v-model="form.topic">
                    <SelectTrigger><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="t in topics" :key="t.key" :value="t.key">{{ t.label }}</SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.topic" class="text-sm text-destructive">{{ form.errors.topic }}</p>
            </div>
            <div class="space-y-1.5">
                <Label>Улс</Label>
                <Select v-model="countryModel">
                    <SelectTrigger><SelectValue placeholder="Бүх улс / Ерөнхий" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">Бүх улс / Ерөнхий</SelectItem>
                        <SelectItem v-for="c in countries" :key="c" :value="c">{{ c }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div class="space-y-1.5">
            <Label>Тойм (excerpt)</Label>
            <Textarea v-model="form.excerpt" rows="2" placeholder="Богино тайлбар (жагсаалт, хайлтад харагдана)" />
        </div>

        <div class="space-y-1.5">
            <Label>Үндсэн агуулга</Label>
            <RichTextEditor v-model="form.body" placeholder="Алхам алхмаар тайлбарла. Гарчиг (H2/H3), жагсаалт, зураг, холбоос ашиглаж болно." />
            <p v-if="form.errors.body" class="text-sm text-destructive">{{ form.errors.body }}</p>
        </div>

        <div class="space-y-1.5">
            <Label>Нүүр зураг</Label>
            <ImageUpload v-model:file="form.cover" v-model:remove="form.remove_cover" :current="guide?.cover_image" label="Нүүр зураг" />
        </div>

        <div class="flex flex-wrap items-center gap-6">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.is_featured" type="checkbox" class="rounded border-gray-300 text-primary focus:ring-ring" />
                Онцлох
            </label>
            <div class="flex items-center gap-2">
                <Label>Төлөв:</Label>
                <Select v-model="form.status">
                    <SelectTrigger class="h-9 w-40"><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="draft">Ноорог</SelectItem>
                        <SelectItem value="published">Нийтлэх</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div class="flex gap-3">
            <Button type="submit" :disabled="form.processing || submitting">{{ submitting ? 'Зураг боловсруулж байна…' : 'Хадгалах' }}</Button>
            <Button as="a" href="/admin/guides" variant="secondary">Цуцлах</Button>
        </div>
    </form>
</template>
