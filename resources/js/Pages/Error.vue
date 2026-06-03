<script setup>
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ status: { type: Number, default: 404 } });

const content = computed(() => ({
    404: { title: 'Хуудас олдсонгүй', text: 'Хайж буй хуудас устсан, нэр нь өөрчлөгдсөн эсвэл байхгүй байна.' },
    403: { title: 'Хандах эрхгүй', text: 'Энэ хуудсанд хандах эрх танд алга.' },
    500: { title: 'Алдаа гарлаа', text: 'Серверийн талд алдаа гарлаа. Бид удахгүй засна.' },
    503: { title: 'Засвартай байна', text: 'Сайт түр засвартай байна. Удахгүй эргэн ороорой.' },
    429: { title: 'Хэт олон хүсэлт', text: 'Та хэтэрхий олон хүсэлт илгээлээ. Хэсэг хүлээгээд дахин оролдоно уу.' },
}[props.status] ?? { title: 'Алдаа', text: 'Гэнэтийн алдаа гарлаа.' }));
</script>

<template>
    <Head :title="content.title" />

    <PublicLayout>
        <div class="mx-auto flex max-w-lg flex-col items-center py-16 text-center sm:py-24">
            <p class="text-7xl font-extrabold tracking-tight text-brand-600 sm:text-8xl">{{ status }}</p>
            <h1 class="mt-4 text-2xl font-bold text-gray-900">{{ content.title }}</h1>
            <p class="mt-2 text-gray-500">{{ content.text }}</p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <Link href="/" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">← Нүүр хуудас</Link>
                <Link href="/zar" class="rounded-lg px-5 py-2.5 text-sm font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50">Зар үзэх</Link>
            </div>
        </div>
    </PublicLayout>
</template>
