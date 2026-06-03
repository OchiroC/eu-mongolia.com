<script setup>
import { cn } from '@/lib/utils';
import { DialogDescription } from 'reka-ui';

const props = defineProps({ class: { type: null, default: '' } });
</script>

<template>
    <DialogDescription :class="cn('text-sm text-gray-500', props.class)">
        <slot />
    </DialogDescription>
</template>
