<script setup>
import ListingForm from '@/components/ListingForm.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ listing: Object, categories: Array });
</script>

<template>
    <Head title="Зар засах" />

    <PublicLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="mb-6 text-2xl font-bold text-gray-900">Зар засах</h1>
            <ListingForm :listing="listing" :categories="categories" :submit-url="`/zar/${listing.id}`" method="put" />
        </div>
    </PublicLayout>
</template>
