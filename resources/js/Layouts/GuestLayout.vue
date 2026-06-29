<script setup>
import Logo from '@/Components/Logo.vue';

defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});

const highlights = [
    { icon: '📰', text: 'Европ дахь монголчуудын мэдээ мэдээлэл' },
    { icon: '🎟️', text: 'Эвент, арга хэмжээний тасалбар' },
    { icon: '📢', text: 'Зар сурталчилгаа, бизнес лавлах' },
];
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Зүүн тал — брэнд талбар (зөвхөн дэлгэц томд) -->
        <div class="relative hidden w-1/2 overflow-hidden bg-gradient-to-br from-brand-800 via-brand-700 to-brand-950 lg:flex lg:flex-col lg:justify-between lg:p-12">
            <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-brand-400/20 blur-3xl"></div>

            <Logo size="lg" badge="glass" tone="white" class="relative" />

            <div class="relative">
                <h2 class="text-3xl font-bold leading-tight text-white">
                    Европ дахь монголчуудын<br />нэгдсэн платформ
                </h2>
                <p class="mt-3 max-w-md text-brand-100">
                    Мэдээ, эвент, зар — бүгд нэг дор.
                </p>

                <ul class="mt-8 space-y-3">
                    <li v-for="h in highlights" :key="h.text" class="flex items-center gap-3 text-brand-50">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-lg backdrop-blur">{{ h.icon }}</span>
                        <span class="text-sm">{{ h.text }}</span>
                    </li>
                </ul>
            </div>

            <p class="relative text-sm text-brand-200">© 2026 Yazguur</p>
        </div>

        <!-- Баруун тал — форм -->
        <div class="flex w-full flex-col items-center justify-center px-6 py-10 lg:w-1/2">
            <div class="w-full max-w-md">
                <Logo size="lg" badge="solid" tone="brand" class="mb-8 justify-center lg:hidden" />

                <div v-if="title || subtitle" class="mb-8">
                    <h1 v-if="title" class="text-2xl font-bold text-gray-900">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-1 text-sm text-gray-500">{{ subtitle }}</p>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
