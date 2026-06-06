<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ pro: { type: Object, required: true } });

function initial(name) {
    return (name || '?').charAt(0).toUpperCase();
}
</script>

<template>
    <Link
        :href="`/professionals/${pro.slug}`"
        class="group relative flex flex-col items-center rounded-2xl border bg-white p-5 text-center shadow-card transition duration-300 hover:-translate-y-1 hover:shadow-card-lg"
        :class="pro.is_featured ? 'border-amber-200 ring-1 ring-amber-100' : 'border-gray-100'"
    >
        <span v-if="pro.is_featured" class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-amber-400 px-2 py-0.5 text-[10px] font-bold text-amber-900 shadow-sm">
            <svg class="h-2.5 w-2.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.9 6.26L21 9.27l-4.5 4.39L17.8 21 12 17.27 6.2 21l1.3-7.34L3 9.27l6.1-1.01L12 2z" /></svg>
            Онцлох
        </span>

        <span class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-2xl font-bold text-brand-700 ring-4 ring-brand-50 transition duration-300 group-hover:ring-brand-100">
            <img v-if="pro.photo" :src="pro.photo" :alt="pro.name" class="h-full w-full object-cover" />
            <template v-else>{{ initial(pro.name) }}</template>
        </span>

        <div class="mt-3 flex items-center gap-1">
            <h3 class="font-semibold text-gray-900 group-hover:text-brand-700">{{ pro.name }}</h3>
            <svg v-if="pro.is_verified" class="h-4 w-4 text-brand-600" viewBox="0 0 24 24" fill="currentColor" title="Баталгаажсан"><path fill-rule="evenodd" d="M12 2l2.39 1.74 2.95-.02 1.06 2.76 2.43 1.7-.92 2.81.92 2.81-2.43 1.7-1.06 2.76-2.95-.02L12 22l-2.39-1.76-2.95.02-1.06-2.76-2.43-1.7.92-2.81-.92-2.81 2.43-1.7L6.66 3.7l2.95.02L12 2zm-1.1 13.2l5.2-5.2-1.4-1.4-3.8 3.8-1.8-1.8-1.4 1.4 3.2 3.2z" clip-rule="evenodd" /></svg>
        </div>
        <p v-if="pro.profession" class="mt-0.5 text-sm text-brand-700">{{ pro.profession }}</p>
        <p class="mt-1 text-xs text-gray-400">
            <span v-if="pro.category">{{ pro.category }}</span>
            <span v-if="pro.category && pro.city"> · </span>
            <span v-if="pro.city">{{ pro.city }}</span>
        </p>
    </Link>
</template>
