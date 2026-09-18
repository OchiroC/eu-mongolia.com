<script setup>
import Logo from '@/Components/Logo.vue';
import { ArrowRight } from 'lucide-vue-next';

defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});

const appName = import.meta.env.VITE_APP_NAME || 'OM137';

// Нүүрний чиглүүлэх самбартай ижил хэлбэр — бүртгэлтэй болсноор нээгдэх замууд.
const directions = [
    { label: 'Хамт аялах хүн олох', dest: 'Хамт аялах' },
    { label: 'Байр, ажлын зар тавих', dest: 'Зар' },
    { label: 'Эвентийн тасалбар авах', dest: 'Эвент' },
    { label: 'Монголчуудтай шууд бичилцэх', dest: 'Зурвас' },
];
</script>

<template>
    <div class="flex min-h-screen bg-white">
        <!-- Зүүн тал — хар самбар (зөвхөн том дэлгэцэнд). -->
        <div class="hidden w-1/2 flex-col justify-between bg-board p-12 text-white lg:flex">
            <Logo size="lg" badge="glass" tone="white" />

            <div>
                <p class="font-mono text-[11px] uppercase tracking-[0.14em] text-signal-400">FRA · Frankfurt am Main</p>
                <h2 class="mt-4 max-w-md text-3xl font-semibold leading-tight tracking-tight">
                    Франкфуртад буусан монгол хүний эхний зогсоол.
                </h2>

                <ul class="mt-10 max-w-md border-t border-board-line">
                    <li v-for="d in directions" :key="d.label" class="flex items-center gap-4 border-b border-board-line py-3.5">
                        <ArrowRight class="h-4 w-4 shrink-0 text-signal-400" />
                        <span class="flex-1 text-[15px]">{{ d.label }}</span>
                        <span class="font-mono text-[11px] uppercase tracking-[0.1em] text-white/40">{{ d.dest }}</span>
                    </li>
                </ul>
            </div>

            <p class="font-mono text-[11px] uppercase tracking-[0.12em] text-white/40">© 2026 {{ appName }}</p>
        </div>

        <!-- Баруун тал — форм. -->
        <div class="flex w-full flex-col items-center justify-center px-6 py-12 lg:w-1/2">
            <div class="w-full max-w-sm">
                <Logo size="lg" class="mb-10 lg:hidden" />

                <div v-if="title || subtitle" class="mb-8">
                    <h1 v-if="title" class="text-2xl font-semibold tracking-tight text-brand-600">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-2 text-sm text-brand-500">{{ subtitle }}</p>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
