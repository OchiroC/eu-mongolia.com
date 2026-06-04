<script setup>
import GuideForm from '@/Components/GuideForm.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ topics: Array, countries: Array });
</script>

<template>
    <Head title="Шинэ заавар" />

    <AdminLayout>
        <template #title>Шинэ заавар</template>
        <GuideForm :topics="topics" :countries="countries" submit-url="/admin/guides" method="post" />
    </AdminLayout>
</template>
