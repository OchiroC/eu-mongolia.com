<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: null, default: '' },
});

const model = defineModel({ type: Boolean, default: false });
</script>

<template>
    <input
        v-model="model"
        type="checkbox"
        :class="cn('h-4 w-4 rounded border-input text-primary focus:ring-2 focus:ring-ring focus:ring-offset-0', props.class)"
    />
</template>
