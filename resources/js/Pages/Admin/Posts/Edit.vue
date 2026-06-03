<script setup>
import PostForm from '@/Components/PostForm.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    post: Object,
    categories: Array,
});
</script>

<template>
    <Head title="Мэдээ засах" />

    <AdminLayout>
        <template #title>Мэдээ засах</template>
        <PostForm
            :post="post"
            :categories="categories"
            :submit-url="`/admin/posts/${post.id}`"
            method="put"
        />
    </AdminLayout>
</template>
