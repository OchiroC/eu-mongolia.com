<script setup>
import BannerForm from '@/components/BannerForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ banner: Object, advertisers: Array });
</script>

<template>
    <Head title="Баннер засах" />

    <AdminLayout>
        <template #title>Баннер засах</template>
        <BannerForm
            :banner="banner"
            :advertisers="advertisers"
            :submit-url="`/admin/banners/${banner.id}`"
            method="put"
        />
    </AdminLayout>
</template>
