<script setup>
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ orders: Array });

function formatDate(value) {
    if (!value) return '';
    return new Date(value).toLocaleString('mn-MN', { dateStyle: 'long', timeStyle: 'short' });
}
</script>

<template>
    <Head title="Миний тасалбарууд" />

    <PublicLayout>
        <h1 class="mb-6 text-2xl font-bold">Миний тасалбарууд</h1>

        <div v-if="orders.length" class="space-y-4">
            <Link
                v-for="order in orders"
                :key="order.id"
                :href="`/orders/${order.id}`"
                class="block rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100 hover:shadow-md"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold">{{ order.event?.title }}</p>
                        <p class="mt-1 text-sm text-gray-500">📅 {{ formatDate(order.event?.starts_at) }}</p>
                        <p class="mt-1 text-xs text-gray-400">#{{ order.reference }}</p>
                    </div>
                    <span class="rounded-full bg-brand-50 px-3 py-1 text-sm font-medium text-brand-700">
                        {{ order.ticket_count }} тасалбар
                    </span>
                </div>
            </Link>
        </div>

        <p v-else class="rounded-lg bg-white py-12 text-center text-gray-500 ring-1 ring-gray-100">
            Та одоогоор тасалбар аваагүй байна.
            <Link href="/events" class="text-brand-700 underline">Эвент үзэх</Link>
        </p>
    </PublicLayout>
</template>
