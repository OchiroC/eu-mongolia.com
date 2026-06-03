<script setup>
import Sortable from 'sortablejs';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({
    max: { type: Number, default: 8 },
});

// Нэгдсэн жагсаалт: { url: string|null, file: File|null, preview: string|null }
const items = defineModel('items', { default: () => [] });

const input = ref(null);
const grid = ref(null);
let sortable = null;

onMounted(() => {
    sortable = Sortable.create(grid.value, {
        animation: 150,
        ghostClass: 'opacity-40',
        delay: 120, // touch дээр санамсаргүй чирэхээс сэргийлнэ
        delayOnTouchOnly: true,
        onEnd: ({ oldIndex, newIndex }) => {
            if (oldIndex == null || newIndex == null || oldIndex === newIndex) return;
            const arr = items.value;
            const [moved] = arr.splice(oldIndex, 1);
            arr.splice(newIndex, 0, moved);
        },
    });
});
onBeforeUnmount(() => sortable?.destroy());

function pick() {
    input.value?.click();
}

function onFiles(e) {
    for (const f of Array.from(e.target.files)) {
        if (items.value.length >= props.max) break;
        items.value.push({ url: null, file: f, preview: URL.createObjectURL(f) });
    }
    e.target.value = '';
}

function remove(i) {
    const it = items.value[i];
    if (it?.preview) URL.revokeObjectURL(it.preview);
    items.value.splice(i, 1);
}

function src(it) {
    return it.url ?? it.preview;
}
</script>

<template>
    <div>
        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6">
            <!-- Зургийн хайрцгууд (sortable) -->
            <div ref="grid" class="contents">
                <div
                    v-for="(it, i) in items"
                    :key="it.url ?? it.preview"
                    class="group relative aspect-square cursor-move touch-none select-none overflow-hidden rounded-lg bg-gray-100 ring-1 ring-gray-200"
                >
                    <img :src="src(it)" alt="" class="pointer-events-none h-full w-full object-cover" />
                    <span v-if="i === 0" class="pointer-events-none absolute inset-x-0 bottom-0 bg-black/60 py-0.5 text-center text-[10px] font-medium text-white">Нүүр</span>
                    <button
                        type="button"
                        class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-black/60 text-xs text-white opacity-0 transition group-hover:opacity-100"
                        @click.stop="remove(i)"
                    >✕</button>
                </div>
            </div>

            <!-- Нэмэх -->
            <button
                v-if="items.length < max"
                type="button"
                class="flex aspect-square flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-gray-200 text-gray-400 transition hover:border-brand-400 hover:text-brand-500"
                @click="pick"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                <span class="text-[11px] font-medium">Зураг</span>
            </button>
        </div>
        <p class="mt-2 text-xs text-gray-400">{{ items.length }}/{{ max }} — чирж дараалал солино (утсанд удаан дарж чирнэ). Эхний зураг нүүр.</p>
        <input ref="input" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="onFiles" />
    </div>
</template>
