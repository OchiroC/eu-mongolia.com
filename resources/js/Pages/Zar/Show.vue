<script setup>
import ListingCard from '@/components/ListingCard.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import Button from '@/components/ui/Button.vue';
import Dialog from '@/components/ui/Dialog.vue';
import DialogContent from '@/components/ui/DialogContent.vue';
import DialogDescription from '@/components/ui/DialogDescription.vue';
import DialogFooter from '@/components/ui/DialogFooter.vue';
import DialogTitle from '@/components/ui/DialogTitle.vue';
import Label from '@/components/ui/Label.vue';
import Select from '@/components/ui/Select.vue';
import SelectContent from '@/components/ui/SelectContent.vue';
import SelectItem from '@/components/ui/SelectItem.vue';
import SelectTrigger from '@/components/ui/SelectTrigger.vue';
import SelectValue from '@/components/ui/SelectValue.vue';
import Textarea from '@/components/ui/Textarea.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    listing: Object,
    similar: Array,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const activeImg = ref(0);
const showContact = ref(false);

// Худалдагчид зурвас бичих
const msgForm = useForm({ body: '' });
function sendMessage() {
    if (!user.value) {
        router.visit('/login');
        return;
    }
    if (!msgForm.body.trim()) return;
    msgForm.post(`/zar/${props.listing.id}/message`, {
        onSuccess: () => msgForm.reset('body'),
    });
}

// Гомдол мэдүүлэх
const reportOpen = ref(false);
const reportForm = useForm({ reason: 'spam', note: '' });
const reasons = [
    { value: 'spam', label: 'Спам / реклам' },
    { value: 'scam', label: 'Луйвар / залилан' },
    { value: 'prohibited', label: 'Хориотой бараа' },
    { value: 'duplicate', label: 'Давхардсан зар' },
    { value: 'offensive', label: 'Зохисгүй агуулга' },
    { value: 'other', label: 'Бусад' },
];

function openReport() {
    if (!user.value) {
        router.visit('/login');
        return;
    }
    reportOpen.value = true;
}

function submitReport() {
    reportForm.post(`/zar/${props.listing.id}/report`, {
        preserveScroll: true,
        onSuccess: () => {
            reportOpen.value = false;
            reportForm.reset();
        },
    });
}

function priceLabel(l) {
    if (l.price_type === 'free') return 'Үнэгүй';
    if (l.price_type === 'giveaway') return 'Дайна';
    if (l.price === null || l.price === undefined) return 'Тохиролцоно';
    const p = Number(l.price).toLocaleString('mn-MN') + ' €';
    return l.price_type === 'negotiable' ? p + ' (VB)' : p;
}
function fullDate(value) {
    if (!value) return '';
    return new Date(value).toLocaleDateString('mn-MN', { year: 'numeric', month: 'long', day: 'numeric' });
}
</script>

<template>
    <Head :title="listing.title" />

    <PublicLayout>
        <Link href="/zar" class="mb-4 inline-block text-sm text-brand-700 hover:underline">← Зар руу буцах</Link>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Зүүн: зураг + тайлбар -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Зургийн галерей -->
                <div class="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-gray-100">
                    <div class="aspect-[16/10] bg-gray-100">
                        <img v-if="listing.images.length" :src="listing.images[activeImg]" :alt="listing.title" class="h-full w-full object-contain" />
                        <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                            <svg class="h-20 w-20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    </div>
                    <div v-if="listing.images.length > 1" class="flex gap-2 overflow-x-auto p-3">
                        <button
                            v-for="(img, i) in listing.images"
                            :key="i"
                            class="h-16 w-16 shrink-0 overflow-hidden rounded-lg ring-2 transition"
                            :class="i === activeImg ? 'ring-brand-600' : 'ring-transparent'"
                            @click="activeImg = i"
                        >
                            <img :src="img" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Тайлбар -->
                <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100">
                    <h2 class="mb-3 font-semibold text-gray-900">Тайлбар</h2>
                    <p class="whitespace-pre-line leading-relaxed text-gray-700">{{ listing.description }}</p>
                </div>
            </div>

            <!-- Баруун: үнэ + холбоо -->
            <div class="space-y-6">
                <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100">
                    <div class="flex flex-wrap items-center gap-2">
                        <Link v-if="listing.category" :href="`/zar?category=${listing.category.slug}`" class="rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700">{{ listing.category.name }}</Link>
                        <span v-if="listing.condition" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">{{ listing.condition === 'new' ? 'Шинэ' : 'Хуучин' }}</span>
                    </div>

                    <h1 class="mt-3 text-xl font-bold leading-tight text-gray-900">{{ listing.title }}</h1>
                    <p class="mt-2 text-2xl font-extrabold text-gray-900">{{ priceLabel(listing) }}</p>

                    <div class="mt-4 space-y-1.5 text-sm text-gray-500">
                        <p v-if="listing.city">📍 {{ listing.postal_code }} {{ listing.city }}<span v-if="listing.country">, {{ listing.country }}</span></p>
                        <p>🕓 {{ fullDate(listing.created_at) }}</p>
                        <p>👁 {{ listing.views }} үзсэн</p>
                    </div>

                    <!-- Эзэмшигчийн товч (өөрийн зар бол) -->
                    <div v-if="listing.owned" class="mt-5 flex gap-2 border-t border-gray-100 pt-5">
                        <Button :as="Link" :href="`/zar/${listing.id}/edit`" variant="secondary" class="flex-1">Засах</Button>
                        <Button :as="Link" href="/my/zar" variant="secondary" class="flex-1">Миний зар</Button>
                    </div>
                </div>

                <!-- Худалдагч + холбоо -->
                <div class="rounded-2xl bg-white p-6 shadow-soft ring-1 ring-gray-100">
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-100 text-lg font-bold text-brand-700">{{ (listing.seller.name || '?').charAt(0).toUpperCase() }}</span>
                        <div>
                            <p class="font-semibold text-gray-900">{{ listing.seller.name }}</p>
                            <p class="text-xs text-gray-400">Гишүүн: {{ listing.seller.since }}</p>
                        </div>
                    </div>

                    <Button v-if="!showContact" class="mt-5 w-full" @click="showContact = true">
                        Холбоо барих
                    </Button>

                    <div v-else class="mt-5 space-y-2 border-t border-gray-100 pt-4 text-sm">
                        <p v-if="listing.contact_name"><span class="text-gray-400">Нэр:</span> <span class="font-medium text-gray-900">{{ listing.contact_name }}</span></p>
                        <a v-if="listing.contact_phone" :href="`tel:${listing.contact_phone}`" class="block rounded-lg bg-brand-50 px-4 py-2.5 text-center font-semibold text-brand-700 hover:bg-brand-100">📞 {{ listing.contact_phone }}</a>
                        <a v-if="listing.contact_email" :href="`mailto:${listing.contact_email}`" class="block rounded-lg bg-gray-100 px-4 py-2.5 text-center font-semibold text-gray-700 hover:bg-gray-200">✉️ {{ listing.contact_email }}</a>
                        <p v-if="!listing.contact_phone && !listing.contact_email" class="text-gray-400">Холбоо барих мэдээлэл оруулаагүй.</p>
                    </div>

                    <!-- Зурвас бичих (өөрийн зар биш бол) -->
                    <div v-if="!listing.owned" class="mt-4 border-t border-gray-100 pt-4">
                        <p class="mb-2 text-sm font-medium text-gray-700">Худалдагчид зурвас бичих</p>
                        <Textarea v-model="msgForm.body" rows="2" placeholder="Сайн байна уу, энэ бараа байгаа юу?" />
                        <p v-if="msgForm.errors.body" class="mt-1 text-sm text-destructive">{{ msgForm.errors.body }}</p>
                        <Button class="mt-2 w-full" :disabled="msgForm.processing" @click="sendMessage">
                            {{ user ? 'Зурвас илгээх' : 'Нэвтэрч зурвас бичих' }}
                        </Button>
                    </div>

                    <p class="mt-4 text-center text-xs text-gray-400">Урьдчилгаа төлбөр шаардсан этгээдээс болгоомжил.</p>
                    <button type="button" class="mt-3 flex w-full items-center justify-center gap-1.5 text-xs text-gray-400 transition hover:text-red-500" @click="openReport">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 2H21l-3 6 3 6h-8.5l-1-2H5a2 2 0 00-2 2z" /></svg>
                        Энэ зарыг мэдээлэх
                    </button>
                </div>
            </div>
        </div>

        <!-- Төстэй зар -->
        <div v-if="similar.length" class="mt-10">
            <h2 class="mb-4 text-lg font-bold text-gray-900">Төстэй зар</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                <ListingCard v-for="l in similar" :key="l.id" :listing="l" />
            </div>
        </div>

        <!-- Гомдол мэдүүлэх цонх -->
        <Dialog v-model:open="reportOpen">
            <DialogContent class="max-w-md">
                <DialogTitle>Зар мэдээлэх</DialogTitle>
                <DialogDescription>Энэ зар дүрэм зөрчсөн бол шалтгааныг сонгоно уу. Бид шалгах болно.</DialogDescription>

                <div class="space-y-1.5">
                    <Label>Шалтгаан</Label>
                    <Select v-model="reportForm.reason">
                        <SelectTrigger><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="r in reasons" :key="r.value" :value="r.value">{{ r.label }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="space-y-1.5">
                    <Label>Тайлбар (заавал биш)</Label>
                    <Textarea v-model="reportForm.note" rows="3" placeholder="Нэмэлт мэдээлэл..." />
                    <p v-if="reportForm.errors.note" class="text-sm text-destructive">{{ reportForm.errors.note }}</p>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="reportOpen = false">Болих</Button>
                    <Button type="button" :disabled="reportForm.processing" @click="submitReport">Илгээх</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </PublicLayout>
</template>
