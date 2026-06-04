<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Dialog from '@/Components/ui/Dialog.vue';
import DialogContent from '@/Components/ui/DialogContent.vue';
import DialogDescription from '@/Components/ui/DialogDescription.vue';
import DialogTitle from '@/Components/ui/DialogTitle.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ listings: Array });

// Онцлох багцууд (mock төлбөр)
const packages = [
    { days: 7, price: 2, label: '7 хоног' },
    { days: 14, price: 3.5, label: '14 хоног', popular: true },
    { days: 30, price: 6, label: '30 хоног' },
];

const promoteOpen = ref(false);
const promoteFor = ref(null);

function openPromote(l) {
    promoteFor.value = l;
    promoteOpen.value = true;
}
function buyPromote(days) {
    if (!promoteFor.value) return;
    router.post(`/zar/${promoteFor.value.id}/promote`, { days }, {
        preserveScroll: true,
        onSuccess: () => { promoteOpen.value = false; },
    });
}

const statusLabel = { active: 'Идэвхтэй', sold: 'Зарагдсан', inactive: 'Нуусан' };
const statusClass = {
    active: 'bg-emerald-50 text-emerald-700',
    sold: 'bg-gray-100 text-gray-600',
    inactive: 'bg-amber-50 text-amber-700',
};

function priceLabel(l) {
    if (l.price_type === 'free') return 'Үнэгүй';
    if (l.price_type === 'giveaway') return 'Дайна';
    if (l.price === null || l.price === undefined) return 'Тохиролцоно';
    const p = Number(l.price).toLocaleString('mn-MN') + ' €';
    return l.price_type === 'negotiable' ? p + ' (VB)' : p;
}
function setStatus(l, status) {
    router.patch(`/zar/${l.id}/status`, { status }, { preserveScroll: true });
}
function destroy(l) {
    if (confirm('Энэ зарыг устгах уу?')) {
        router.delete(`/zar/${l.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Миний зар" />

    <PublicLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Миний зар</h1>
            <Button :as="Link" href="/zar/new">+ Зар нэмэх</Button>
        </div>

        <div v-if="listings.length" class="space-y-3">
            <div
                v-for="l in listings"
                :key="l.id"
                class="flex flex-col gap-4 rounded-2xl bg-white p-4 shadow-soft ring-1 ring-gray-100 sm:flex-row sm:items-center"
            >
                <Link :href="`/zar/${l.slug}`" class="h-20 w-28 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                    <img v-if="l.cover" :src="l.cover" class="h-full w-full object-cover" />
                    <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                </Link>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusClass[l.status]">{{ statusLabel[l.status] }}</span>
                        <span v-if="l.is_featured" class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">★ Онцлох · {{ l.featured_until }} хүртэл</span>
                        <span class="text-xs text-gray-400">👁 {{ l.views }}</span>
                    </div>
                    <Link :href="`/zar/${l.slug}`" class="mt-1 block truncate font-semibold text-gray-900 hover:text-brand-700">{{ l.title }}</Link>
                    <p class="text-sm font-bold text-gray-900">{{ priceLabel(l) }}</p>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    <Button v-if="l.status === 'active'" size="sm" class="bg-amber-500 hover:bg-amber-600" @click="openPromote(l)">
                        ★ {{ l.is_featured ? 'Сунгах' : 'Онцлох' }}
                    </Button>
                    <Button v-if="l.status !== 'sold'" variant="outline" size="sm" @click="setStatus(l, 'sold')">Зарагдсан</Button>
                    <Button v-else variant="secondary" size="sm" @click="setStatus(l, 'active')">Дахин нийтлэх</Button>
                    <Button :as="Link" :href="`/zar/${l.id}/edit`" variant="secondary" size="sm">Засах</Button>
                    <Button variant="destructive" size="sm" @click="destroy(l)">Устгах</Button>
                </div>
            </div>
        </div>

        <div v-else class="rounded-2xl bg-white py-16 text-center shadow-soft ring-1 ring-gray-100">
            <p class="text-gray-500">Та одоогоор зар нийтлээгүй байна.</p>
            <Link href="/zar/new" class="mt-3 inline-block font-semibold text-brand-700 hover:underline">Анхны зараа нэмэх →</Link>
        </div>

        <!-- Онцлох багц сонгох -->
        <Dialog v-model:open="promoteOpen">
            <DialogContent class="max-w-md">
                <DialogTitle>Зар онцлох</DialogTitle>
                <DialogDescription>
                    Онцлох зар нь жагсаалт болон нүүр хуудсанд <span class="font-medium text-amber-600">★ тэмдэгтэйгээр дээр</span> харагдана. Илүү олон хүн харж, хурдан зарагдана.
                </DialogDescription>

                <div class="mt-2 space-y-2">
                    <button
                        v-for="p in packages"
                        :key="p.days"
                        type="button"
                        class="flex w-full items-center justify-between rounded-xl border p-4 text-left transition hover:border-amber-400 hover:bg-amber-50/50"
                        :class="p.popular ? 'border-amber-300 bg-amber-50/40' : 'border-gray-200'"
                        @click="buyPromote(p.days)"
                    >
                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ p.label }}
                                <span v-if="p.popular" class="ml-1 rounded-full bg-amber-400 px-2 py-0.5 text-[10px] font-bold text-amber-900">Түгээмэл</span>
                            </p>
                            <p class="text-xs text-gray-400">Зарыг {{ p.label }} онцолно</p>
                        </div>
                        <span class="text-lg font-bold text-gray-900">{{ p.price }}€</span>
                    </button>
                </div>
                <p class="mt-1 text-center text-xs text-gray-400">Туршилтын (mock) төлбөр — карт холбогдоогүй.</p>
            </DialogContent>
        </Dialog>
    </PublicLayout>
</template>
