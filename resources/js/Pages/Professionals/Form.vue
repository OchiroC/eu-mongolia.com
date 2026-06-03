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
import Textarea from '@/components/ui/Textarea.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    professional: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    languageOptions: { type: Array, default: () => [] },
});

const isEdit = computed(() => !!props.professional);

const form = useForm({
    _method: isEdit.value ? 'put' : 'post',
    professional_category_id: props.professional?.professional_category_id ?? null,
    name: props.professional?.name ?? '',
    profession: props.professional?.profession ?? '',
    bio: props.professional?.bio ?? '',
    photo: null,
    remove_photo: false,
    city: props.professional?.city ?? '',
    country: props.professional?.country ?? '',
    languages: [...(props.professional?.languages ?? [])],
    services: props.professional?.services ?? '',
    phone: props.professional?.phone ?? '',
    email: props.professional?.email ?? '',
    website: props.professional?.website ?? '',
    facebook: props.professional?.facebook ?? '',
});

const categoryModel = computed({
    get: () => form.professional_category_id ?? 'none',
    set: (v) => { form.professional_category_id = v === 'none' ? null : v; },
});

function toggleLang(lang) {
    const i = form.languages.indexOf(lang);
    if (i === -1) form.languages.push(lang);
    else form.languages.splice(i, 1);
}

function submit() {
    if (isEdit.value) {
        form.post(`/professionals/${props.professional.id}`);
    } else {
        form.post('/professionals');
    }
}
</script>

<template>
    <Head :title="isEdit ? 'Профайл засах' : 'Мэргэжилтнээр бүртгүүлэх'" />

    <PublicLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Профайл засах' : 'Мэргэжилтнээр бүртгүүлэх' }}</h1>
            <p class="mt-1 text-sm text-gray-500">Бөглөж илгээсний дараа админ шалгаж баталгаажуулна. Баталгаажсаны дараа лавлахад нийтлэгдэнэ.</p>

            <form class="mt-6 space-y-5" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label>Нэр</Label>
                        <Input v-model="form.name" type="text" placeholder="Б. Болд эсвэл байгууллагын нэр" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Мэргэжил / Гарчиг</Label>
                        <Input v-model="form.profession" type="text" placeholder="жнь: Гэр бүлийн хуульч" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label>Ангилал</Label>
                    <Select v-model="categoryModel">
                        <SelectTrigger><SelectValue placeholder="— Сонгох —" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="none">— Сонгох —</SelectItem>
                            <SelectItem v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label>Хот</Label>
                        <Input v-model="form.city" type="text" placeholder="Берлин" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Улс</Label>
                        <Input v-model="form.country" type="text" placeholder="Герман" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label>Ярьдаг хэл</Label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="lang in languageOptions"
                            :key="lang"
                            type="button"
                            class="rounded-full px-3 py-1 text-sm transition"
                            :class="form.languages.includes(lang) ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            @click="toggleLang(lang)"
                        >{{ lang }}</button>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label>Танилцуулга</Label>
                    <RichTextEditor v-model="form.bio" placeholder="Туршлага, мэргэшил, боловсрол..." />
                </div>

                <div class="space-y-1.5">
                    <Label>Үйлчилгээ</Label>
                    <Textarea v-model="form.services" rows="3" placeholder="Үзүүлдэг үйлчилгээгээ мөр мөрөөр бичнэ үү" />
                </div>

                <div class="space-y-1.5">
                    <Label>Зураг</Label>
                    <ImageUpload v-model:file="form.photo" v-model:remove="form.remove_photo" :current="professional?.photo" label="Профайл зураг" />
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-4">
                    <p class="mb-3 text-sm font-medium text-gray-700">Холбоо барих (зөвхөн бүртгэлтэй хэрэглэгчид харагдана)</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label>Утас</Label>
                            <Input v-model="form.phone" type="text" placeholder="+49 ..." />
                        </div>
                        <div class="space-y-1.5">
                            <Label>И-мэйл</Label>
                            <Input v-model="form.email" type="email" />
                            <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <Label>Вэбсайт</Label>
                            <Input v-model="form.website" type="text" placeholder="https://" />
                        </div>
                        <div class="space-y-1.5">
                            <Label>Facebook</Label>
                            <Input v-model="form.facebook" type="text" placeholder="https://facebook.com/..." />
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <Button type="submit" :disabled="form.processing">{{ isEdit ? 'Хадгалах' : 'Илгээх' }}</Button>
                    <Button as="a" href="/professionals" variant="secondary">Цуцлах</Button>
                </div>
            </form>
        </div>
    </PublicLayout>
</template>
