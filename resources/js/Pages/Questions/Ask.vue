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
    categories: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
});

const form = useForm({
    title: '',
    body: '',
    category: 'visa',
    country: '',
});

const countryModel = computed({
    get: () => form.country || 'none',
    set: (v) => { form.country = v === 'none' ? '' : v; },
});

function submit() {
    form.post('/questions');
}
</script>

<template>
    <Head title="Асуулт асуух" />

    <PublicLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-2xl font-bold text-gray-900">Асуулт асуух</h1>
            <p class="mt-1 text-sm text-gray-500">Тодорхой бичих тусам зөв хариулт авах магадлал өндөр.</p>

            <form class="mt-6 space-y-5" @submit.prevent="submit">
                <div class="space-y-1.5">
                    <Label>Асуулт (гарчиг)</Label>
                    <Input v-model="form.title" type="text" placeholder="жнь: Германд оюутны визээ хэрхэн сунгах вэ?" />
                    <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label>Ангилал</Label>
                        <Select v-model="form.category">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in categories" :key="c.key" :value="c.key">{{ c.label }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Улс (заавал биш)</Label>
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
                    <Label>Дэлгэрэнгүй</Label>
                    <Textarea v-model="form.body" rows="6" placeholder="Нөхцөл байдлаа дэлгэрэнгүй тайлбарлаарай..." />
                    <p v-if="form.errors.body" class="text-sm text-destructive">{{ form.errors.body }}</p>
                </div>

                <div class="flex gap-3">
                    <Button type="submit" :disabled="form.processing">Нийтлэх</Button>
                    <Button as="a" href="/questions" variant="secondary">Цуцлах</Button>
                </div>
            </form>
        </div>
    </PublicLayout>
</template>
