<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="h-10 rounded-md border-brand-200 px-3 text-sm focus:border-brand-600 focus:ring-1 focus:ring-brand-600"
        v-model="model"
        ref="input"
    />
</template>
