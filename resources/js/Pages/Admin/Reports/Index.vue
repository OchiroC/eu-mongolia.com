<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ reports: Object });

const reasonLabels = {
    spam: 'Спам/реклам',
    scam: 'Луйвар/залилан',
    prohibited: 'Хориотой бараа',
    duplicate: 'Давхардсан',
    offensive: 'Зохисгүй агуулга',
    other: 'Бусад',
};
const reasonClass = {
    scam: 'bg-red-100 text-red-700',
    prohibited: 'bg-red-100 text-red-700',
    offensive: 'bg-red-100 text-red-700',
    spam: 'bg-amber-100 text-amber-700',
    duplicate: 'bg-amber-100 text-amber-700',
    other: 'bg-gray-100 text-gray-600',
};

function dismiss(id) {
    router.post(`/admin/reports/${id}/dismiss`, {}, { preserveScroll: true });
}
function hide(id) {
    router.post(`/admin/reports/${id}/hide`, {}, { preserveScroll: true });
}
function destroyListing(id) {
    if (confirm('Энэ зарыг бүрмөсөн устгах уу?')) {
        router.delete(`/admin/reports/${id}/listing`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Модерац" />

    <AdminLayout>
        <template #title>Модерац — Гомдол</template>

        <div v-if="reports.data.length" class="space-y-3">
            <div v-for="r in reports.data" :key="r.id" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="reasonClass[r.reason] || 'bg-gray-100 text-gray-600'">
                                {{ reasonLabels[r.reason] || r.reason }}
                            </span>
                            <span class="text-xs text-gray-400">{{ r.reporter ?? 'Зочин' }} · {{ r.created_at }}</span>
                        </div>

                        <template v-if="r.listing">
                            <Link :href="`/zar/${r.listing.slug}`" target="_blank" class="mt-2 block font-semibold text-gray-900 hover:text-brand-700">
                                {{ r.listing.title }}
                            </Link>
                            <p class="text-xs text-gray-400">
                                {{ r.listing.city || '—' }} ·
                                <span :class="r.listing.status === 'active' ? 'text-emerald-600' : 'text-gray-500'">
                                    {{ r.listing.status === 'active' ? 'Идэвхтэй' : r.listing.status === 'sold' ? 'Зарагдсан' : 'Нуусан' }}
                                </span>
                            </p>
                        </template>
                        <p v-else class="mt-2 text-sm italic text-gray-400">Зар устгагдсан байна.</p>

                        <p v-if="r.note" class="mt-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-600">“{{ r.note }}”</p>
                    </div>

                    <div class="flex shrink-0 flex-wrap gap-2">
                        <Button variant="secondary" size="sm" @click="dismiss(r.id)">Дусгах</Button>
                        <template v-if="r.listing">
                            <Button v-if="r.listing.status === 'active'" variant="outline" size="sm" @click="hide(r.id)">Зар нуух</Button>
                            <Button variant="destructive" size="sm" @click="destroyListing(r.id)">Зар устгах</Button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="rounded-2xl bg-white py-16 text-center shadow-sm ring-1 ring-gray-100">
            <svg class="mx-auto h-10 w-10 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="mt-3 font-medium text-gray-700">Шийдвэрлэх гомдол алга</p>
            <p class="text-sm text-gray-400">Бүх гомдол шийдэгдсэн байна 🎉</p>
        </div>

        <div v-if="reports.links && reports.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <Link
                v-for="link in reports.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[
                    link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200',
                    !link.url ? 'pointer-events-none opacity-50' : '',
                ]"
            />
        </div>
    </AdminLayout>
</template>
