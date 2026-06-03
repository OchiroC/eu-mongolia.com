<script setup>
import { cn } from '@/lib/utils';
import { SelectIcon, SelectTrigger } from 'reka-ui';

const props = defineProps({ class: { type: null, default: '' } });
</script>

<template>
    <SelectTrigger
        :class="cn('flex h-10 w-full items-center justify-between gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background data-[placeholder]:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1', props.class)"
    >
        <slot />
        <SelectIcon as-child>
            <svg class="h-4 w-4 shrink-0 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
        </SelectIcon>
    </SelectTrigger>
</template>
