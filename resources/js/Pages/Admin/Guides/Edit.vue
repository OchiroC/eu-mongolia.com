<script setup>
import GuideForm from '@/Components/GuideForm.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ guide: Object, topics: Array, countries: Array });
</script>

<template>
    <Head title="Guide засах" />

    <AdminLayout>
        <template #title>Guide засах</template>
        <GuideForm :guide="guide" :topics="topics" :countries="countries" :submit-url="`/admin/guides/${guide.id}`" method="put" />
    </AdminLayout>
</template>
