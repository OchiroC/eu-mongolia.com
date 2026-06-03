<script setup>
import ListingForm from '@/components/ListingForm.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ categories: Array });
</script>

<template>
    <Head title="Зар нэмэх" />

    <PublicLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="mb-6 text-2xl font-bold text-gray-900">Шинэ зар нийтлэх</h1>
            <ListingForm :categories="categories" submit-url="/zar" method="post" />
        </div>
    </PublicLayout>
</template>
