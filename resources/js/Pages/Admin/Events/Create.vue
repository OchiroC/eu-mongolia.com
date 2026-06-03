<script setup>
import EventForm from '@/components/EventForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Шинэ эвент" />

    <AdminLayout>
        <template #title>Шинэ эвент</template>
        <EventForm submit-url="/admin/events" method="post" />
    </AdminLayout>
</template>
