<script setup>
import CustomsNotice from '@/Components/CustomsNotice.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDate, timeAgo } from '@/lib/date';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Eye, Phone, Plane } from 'lucide-vue-next';

const props = defineProps({ parcel: Object });

function close() {
    router.post(`/achaa/${props.parcel.id}/close`, {}, { preserveScroll: true });
}
function destroy() {
    if (confirm('Энэ зарыг устгах уу?')) router.delete(`/achaa/${props.parcel.id}`);
}
</script>

<template>
    <Head :title="`${parcel.from_city} → ${parcel.to_city}`" />

    <PublicLayout>
        <Link href="/achaa" class="inline-flex items-center gap-1.5 text-sm text-brand-500 hover:text-brand-600">
            <ArrowLeft class="h-4 w-4" /> Ачааны зар
        </Link>

        <div class="mt-6 grid gap-10 lg:grid-cols-12">
            <article class="lg:col-span-8">
                <p class="kicker">{{ parcel.type_label }} · {{ parcel.direction_label }}</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-brand-600">{{ parcel.from_city }} → {{ parcel.to_city }}</h1>
                <p v-if="parcel.status === 'closed'" class="mt-3 inline-block rounded-[3px] bg-brand-100 px-2 py-0.5 font-mono text-[11px] uppercase tracking-wider text-brand-500">Хаагдсан</p>

                <dl class="mt-8 grid grid-cols-2 border-t border-brand-100 sm:grid-cols-4">
                    <div class="border-b border-brand-100 py-4 pr-4">
                        <dt class="kicker">Огноо</dt>
                        <dd class="tabular mt-1.5 font-mono text-brand-600">{{ formatDate(parcel.date) }}</dd>
                    </div>
                    <div class="border-b border-brand-100 py-4 pr-4">
                        <dt class="kicker">Нислэг</dt>
                        <dd class="mt-1.5">
                            <Link v-if="parcel.flight" :href="`/flights/${parcel.flight.slug}`" class="inline-flex items-center gap-1.5 font-mono text-brand-600 underline underline-offset-2">
                                <Plane class="h-3.5 w-3.5" /> {{ parcel.flight.code }}
                            </Link>
                            <span v-else class="text-brand-400">-</span>
                        </dd>
                    </div>
                    <div class="border-b border-brand-100 py-4 pr-4">
                        <dt class="kicker">Жин</dt>
                        <dd class="tabular mt-1.5 font-mono text-brand-600">{{ parcel.weight_kg ? `${parcel.weight_kg} кг` : '-' }}</dd>
                    </div>
                    <div class="border-b border-brand-100 py-4">
                        <dt class="kicker">Үнэ</dt>
                        <dd class="tabular mt-1.5 font-mono text-brand-600">{{ parcel.price || 'Тохиролцоно' }}</dd>
                    </div>
                </dl>

                <p class="mt-8 whitespace-pre-line leading-relaxed text-brand-600">{{ parcel.description }}</p>

                <div class="mt-8"><CustomsNotice /></div>
            </article>

            <aside class="space-y-4 lg:col-span-4">
                <div class="border border-brand-100 p-5">
                    <p class="kicker">Зар тавьсан</p>
                    <p class="mt-2 font-medium text-brand-600">{{ parcel.user }}</p>
                    <p class="mt-1 text-xs text-brand-400">{{ timeAgo(parcel.created_at) }} · <Eye class="inline h-3 w-3 align-[-1px]" /> {{ parcel.views }}</p>

                    <a v-if="parcel.contact_phone" :href="`tel:${parcel.contact_phone}`" class="mt-5 flex h-11 items-center justify-center gap-2 rounded-md bg-brand-600 text-sm font-medium text-white transition-colors hover:bg-brand-500">
                        <Phone class="h-4 w-4" /> {{ parcel.contact_phone }}
                    </a>
                    <Link v-else-if="!$page.props.auth?.user" href="/login" class="mt-5 flex h-11 items-center justify-center rounded-md bg-brand-600 text-sm font-medium text-white transition-colors hover:bg-brand-500">
                        Холбоо барихын тулд нэвтрэх
                    </Link>
                    <p v-else class="mt-5 text-sm text-brand-400">Утасны дугаар оруулаагүй байна.</p>
                </div>

                <div v-if="parcel.owned" class="flex flex-wrap gap-2">
                    <Link :href="`/achaa/${parcel.id}/edit`" class="inline-flex h-9 items-center rounded-md border border-brand-200 px-3 text-sm text-brand-600 hover:border-brand-600">Засах</Link>
                    <button type="button" class="inline-flex h-9 items-center rounded-md border border-brand-200 px-3 text-sm text-brand-600 hover:border-brand-600" @click="close">{{ parcel.status === 'closed' ? 'Дахин нээх' : 'Хаах' }}</button>
                    <button type="button" class="inline-flex h-9 items-center rounded-md border border-red-200 px-3 text-sm text-red-600 hover:border-red-600" @click="destroy">Устгах</button>
                </div>
            </aside>
        </div>
    </PublicLayout>
</template>
