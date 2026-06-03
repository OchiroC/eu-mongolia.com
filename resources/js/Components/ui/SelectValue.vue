<script setup>
import { SelectValue } from 'reka-ui';

defineProps({ placeholder: { type: String, default: '' } });
</script>

<template>
    <SelectValue :placeholder="placeholder" />
</template>
