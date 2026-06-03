<script setup>
import ListingCard from '@/Components/ListingCard.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ listings: Array });
</script>

<template>
    <Head title="Хадгалсан зар" />

    <PublicLayout>
        <h1 class="mb-6 flex items-center gap-2 text-2xl font-bold text-gray-900">
            <svg class="h-6 w-6 fill-red-500 text-red-500" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
            Хадгалсан зар
        </h1>

        <div v-if="listings.length" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            <ListingCard v-for="l in listings" :key="l.id" :listing="l" />
        </div>

        <div v-else class="rounded-2xl bg-white py-16 text-center shadow-soft ring-1 ring-gray-100">
            <p class="text-gray-500">Та одоогоор зар хадгалаагүй байна.</p>
            <Link href="/zar" class="mt-3 inline-block font-semibold text-brand-700 hover:underline">Зар үзэх →</Link>
        </div>
    </PublicLayout>
</template>
