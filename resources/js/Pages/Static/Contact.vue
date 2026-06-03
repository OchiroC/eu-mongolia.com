<script setup>
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

const channels = [
    {
        title: 'И-мэйл',
        value: 'info@eu-mongolia.test',
        href: 'mailto:info@eu-mongolia.test',
        desc: 'Ерөнхий асуулт, санал хүсэлт',
        icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    },
    {
        title: 'Реклам байршуулах',
        value: 'ads@eu-mongolia.test',
        href: 'mailto:ads@eu-mongolia.test',
        desc: 'Баннер болон сурталчилгаа',
        icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
    },
    {
        title: 'Гомдол / зөрчил',
        value: 'support@eu-mongolia.test',
        href: 'mailto:support@eu-mongolia.test',
        desc: 'Зар, агуулгын зөрчил мэдээлэх',
        icon: 'M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.71-3L13.71 4a2 2 0 00-3.42 0L3.36 16a2 2 0 001.71 3z',
    },
];
</script>

<template>
    <Head title="Холбоо барих" />

    <PublicLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-3xl font-bold text-gray-900">Холбоо барих</h1>
            <p class="mt-2 text-lg text-gray-500">Бидэнтэй холбогдох олон арга бий. Бид 1–2 ажлын өдөрт хариу өгөхийг хичээдэг.</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <a
                    v-for="c in channels"
                    :key="c.title"
                    :href="c.href"
                    class="group rounded-2xl border border-gray-100 bg-white p-5 shadow-soft transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" :d="c.icon" /></svg>
                    </span>
                    <h3 class="mt-3 font-semibold text-gray-900">{{ c.title }}</h3>
                    <p class="text-sm text-gray-400">{{ c.desc }}</p>
                    <p class="mt-2 truncate text-sm font-medium text-brand-700">{{ c.value }}</p>
                </a>
            </div>

            <div class="mt-8 rounded-2xl bg-gray-50 p-6 text-sm leading-relaxed text-gray-600">
                <p>EU Mongolia нь хараат бус олон нийтийн платформ. Зар, мэдээ, эвентийн агуулгыг хэрэглэгчид өөрсдөө нийтэлдэг тул аливаа зөрчилтэй агуулгыг олж мэдвэл бидэнд мэдэгдэнэ үү — бид нэн даруй шалгана.</p>
            </div>
        </div>
    </PublicLayout>
</template>
