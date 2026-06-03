<script setup>
import { cn } from '@/lib/utils';
import { ref } from 'vue';

const props = defineProps({
    class: { type: null, default: '' },
});

const model = defineModel({ type: [String, Number], default: '' });

// ref-ээр focus хийх боломжтой болгоно (алдаа гарвал input руу шилжих гэх мэт).
const el = ref(null);
defineExpose({ focus: () => el.value?.focus(), el });
</script>

<template>
    <input
        ref="el"
        v-model="model"
        :class="cn('flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50', props.class)"
    />
</template>
