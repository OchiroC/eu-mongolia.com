<script setup>
import { cn } from '@/lib/utils';
import { SelectContent, SelectPortal, SelectViewport } from 'reka-ui';

const props = defineProps({
    class: { type: null, default: '' },
    position: { type: String, default: 'popper' },
});
</script>

<template>
    <SelectPortal>
        <SelectContent
            :position="position"
            :side-offset="4"
            :class="cn('relative z-50 max-h-96 min-w-[8rem] overflow-hidden rounded-md border border-gray-100 bg-white text-gray-900 shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=top]:slide-in-from-bottom-2', props.class)"
        >
            <SelectViewport class="w-full min-w-[var(--reka-select-trigger-width)] p-1">
                <slot />
            </SelectViewport>
        </SelectContent>
    </SelectPortal>
</template>
