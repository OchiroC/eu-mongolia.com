<script setup>
import { usePage } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';
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
            class="block overflow-hidden rounded-[3px] border border-brand-100"
            :class="variant === 'box' ? 'bg-white' : ''"
        >
            <img
                :src="banner.image_path"
                :alt="banner.title"
                class="w-full object-cover"
                :class="variant === 'box' ? 'aspect-[4/3]' : 'aspect-[8/1] max-h-32'"
            />
        </a>
        <p class="kicker text-right">Реклам</p>
    </div>

    <!-- Сул байршил: сурталчилгаа захиалаагүй үед зарах урилга. -->
    <a
        v-else-if="placeholder"
        href="/contact"
        class="group flex border border-brand-100 bg-sand-50 transition-colors hover:border-brand-300"
        :class="variant === 'box' ? 'aspect-[4/3] flex-col justify-between p-5' : 'items-center justify-between gap-6 px-5 py-4'"
    >
        <span>
            <span class="kicker block">Сурталчилгааны байр</span>
            <span class="mt-1.5 block text-sm text-brand-500">Франкфурт дахь монголчуудад бизнесээ таниулаарай.</span>
        </span>
        <span class="inline-flex shrink-0 items-center gap-1 text-sm font-medium text-brand-600 group-hover:underline group-hover:underline-offset-4">
            Холбогдох <ArrowRight class="h-4 w-4" />
        </span>
    </a>
</template>
