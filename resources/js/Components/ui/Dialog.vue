<script setup>
import { DialogRoot } from 'reka-ui';

const open = defineModel('open', { type: Boolean, default: false });
</script>

<template>
    <DialogRoot v-model:open="open">
        <slot />
    </DialogRoot>
</template>
