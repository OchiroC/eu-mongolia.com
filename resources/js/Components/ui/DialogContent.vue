<script setup>
import { cn } from '@/lib/utils';
import { DialogClose, DialogContent, DialogOverlay, DialogPortal } from 'reka-ui';

const props = defineProps({
    class: { type: null, default: '' },
});
</script>

<template>
    <DialogPortal>
        <DialogOverlay
            class="fixed inset-0 z-50 bg-black/50 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0"
        />
        <DialogContent
            :class="cn('fixed left-1/2 top-1/2 z-50 grid w-[calc(100%-2rem)] max-w-lg -translate-x-1/2 -translate-y-1/2 gap-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-xl duration-200 focus:outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95', props.class)"
        >
            <slot />
            <DialogClose
                class="absolute right-4 top-4 rounded-md p-1 text-gray-400 opacity-70 transition hover:bg-gray-100 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                <span class="sr-only">Хаах</span>
            </DialogClose>
        </DialogContent>
    </DialogPortal>
</template>
