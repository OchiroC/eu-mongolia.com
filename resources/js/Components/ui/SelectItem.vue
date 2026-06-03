<script setup>
import { cn } from '@/lib/utils';
import { SelectItem, SelectItemIndicator, SelectItemText } from 'reka-ui';

const props = defineProps({
    value: { required: true },
    disabled: { type: Boolean, default: false },
    class: { type: null, default: '' },
});
</script>

<template>
    <SelectItem
        :value="value"
        :disabled="disabled"
        :class="cn('relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50', props.class)"
    >
        <span class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
            <SelectItemIndicator>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            </SelectItemIndicator>
        </span>
        <SelectItemText><slot /></SelectItemText>
    </SelectItem>
</template>
