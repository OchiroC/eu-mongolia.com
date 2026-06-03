<script setup>
import ImageUpload from '@/components/ImageUpload.vue';
import RichTextEditor from '@/components/RichTextEditor.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import SelectContent from '@/components/ui/SelectContent.vue';
import SelectItem from '@/components/ui/SelectItem.vue';
import SelectTrigger from '@/components/ui/SelectTrigger.vue';
import SelectValue from '@/components/ui/SelectValue.vue';
import { uploadInlineImages } from '@/lib/uploadInlineImages';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    event: { type: Object, default: null },
    submitUrl: String,
    method: { type: String, default: 'post' },
});

const countries = ['Герман', 'Чех', 'Польш', 'Унгар', 'Австри', 'Франц', 'Бельги', 'Голланд', 'Швед', 'Итали', 'Бусад'];

function toLocal(value) {
    if (!value) return '';
    return String(value).slice(0, 16).replace(' ', 'T');
}

const form = useForm({
    _method: props.method,
    title: props.event?.title ?? '',
    description: props.event?.description ?? '',
    cover: null,
    remove_cover: false,
    venue: props.event?.venue ?? '',
    city: props.event?.city ?? '',
    country: props.event?.country ?? '',
    starts_at: toLocal(props.event?.starts_at),
    ends_at: toLocal(props.event?.ends_at),
    status: props.event?.status ?? 'draft',
    is_featured: props.event?.is_featured ?? false,
    has_tickets: props.event?.has_tickets ?? true,
    ticket_types: props.event?.ticket_types?.length
        ? props.event.ticket_types.map((t) => ({ id: t.id, name: t.name, price: t.price, quantity: t.quantity, sold: t.sold }))
        : [{ id: null, name: 'Энгийн', price: 0, quantity: 100, sold: 0 }],
});

// shadcn Select-д хоосон утгыг 'none' sentinel-ээр илэрхийлнэ.
const countryModel = computed({
    get: () => form.country || 'none',
    set: (v) => { form.country = v === 'none' ? '' : v; },
});

function addType() {
    form.ticket_types.push({ id: null, name: '', price: 0, quantity: 0, sold: 0 });
}

function removeType(i) {
    form.ticket_types.splice(i, 1);
}

const submitting = ref(false);

async function submit() {
    // Тайлбарт түр суусан зургуудыг (data URL) одоо upload хийж, URL-аар солино.
    submitting.value = true;
    try {
        form.description = await uploadInlineImages(form.description);
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
    <form class="max-w-3xl space-y-5" @submit.prevent="submit">
        <div class="space-y-1.5">
            <Label>Эвентийн нэр</Label>
            <Input v-model="form.title" type="text" />
            <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
        </div>

        <div class="space-y-1.5">
            <Label>Тайлбар</Label>
            <RichTextEditor v-model="form.description" placeholder="Эвентийн тухай дэлгэрэнгүй…" />
        </div>

        <div class="space-y-1.5">
            <Label>Нүүр зураг</Label>
            <ImageUpload v-model:file="form.cover" v-model:remove="form.remove_cover" :current="event?.cover_image" label="Нүүр зураг" />
            <p v-if="form.errors.cover" class="text-sm text-destructive">{{ form.errors.cover }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="space-y-1.5">
                <Label>Газар (venue)</Label>
                <Input v-model="form.venue" type="text" />
            </div>
            <div class="space-y-1.5">
                <Label>Хот</Label>
                <Input v-model="form.city" type="text" />
            </div>
            <div class="space-y-1.5">
                <Label>Улс</Label>
                <Select v-model="countryModel">
                    <SelectTrigger><SelectValue placeholder="—" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">—</SelectItem>
                        <SelectItem v-for="c in countries" :key="c" :value="c">{{ c }}</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="space-y-1.5">
                <Label>Эхлэх</Label>
                <Input v-model="form.starts_at" type="datetime-local" />
                <p v-if="form.errors.starts_at" class="text-sm text-destructive">{{ form.errors.starts_at }}</p>
            </div>
            <div class="space-y-1.5">
                <Label>Дуусах</Label>
                <Input v-model="form.ends_at" type="datetime-local" />
            </div>
            <div class="space-y-1.5">
                <Label>Төлөв</Label>
                <Select v-model="form.status">
                    <SelectTrigger><SelectValue /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="draft">Ноорог</SelectItem>
                        <SelectItem value="published">Нийтлэх</SelectItem>
                        <SelectItem value="cancelled">Цуцлах</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <label class="flex w-fit items-center gap-2 text-sm text-gray-700">
            <input v-model="form.is_featured" type="checkbox" class="rounded border-gray-300 text-primary focus:ring-ring" />
            Онцлох (нүүр хуудсанд тэргүүлж харагдана)
        </label>

        <!-- Эвентийн төрөл: тасалбартай эсэх -->
        <div class="rounded-lg border border-gray-200 p-4">
            <label class="flex cursor-pointer items-start gap-3">
                <input v-model="form.has_tickets" type="checkbox" class="mt-0.5 rounded border-gray-300 text-primary focus:ring-ring" />
                <span>
                    <span class="block text-sm font-medium text-gray-800">Тасалбартай эвент</span>
                    <span class="block text-xs text-gray-500">Идэвхжүүлбэл тасалбар зарах систем холбогдоно. Үгүй бол зөвхөн мэдээллийн эвент болно.</span>
                </span>
            </label>
        </div>

        <div v-if="form.has_tickets" class="rounded-lg border border-gray-200 p-4">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-medium text-gray-800">Тасалбарын төрөл</h3>
                <Button type="button" variant="link" class="h-auto p-0" @click="addType">+ Төрөл нэмэх</Button>
            </div>

            <div class="space-y-3">
                <div v-for="(t, i) in form.ticket_types" :key="i" class="flex items-end gap-3">
                    <div class="flex-1 space-y-1">
                        <Label class="text-xs text-gray-500">Нэр</Label>
                        <Input v-model="t.name" type="text" placeholder="VIP" />
                    </div>
                    <div class="w-28 space-y-1">
                        <Label class="text-xs text-gray-500">Үнэ (€)</Label>
                        <Input v-model.number="t.price" type="number" step="0.01" min="0" />
                    </div>
                    <div class="w-28 space-y-1">
                        <Label class="text-xs text-gray-500">Тоо</Label>
                        <Input v-model.number="t.quantity" type="number" min="0" />
                    </div>
                    <Button type="button" variant="ghost" size="icon" class="text-destructive hover:text-destructive" :title="t.sold > 0 ? 'Зарагдсан тул устгахгүй' : 'Устгах'" @click="removeType(i)">✕</Button>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <Button type="submit" :disabled="form.processing || submitting">{{ submitting ? 'Зураг боловсруулж байна…' : 'Хадгалах' }}</Button>
            <Button as="a" href="/admin/events" variant="secondary">Цуцлах</Button>
        </div>
    </form>
</template>
