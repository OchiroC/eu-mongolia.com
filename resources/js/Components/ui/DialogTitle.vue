<script setup>
import { cn } from '@/lib/utils';
import { DialogTitle } from 'reka-ui';

const props = defineProps({ class: { type: null, default: '' } });
</script>

<template>
    <DialogTitle :class="cn('text-lg font-semibold text-gray-900', props.class)">
        <slot />
    </DialogTitle>
</template>
