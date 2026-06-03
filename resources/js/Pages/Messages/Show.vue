<script setup>
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { nextTick, onMounted, ref, watch } from 'vue';

const props = defineProps({
    conversation: Object,
    messages: { type: Array, default: () => [] },
});

const form = useForm({ body: '' });
const thread = ref(null);

function scrollDown() {
    nextTick(() => {
        if (thread.value) thread.value.scrollTop = thread.value.scrollHeight;
    });
}
onMounted(scrollDown);
watch(() => props.messages.length, scrollDown);

function send() {
    if (!form.body.trim()) return;
    form.post(`/messages/${props.conversation.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('body');
            router.reload({ only: ['messages'] });
        },
    });
}

function initial(name) {
    return (name || '?').charAt(0).toUpperCase();
}
</script>

<template>
    <Head :title="`Зурвас — ${conversation.other}`" />

    <PublicLayout>
        <div class="mx-auto flex max-w-2xl flex-col" style="height: calc(100vh - 9rem)">
            <!-- Толгой -->
            <div class="flex items-center gap-3 rounded-t-2xl border border-gray-100 bg-white px-4 py-3 shadow-soft">
                <Link href="/messages" class="text-gray-400 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                </Link>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-sm font-bold text-brand-700">
                    <img v-if="conversation.avatar" :src="conversation.avatar" alt="" class="h-full w-full object-cover" />
                    <template v-else>{{ initial(conversation.other) }}</template>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold text-gray-900">{{ conversation.other }}</p>
                    <Link v-if="conversation.listing" :href="`/zar/${conversation.listing.slug}`" class="truncate text-xs text-brand-700 hover:underline">{{ conversation.listing.title }}</Link>
                </div>
            </div>

            <!-- Мессежүүд -->
            <div ref="thread" class="flex-1 space-y-2 overflow-y-auto border-x border-gray-100 bg-gray-50 px-4 py-4">
                <div v-for="m in messages" :key="m.id" class="flex" :class="m.mine ? 'justify-end' : 'justify-start'">
                    <div
                        class="max-w-[78%] rounded-2xl px-3.5 py-2 text-sm"
                        :class="m.mine ? 'rounded-br-sm bg-brand-600 text-white' : 'rounded-bl-sm bg-white text-gray-800 ring-1 ring-gray-100'"
                    >
                        <p class="whitespace-pre-line">{{ m.body }}</p>
                        <p class="mt-1 text-[10px]" :class="m.mine ? 'text-brand-100' : 'text-gray-400'">{{ m.created_at }}</p>
                    </div>
                </div>
                <p v-if="!messages.length" class="py-8 text-center text-sm text-gray-400">Эхний зурвасаа бичээрэй.</p>
            </div>

            <!-- Бичих -->
            <form class="flex items-end gap-2 rounded-b-2xl border border-gray-100 bg-white p-3 shadow-soft" @submit.prevent="send">
                <textarea
                    v-model="form.body"
                    rows="1"
                    placeholder="Зурвас бичих…"
                    class="max-h-32 min-h-[42px] flex-1 resize-none rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    @keydown.enter.exact.prevent="send"
                ></textarea>
                <button type="submit" :disabled="form.processing || !form.body.trim()" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white transition hover:bg-brand-700 disabled:opacity-50">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                </button>
            </form>
        </div>
    </PublicLayout>
</template>
