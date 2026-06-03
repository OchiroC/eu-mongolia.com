<script setup>
import BannerForm from '@/components/BannerForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ advertisers: Array });
</script>

<template>
    <Head title="Шинэ баннер" />

    <AdminLayout>
        <template #title>Шинэ баннер</template>
        <BannerForm :advertisers="advertisers" submit-url="/admin/banners" method="post" />
    </AdminLayout>
</template>
