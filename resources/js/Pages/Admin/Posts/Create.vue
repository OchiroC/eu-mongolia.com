<script setup>
import PostForm from '@/components/PostForm.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    categories: Array,
});
</script>

<template>
    <Head title="Шинэ мэдээ" />

    <AdminLayout>
        <template #title>Шинэ мэдээ</template>
        <PostForm :categories="categories" submit-url="/admin/posts" method="post" />
    </AdminLayout>
</template>
