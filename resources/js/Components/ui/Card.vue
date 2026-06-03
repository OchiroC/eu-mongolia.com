<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({ class: { type: null, default: '' } });
</script>

<template>
    <div :class="cn('rounded-xl border border-gray-100 bg-card text-card-foreground shadow-soft', props.class)">
        <slot />
    </div>
</template>
