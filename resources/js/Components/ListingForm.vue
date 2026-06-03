<script setup>
import MultiImageUpload from '@/components/MultiImageUpload.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import SelectContent from '@/components/ui/SelectContent.vue';
import SelectItem from '@/components/ui/SelectItem.vue';
import SelectTrigger from '@/components/ui/SelectTrigger.vue';
import SelectValue from '@/components/ui/SelectValue.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    listing: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
    submitUrl: String,
    method: { type: String, default: 'post' },
});

const countries = ['Герман', 'Чех', 'Польш', 'Унгар', 'Австри', 'Франц', 'Бельги', 'Голланд', 'Швед', 'Итали', 'Бусад'];

const form = useForm({
    _method: props.method,
    listing_category_id: props.listing?.listing_category_id ?? null,
    title: props.listing?.title ?? '',
    description: props.listing?.description ?? '',
    price_type: props.listing?.price_type ?? 'fixed',
    price: props.listing?.price ?? null,
    condition: props.listing?.condition ?? null,
    postal_code: props.listing?.postal_code ?? '',
    city: props.listing?.city ?? '',
    country: props.listing?.country ?? '',
    contact_name: props.listing?.contact_name ?? '',
    contact_phone: props.listing?.contact_phone ?? '',
    contact_email: props.listing?.contact_email ?? '',
    image_order: [], // эрэмбэ (submit үед бөглөнө)
    new_images: [], // шинэ File-ууд (submit үед бөглөнө)
    status: props.listing?.status ?? 'active',
});

// Зургийн нэгдсэн жагсаалт (хуучин URL + шинэ File). UI энэ дээр ажиллана.
const imageItems = ref((props.listing?.images ?? []).map((url) => ({ url, file: null, preview: null })));

// shadcn Select-д хоосон улсыг 'none' sentinel-ээр.
const countryModel = computed({
    get: () => form.country || 'none',
    set: (v) => { form.country = v === 'none' ? '' : v; },
});

function submit() {
    if (form.price_type === 'free' || form.price_type === 'giveaway') {
        form.price = null;
    }

    // Зургийн эрэмбэ + шинэ файлуудыг бүрдүүлнэ.
    const files = [];
    form.image_order = imageItems.value.map((it) => {
        if (it.file) {
            const idx = files.length;
            files.push(it.file);
            return 'new:' + idx;
        }
        return it.url;
    });
    form.new_images = files;

    // Файл upload-той тул бүх тохиолдолд POST + _method spoofing.
    form.post(props.submitUrl);
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <!-- Ангилал + гарчиг -->
        <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100">
            <h2 class="mb-4 font-semibold text-gray-900">Үндсэн мэдээлэл</h2>
            <div class="space-y-4">
                <div class="space-y-1.5">
                    <Label>Ангилал</Label>
                    <Select v-model="form.listing_category_id">
                        <SelectTrigger><SelectValue placeholder="— Ангилал сонгох —" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.listing_category_id" class="text-sm text-destructive">{{ form.errors.listing_category_id }}</p>
                </div>
                <div class="space-y-1.5">
                    <Label>Гарчиг</Label>
                    <Input v-model="form.title" type="text" placeholder="Жишээ: Toyota Prius 2015" />
                    <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
                </div>
                <div class="space-y-1.5">
                    <Label>Тайлбар</Label>
                    <Textarea v-model="form.description" rows="6" placeholder="Барааны байдал, онцлог, нөхцөл..." />
                    <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                </div>
                <div class="space-y-1.5">
                    <Label>Байдал</Label>
                    <div class="flex gap-2">
                        <Button type="button" :variant="form.condition === 'new' ? 'default' : 'outline'" @click="form.condition = 'new'">Шинэ</Button>
                        <Button type="button" :variant="form.condition === 'used' ? 'default' : 'outline'" @click="form.condition = 'used'">Хуучин</Button>
                        <Button type="button" :variant="form.condition === null ? 'default' : 'outline'" @click="form.condition = null">Хамаагүй</Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Үнэ -->
        <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100">
            <h2 class="mb-4 font-semibold text-gray-900">Үнэ</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <Label>Үнийн төрөл</Label>
                    <Select v-model="form.price_type">
                        <SelectTrigger><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="fixed">Тогтмол үнэ</SelectItem>
                            <SelectItem value="negotiable">Тохиролцоно (VB)</SelectItem>
                            <SelectItem value="free">Үнэгүй</SelectItem>
                            <SelectItem value="giveaway">Дайна</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div v-if="form.price_type === 'fixed' || form.price_type === 'negotiable'" class="space-y-1.5">
                    <Label>Үнэ (€)</Label>
                    <Input v-model.number="form.price" type="number" step="0.01" min="0" placeholder="0.00" />
                    <p v-if="form.errors.price" class="text-sm text-destructive">{{ form.errors.price }}</p>
                </div>
            </div>
        </div>

        <!-- Зураг -->
        <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100">
            <h2 class="mb-1 font-semibold text-gray-900">Зураг</h2>
            <p class="mb-4 text-sm text-gray-400">Зургаа сонгож upload хийнэ. Хамгийн ихдээ 8.</p>
            <MultiImageUpload v-model:items="imageItems" :max="8" />
            <p v-if="form.errors.new_images || form.errors.image_order" class="mt-2 text-sm text-destructive">{{ form.errors.new_images || form.errors.image_order }}</p>
        </div>

        <!-- Байршил + холбоо -->
        <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100">
            <h2 class="mb-4 font-semibold text-gray-900">Байршил & Холбоо барих</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="space-y-1.5">
                    <Label>Шуудангийн код (PLZ)</Label>
                    <Input v-model="form.postal_code" type="text" placeholder="10115" />
                </div>
                <div class="space-y-1.5">
                    <Label>Хот</Label>
                    <Input v-model="form.city" type="text" placeholder="Berlin" />
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
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div class="space-y-1.5">
                    <Label>Нэр</Label>
                    <Input v-model="form.contact_name" type="text" />
                </div>
                <div class="space-y-1.5">
                    <Label>Утас</Label>
                    <Input v-model="form.contact_phone" type="text" placeholder="+49 ..." />
                </div>
                <div class="space-y-1.5">
                    <Label>И-мэйл</Label>
                    <Input v-model="form.contact_email" type="email" />
                    <p v-if="form.errors.contact_email" class="text-sm text-destructive">{{ form.errors.contact_email }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <Button type="submit" size="lg" :disabled="form.processing">Нийтлэх</Button>
            <Button as="a" href="/my/zar" variant="outline" size="lg">Цуцлах</Button>
        </div>
    </form>
</template>
