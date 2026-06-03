<script setup>
import ImageUpload from '@/components/ImageUpload.vue';
import MultiImageUpload from '@/components/MultiImageUpload.vue';
import RichTextEditor from '@/components/RichTextEditor.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import SelectContent from '@/components/ui/SelectContent.vue';
import SelectItem from '@/components/ui/SelectItem.vue';
import SelectTrigger from '@/components/ui/SelectTrigger.vue';
import SelectValue from '@/components/ui/SelectValue.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { uploadInlineImages } from '@/lib/uploadInlineImages';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    post: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    tags: { type: Array, default: () => [] },
    submitUrl: String,
    method: { type: String, default: 'post' },
});

const form = useForm({
    _method: props.method,
    title: props.post?.title ?? '',
    news_category_id: props.post?.news_category_id ?? null,
    excerpt: props.post?.excerpt ?? '',
    body: props.post?.body ?? '',
    cover: null,
    remove_cover: false,
    country: props.post?.country ?? '',
    is_featured: props.post?.is_featured ?? false,
    comments_enabled: props.post?.comments_enabled ?? true,
    status: props.post?.status ?? 'draft',
    tags: [...(props.tags ?? [])],
    gallery_order: [],
    gallery_files: [],
});

// Галерейн нэгдсэн жагсаалт (хуучин URL + шинэ File). UI энэ дээр ажиллана.
const galleryItems = ref((props.post?.gallery ?? []).map((url) => ({ url, file: null, preview: null })));

// Таг оруулах
const tagInput = ref('');
function addTag() {
    const v = tagInput.value.trim().replace(/,+$/, '').trim();
    if (v && form.tags.length < 15 && !form.tags.includes(v)) {
        form.tags.push(v);
    }
    tagInput.value = '';
}
function onTagKey(e) {
    if (e.key === ',') {
        e.preventDefault();
        addTag();
    } else if (e.key === 'Backspace' && !tagInput.value && form.tags.length) {
        form.tags.pop();
    }
}
function removeTag(i) {
    form.tags.splice(i, 1);
}

const countries = ['Герман', 'Чех', 'Польш', 'Унгар', 'Австри', 'Франц', 'Бельги', 'Голланд', 'Швед', 'Итали', 'Бусад'];

// shadcn Select-д хоосон/null утгыг 'none' sentinel-ээр илэрхийлнэ.
const categoryModel = computed({
    get: () => form.news_category_id ?? 'none',
    set: (v) => { form.news_category_id = v === 'none' ? null : v; },
});
const countryModel = computed({
    get: () => form.country || 'none',
    set: (v) => { form.country = v === 'none' ? '' : v; },
});

const submitting = ref(false);

async function submit() {
    // Галерейн эрэмбэ + шинэ файлуудыг бүрдүүлнэ.
    const files = [];
    form.gallery_order = galleryItems.value.map((it) => {
        if (it.file) {
            const idx = files.length;
            files.push(it.file);
            return 'new:' + idx;
        }
        return it.url;
    });
    form.gallery_files = files;

    // Агуулгад түр суусан зургуудыг (data URL) одоо upload хийж, URL-аар солино.
    submitting.value = true;
    try {
        form.body = await uploadInlineImages(form.body);
    } catch {
        submitting.value = false;
        window.alert('Агуулгын зураг оруулахад алдаа гарлаа. Дахин оролдоно уу.');
        return;
    }
    submitting.value = false;

    // Файл upload-той тул бүх тохиолдолд POST + _method spoofing ашиглана.
    form.post(props.submitUrl);
}
</script>

<template>
    <form class="max-w-2xl space-y-5" @submit.prevent="submit">
        <div class="space-y-1.5">
            <Label>Гарчиг</Label>
            <Input v-model="form.title" type="text" />
            <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1.5">
                <Label>Ангилал</Label>
                <Select v-model="categoryModel">
                    <SelectTrigger><SelectValue placeholder="— Сонгох —" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">— Сонгох —</SelectItem>
                        <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id">
                            <span :class="cat.depth ? 'text-gray-500' : ''">{{ cat.depth ? '↳ ' : '' }}{{ cat.name }}</span>
                        </SelectItem>
                    </SelectContent>
                </Select>
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

        <div class="space-y-1.5">
            <Label>Тойм (excerpt)</Label>
            <Textarea v-model="form.excerpt" rows="2" />
            <p v-if="form.errors.excerpt" class="text-sm text-destructive">{{ form.errors.excerpt }}</p>
        </div>

        <div class="space-y-1.5">
            <Label>Үндсэн текст</Label>
            <RichTextEditor v-model="form.body" placeholder="Мэдээний агуулгаа бичнэ үү…" />
            <p v-if="form.errors.body" class="text-sm text-destructive">{{ form.errors.body }}</p>
        </div>

        <div class="space-y-1.5">
            <Label>Таг (шошго)</Label>
            <div class="flex flex-wrap items-center gap-1.5 rounded-md border border-input bg-background p-2 ring-offset-background focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2">
                <span v-for="(t, i) in form.tags" :key="i" class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-0.5 text-sm text-brand-700">
                    {{ t }}
                    <button type="button" class="text-brand-400 transition hover:text-brand-700" @click="removeTag(i)">✕</button>
                </span>
                <input
                    v-model="tagInput"
                    type="text"
                    :placeholder="form.tags.length ? '' : 'Таг бичээд Enter...'"
                    class="min-w-[120px] flex-1 border-0 bg-transparent p-0.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-0"
                    @keydown.enter.prevent="addTag"
                    @keydown="onTagKey"
                />
            </div>
            <p class="text-xs text-gray-400">Enter эсвэл таслалаар нэмнэ · {{ form.tags.length }}/15</p>
            <p v-if="form.errors.tags" class="text-sm text-destructive">{{ form.errors.tags }}</p>
        </div>

        <div class="space-y-1.5">
            <Label>Нүүр зураг</Label>
            <ImageUpload v-model:file="form.cover" v-model:remove="form.remove_cover" :current="post?.cover_image" label="Нүүр зураг" />
            <p v-if="form.errors.cover" class="text-sm text-destructive">{{ form.errors.cover }}</p>
        </div>

        <div class="space-y-1.5">
            <Label>Зургийн галерей</Label>
            <MultiImageUpload v-model:items="galleryItems" :max="12" />
            <p v-if="form.errors.gallery_files || form.errors.gallery_order" class="text-sm text-destructive">{{ form.errors.gallery_files || form.errors.gallery_order }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-6">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.is_featured" type="checkbox" class="rounded border-gray-300 text-primary focus:ring-ring" />
                Онцлох
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.comments_enabled" type="checkbox" class="rounded border-gray-300 text-primary focus:ring-ring" />
                Сэтгэгдэл бичихийг зөвшөөрөх
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
            <Button as="a" href="/admin/posts" variant="secondary">Цуцлах</Button>
        </div>
    </form>
</template>
