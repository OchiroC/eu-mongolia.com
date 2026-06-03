<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({
    placement: { type: String, required: true },
    // 'leaderboard' (өргөн), 'box' (хажуу талын) гэсэн харагдах хэлбэр
    variant: { type: String, default: 'leaderboard' },
    // Зар сонгогдоогүй үед "Энд сурталчилгаа" сул байршил харуулах эсэх
    placeholder: { type: Boolean, default: false },
});

const page = usePage();
const banners = computed(() => page.props.banners?.[props.placement] ?? []);

onMounted(() => {
    // Харагдсан тоог бүртгэнэ (хуудас ачаалагдах үед).
    banners.value.forEach((b) => {
        try {
            navigator.sendBeacon(`/banners/${b.id}/impression`);
        } catch (e) {
            // beacon дэмжихгүй бол алгасна
        }
    });
});
</script>

<template>
    <div v-if="banners.length" class="space-y-3">
        <a
            v-for="banner in banners"
            :key="banner.id"
            :href="`/banners/${banner.id}/click`"
            target="_blank"
            rel="noopener sponsored"
            class="block overflow-hidden rounded-lg ring-1 ring-gray-200"
            :class="variant === 'box' ? 'bg-white' : ''"
        >
            <img
                :src="banner.image_path"
                :alt="banner.title"
                class="w-full object-cover"
                :class="variant === 'box' ? 'aspect-[4/3]' : 'aspect-[8/1] max-h-32'"
            />
        </a>
        <p class="text-right text-[10px] uppercase tracking-wide text-gray-400">Реклам</p>
    </div>

    <!-- Зар сонгогдоогүй үеийн сул байршил -->
    <a
        v-else-if="placeholder"
        href="/contact"
        class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50/70 text-center text-gray-400 transition hover:border-brand-300 hover:text-brand-500"
        :class="variant === 'box' ? 'aspect-[4/3] p-4' : 'min-h-[96px] p-5'"
    >
        <svg class="mb-1 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z" /><path stroke-linecap="round" stroke-linejoin="round" d="M3 16l4.5-4.5a2 2 0 012.8 0L15 16m-1-1l1.5-1.5a2 2 0 012.8 0L21 16M14 8h.01" /></svg>
        <p class="text-sm font-medium text-gray-500">Энд сурталчилгаа байршуулах</p>
        <p class="text-xs">Реклам байршуулахаар бидэнтэй холбогдоно уу</p>
    </a>
</template>
