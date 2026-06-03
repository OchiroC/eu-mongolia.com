<script setup>
import EventForm from '@/Components/EventForm.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ event: Object });
</script>

<template>
    <Head title="Эвент засах" />

    <AdminLayout>
        <template #title>Эвент засах</template>
        <EventForm :event="event" :submit-url="`/admin/events/${event.id}`" method="put" />
    </AdminLayout>
</template>
