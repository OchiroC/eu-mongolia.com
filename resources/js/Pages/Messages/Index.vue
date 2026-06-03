<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ conversations: { type: Array, default: () => [] } });

function initial(name) {
    return (name || '?').charAt(0).toUpperCase();
}
</script>

<template>
    <Head title="Зурвас" />

    <PublicLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="mb-5 text-2xl font-bold text-gray-900">Зурвас</h1>

            <div v-if="conversations.length" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-soft">
                <Link
                    v-for="c in conversations"
                    :key="c.id"
                    :href="`/messages/${c.id}`"
                    class="flex items-center gap-3 border-b border-gray-50 px-4 py-3 transition last:border-0 hover:bg-gray-50"
                >
                    <span class="relative flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-100 font-bold text-brand-700">
                        <img v-if="c.avatar" :src="c.avatar" alt="" class="h-full w-full object-cover" />
                        <template v-else>{{ initial(c.other) }}</template>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate font-semibold text-gray-900">{{ c.other }}</span>
                            <span class="shrink-0 text-xs text-gray-400">{{ c.last_at }}</span>
                        </div>
                        <p v-if="c.listing" class="truncate text-xs text-brand-700">{{ c.listing.title }}</p>
                        <p class="truncate text-sm text-gray-500">{{ c.last }}</p>
                    </div>
                    <span v-if="c.unread" class="flex h-5 min-w-[20px] shrink-0 items-center justify-center rounded-full bg-brand-600 px-1.5 text-xs font-bold text-white">{{ c.unread }}</span>
                </Link>
            </div>

            <div v-else class="rounded-2xl border border-dashed border-gray-200 bg-white py-16 text-center">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.5C3.5 15.3 3 13.7 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                <p class="mt-3 font-medium text-gray-700">Зурвас алга байна</p>
                <p class="text-sm text-gray-400">Зар үзээд худалдагчтай холбогдоорой.</p>
                <Link href="/zar" class="mt-3 inline-block font-medium text-brand-700 hover:underline">Зар үзэх →</Link>
            </div>
        </div>
    </PublicLayout>
</template>
