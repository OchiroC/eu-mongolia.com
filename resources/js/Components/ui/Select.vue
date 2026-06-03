<script setup>
import { SelectRoot } from 'reka-ui';

// Утга нь string, number эсвэл sentinel ('all'/'none') байж болно.
const model = defineModel({ default: undefined });
</script>

<template>
    <SelectRoot v-model="model">
        <slot />
    </SelectRoot>
</template>
