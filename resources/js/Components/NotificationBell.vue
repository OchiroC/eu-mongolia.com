<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const data = computed(() => page.props.notifications ?? { unread: 0, items: [] });

const open = ref(false);
const root = ref(null);

function toggle() {
    open.value = !open.value;
    if (open.value && data.value.unread > 0) {
        router.post('/notifications/read', {}, { preserveScroll: true, preserveState: true, only: ['notifications'] });
    }
}

function onClickOutside(e) {
    if (root.value && !root.value.contains(e.target)) {
        open.value = false;
    }
}
onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));

const typeIcon = {
    report: 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 2H21l-3 6 3 6h-8.5l-1-2H5a2 2 0 00-2 2z',
    order: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
    moderation: 'M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.71-3L13.71 4a2 2 0 00-3.42 0L3.36 16a2 2 0 001.71 3z',
    comment: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.5C3.5 15.3 3 13.7 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    message: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.5C3.5 15.3 3 13.7 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    answer: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};
function iconFor(t) {
    return typeIcon[t] || 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1';
}
</script>

<template>
    <div ref="root" class="relative">
        <button type="button" class="relative flex h-9 w-9 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100" aria-label="Мэдэгдэл" @click="toggle">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1" /></svg>
            <span v-if="data.unread" class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ data.unread > 9 ? '9+' : data.unread }}</span>
        </button>

        <transition
            enter-active-class="transition duration-100" enter-from-class="opacity-0 scale-95"
            leave-active-class="transition duration-75" leave-to-class="opacity-0 scale-95"
        >
            <div v-if="open" class="absolute right-0 mt-2 max-h-[70vh] w-80 overflow-y-auto rounded-xl border border-gray-100 bg-white py-1 shadow-lg">
                <div class="border-b border-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-900">Мэдэгдэл</div>
                <Link
                    v-for="n in data.items"
                    :key="n.id"
                    :href="n.url || '#'"
                    class="flex gap-3 px-4 py-3 transition hover:bg-gray-50"
                    :class="!n.read ? 'bg-brand-50/40' : ''"
                    @click="open = false"
                >
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" :d="iconFor(n.type)" /></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900">{{ n.title }}</p>
                        <p class="line-clamp-2 text-sm text-gray-500">{{ n.message }}</p>
                        <p class="mt-0.5 text-xs text-gray-400">{{ n.created_at }}</p>
                    </div>
                </Link>
                <p v-if="!data.items.length" class="px-4 py-8 text-center text-sm text-gray-400">Мэдэгдэл алга байна.</p>
            </div>
        </transition>
    </div>
</template>
