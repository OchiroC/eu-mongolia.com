<script setup>
import PostForm from '@/Components/PostForm.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
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
