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
    job: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    types: { type: Array, default: () => [] },
    countries: { type: Array, default: () => [] },
});

const isEdit = computed(() => !!props.job);

const form = useForm({
    _method: isEdit.value ? 'put' : 'post',
    title: props.job?.title ?? '',
    company: props.job?.company ?? '',
    description: props.job?.description ?? '',
    employment_type: props.job?.employment_type ?? 'full_time',
    category: props.job?.category ?? 'service',
    city: props.job?.city ?? '',
    country: props.job?.country ?? '',
    salary: props.job?.salary ?? '',
    contact_email: props.job?.contact_email ?? '',
    contact_phone: props.job?.contact_phone ?? '',
    apply_url: props.job?.apply_url ?? '',
});

const countryModel = computed({
    get: () => form.country || 'none',
    set: (v) => { form.country = v === 'none' ? '' : v; },
});

function submit() {
    if (isEdit.value) form.post(`/jobs/${props.job.id}`);
    else form.post('/jobs');
}
</script>

<template>
    <Head :title="isEdit ? 'Ажлын зар засах' : 'Ажлын зар нэмэх'" />

    <PublicLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-2xl font-bold text-gray-900">{{ isEdit ? 'Ажлын зар засах' : 'Ажлын зар нэмэх' }}</h1>

            <form class="mt-6 space-y-5" @submit.prevent="submit">
                <div class="space-y-1.5">
                    <Label>Албан тушаал / Гарчиг</Label>
                    <Input v-model="form.title" type="text" placeholder="жнь: Ресторанд туслах ажилтан" />
                    <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label>Компани / Ажил олгогч</Label>
                        <Input v-model="form.company" type="text" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Цалин</Label>
                        <Input v-model="form.salary" type="text" placeholder="жнь: €12/цаг, Тохиролцоно" />
                    </div>
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
                        <Label>Ажлын төрөл</Label>
                        <Select v-model="form.employment_type">
                            <SelectTrigger><SelectValue /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="t in types" :key="t.key" :value="t.key">{{ t.label }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <Label>Хот</Label>
                        <Input v-model="form.city" type="text" placeholder="Берлин" />
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
                    <Label>Дэлгэрэнгүй</Label>
                    <Textarea v-model="form.description" rows="6" placeholder="Ажлын байрны тодорхойлолт, шаардлага, цагийн хуваарь..." />
                    <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                </div>

                <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-4">
                    <p class="mb-3 text-sm font-medium text-gray-700">Холбоо барих (нэвтэрсэн хэрэглэгчид харагдана)</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label>И-мэйл</Label>
                            <Input v-model="form.contact_email" type="email" />
                            <p v-if="form.errors.contact_email" class="text-sm text-destructive">{{ form.errors.contact_email }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <Label>Утас</Label>
                            <Input v-model="form.contact_phone" type="text" placeholder="+49 ..." />
                        </div>
                        <div class="space-y-1.5 sm:col-span-2">
                            <Label>Өргөдлийн холбоос (заавал биш)</Label>
                            <Input v-model="form.apply_url" type="text" placeholder="https://" />
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <Button type="submit" :disabled="form.processing">{{ isEdit ? 'Хадгалах' : 'Нийтлэх' }}</Button>
                    <Button as="a" href="/jobs" variant="secondary">Цуцлах</Button>
                </div>
            </form>
        </div>
    </PublicLayout>
</template>
