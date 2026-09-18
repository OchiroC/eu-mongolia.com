<script setup>
// Дотоод хуудсуудын нэгдсэн толгой: жижиг шошго, гарчиг, тайлбар, баруун талын үйлдэл.
defineProps({
    kicker: { type: String, default: '' },
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
});
</script>

<template>
    <div class="mb-8 flex flex-col gap-5 border-b border-brand-100 pb-8 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <p v-if="kicker" class="kicker">{{ kicker }}</p>
            <h1 class="text-3xl font-semibold tracking-tight text-brand-600" :class="kicker ? 'mt-3' : ''">{{ title }}</h1>
            <p v-if="subtitle" class="mt-2 max-w-2xl text-[15px] leading-relaxed text-brand-500">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.actions" class="flex shrink-0 flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
