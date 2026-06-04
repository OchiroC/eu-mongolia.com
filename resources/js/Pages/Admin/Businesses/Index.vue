<script setup>
import Button from '@/Components/ui/Button.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ businesses: Object, filter: String, counts: Object });

const tabs = [
    { key: 'pending', label: 'Хүлээгдэж буй' },
    { key: 'active', label: 'Нийтлэгдсэн' },
    { key: 'inactive', label: 'Идэвхгүй' },
];

function go(status) { router.get('/admin/businesses', { status }, { preserveScroll: true, preserveState: true }); }
function approve(id) { router.post(`/admin/businesses/${id}/approve`, {}, { preserveScroll: true }); }
function feature(id) { router.post(`/admin/businesses/${id}/feature`, {}, { preserveScroll: true }); }
function deactivate(id) { router.post(`/admin/businesses/${id}/deactivate`, {}, { preserveScroll: true }); }
function destroy(id) { if (confirm('Устгах уу?')) router.delete(`/admin/businesses/${id}`, { preserveScroll: true }); }
function initial(name) { return (name || '?').charAt(0).toUpperCase(); }
</script>

<template>
    <Head title="Бизнес" />

    <AdminLayout>
        <template #title>Монгол бизнес лавлах</template>

        <div class="mb-5 flex flex-wrap gap-2">
            <button v-for="t in tabs" :key="t.key" class="rounded-full px-4 py-1.5 text-sm font-medium transition" :class="filter === t.key ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50'" @click="go(t.key)">{{ t.label }} <span class="ml-1 text-xs opacity-80">{{ counts[t.key] }}</span></button>
        </div>

        <div v-if="businesses.data.length" class="space-y-3">
            <div v-for="b in businesses.data" :key="b.id" class="flex flex-col gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-100 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-100 text-sm font-bold text-brand-700">
                        <img v-if="b.photo" :src="b.photo" alt="" class="h-full w-full object-cover" /><template v-else>{{ initial(b.name) }}</template>
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="font-semibold text-gray-900">{{ b.name }}</span>
                            <span v-if="b.is_featured" class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-700">Онцлох</span>
                        </div>
                        <p class="truncate text-xs text-gray-400">{{ b.category_label }} · {{ b.city }}<span v-if="b.owner"> · {{ b.owner }}</span></p>
                    </div>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <Button v-if="b.status === 'pending'" size="sm" @click="approve(b.id)">Батлах</Button>
                    <Button variant="outline" size="sm" @click="feature(b.id)">{{ b.is_featured ? 'Онцлох хасах' : 'Онцлох' }}</Button>
                    <Button v-if="b.status !== 'pending'" variant="secondary" size="sm" @click="deactivate(b.id)">{{ b.status === 'inactive' ? 'Идэвхжүүлэх' : 'Идэвхгүй' }}</Button>
                    <Link :href="`/businesses/${b.slug}`" target="_blank"><Button variant="ghost" size="sm">Үзэх</Button></Link>
                    <Button variant="destructive" size="sm" @click="destroy(b.id)">Устгах</Button>
                </div>
            </div>
        </div>

        <div v-else class="rounded-2xl bg-white py-16 text-center shadow-sm ring-1 ring-gray-100">
            <p class="font-medium text-gray-700">Бизнес алга</p>
        </div>

        <div v-if="businesses.links && businesses.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <Link v-for="link in businesses.links" :key="link.label" :href="link.url || ''" v-html="link.label" class="rounded-md px-3 py-1 text-sm" :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']" />
        </div>
    </AdminLayout>
</template>
