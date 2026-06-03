<script setup>
import EventForm from '@/Components/EventForm.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Шинэ эвент" />

    <AdminLayout>
        <template #title>Шинэ эвент</template>
        <EventForm submit-url="/admin/events" method="post" />
    </AdminLayout>
</template>
