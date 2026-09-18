<script setup>
import GuideForm from '@/Components/GuideForm.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ guide: Object, topics: Array, countries: Array, stages: Array });
</script>

<template>
    <Head title="Гарын авлага засах" />

    <AdminLayout>
        <template #title>Гарын авлага засах</template>
        <GuideForm :guide="guide" :topics="topics" :countries="countries" :stages="stages" :submit-url="`/admin/guides/${guide.id}`" method="put" />
    </AdminLayout>
</template>
