<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    current: { type: String, default: '' }, // одоо байгаа зургийн URL
    label: { type: String, default: 'Зураг' },
});

// Шинэ файл болон "устгах" төлөвийг эцэг формтой v-model-оор холбоно.
const file = defineModel('file', { default: null });
const remove = defineModel('remove', { type: Boolean, default: false });

const input = ref(null);
const localPreview = ref(null);

const preview = computed(() => {
    if (remove.value) return null;
    return localPreview.value ?? props.current ?? null;
});

function pick() {
    input.value?.click();
}

function onFile(e) {
    const f = e.target.files[0];
    if (!f) return;
    file.value = f;
    remove.value = false;
    localPreview.value = URL.createObjectURL(f);
    e.target.value = '';
}

function clear() {
    file.value = null;
    remove.value = true;
    localPreview.value = null;
}
</script>

<template>
    <div>
        <div
            class="group relative aspect-video w-full cursor-pointer overflow-hidden rounded-lg border border-input bg-gray-50 transition hover:border-brand-400"
            @click="pick"
        >
            <template v-if="preview">
                <img :src="preview" alt="" class="h-full w-full object-cover" />
                <!-- Hover: зураг солих -->
                <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition group-hover:opacity-100">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/95 px-3 py-1.5 text-sm font-medium text-gray-900 shadow-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Зураг солих
                    </span>
                </div>
                <!-- Устгах -->
                <button
                    type="button"
                    class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition hover:bg-black/70 group-hover:opacity-100"
                    title="Зураг устгах"
                    @click.stop="clear"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </template>

            <div v-else class="flex h-full w-full flex-col items-center justify-center gap-2 text-gray-400 transition group-hover:text-brand-500">
                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <span class="text-sm font-medium">Зураг сонгох</span>
                <span class="text-xs">{{ label }}</span>
            </div>
        </div>
        <p class="mt-1.5 text-xs text-gray-400">JPG, PNG, WebP — 4MB хүртэл.</p>
        <input ref="input" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onFile" />
    </div>
</template>
