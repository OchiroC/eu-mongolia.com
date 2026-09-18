<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({ class: { type: null, default: '' } });
</script>

<template>
    <div :class="cn('rounded-md border border-brand-100 bg-card text-card-foreground', props.class)">
        <slot />
    </div>
</template>
