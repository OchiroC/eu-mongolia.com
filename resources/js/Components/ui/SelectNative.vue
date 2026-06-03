<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: null, default: '' },
});

// Утга нь string, number эсвэл null (ангиллын id) байж болно — type шалгалтгүй.
const model = defineModel({ default: '' });
</script>

<template>
    <select
        v-model="model"
        :class="cn('flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50', props.class)"
    >
        <slot />
    </select>
</template>
