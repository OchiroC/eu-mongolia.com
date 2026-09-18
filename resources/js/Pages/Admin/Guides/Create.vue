<script setup>
import GuideForm from '@/Components/GuideForm.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ topics: Array, countries: Array, stages: Array });
</script>

<template>
    <Head title="Шинэ гарын авлага" />

    <AdminLayout>
        <template #title>Шинэ гарын авлага</template>
        <GuideForm :topics="topics" :countries="countries" :stages="stages" submit-url="/admin/guides" method="post" />
    </AdminLayout>
</template>
