<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    question: Object,
    answers: { type: Array, default: () => [] },
});

const user = computed(() => usePage().props.auth?.user);

const form = useForm({ body: '' });
function submitAnswer() {
    if (!form.body.trim()) return;
    form.post(`/questions/${props.question.slug}/answers`, {
        preserveScroll: true,
        onSuccess: () => form.reset('body'),
    });
}
function vote(a) {
    if (!user.value) { router.visit('/login'); return; }
    router.post(`/answers/${a.id}/vote`, {}, { preserveScroll: true });
}
function accept(a) {
    router.post(`/answers/${a.id}/accept`, {}, { preserveScroll: true });
}
function deleteAnswer(a) {
    if (confirm('Хариултаа устгах уу?')) router.delete(`/answers/${a.id}`, { preserveScroll: true });
}
function deleteQuestion() {
    if (confirm('Асуултаа устгах уу?')) router.delete(`/questions/${props.question.id}`);
}
function initial(name) {
    return (name || '?').charAt(0).toUpperCase();
}
</script>

<template>
    <Head :title="question.title" />

    <PublicLayout>
        <div class="mx-auto max-w-3xl">
            <Link href="/questions" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">← Асуулт хариулт руу буцах</Link>

            <!-- Асуулт -->
            <div class="mt-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-soft">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700">{{ question.category_label }}</span>
                    <span v-if="question.country" class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-500">{{ question.country }}</span>
                </div>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ question.title }}</h1>
                <div class="rich-content mt-3 max-w-none whitespace-pre-line text-gray-700">{{ question.body }}</div>
                <div class="mt-4 flex items-center justify-between text-sm text-gray-400">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="flex h-6 w-6 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-xs font-bold text-brand-700">
                            <img v-if="question.avatar" :src="question.avatar" alt="" class="h-full w-full object-cover" /><template v-else>{{ initial(question.user) }}</template>
                        </span>
                        {{ question.user }} · {{ question.created_at }}
                    </span>
                    <button v-if="question.owned" class="text-gray-400 hover:text-red-500" @click="deleteQuestion">Устгах</button>
                </div>
            </div>

            <!-- Хариултууд -->
            <h2 class="mb-3 mt-8 text-lg font-bold text-gray-900">{{ answers.length }} хариулт</h2>
            <div class="space-y-3">
                <div
                    v-for="a in answers"
                    :key="a.id"
                    class="flex gap-3 rounded-2xl border bg-white p-4 shadow-soft"
                    :class="a.is_best ? 'border-emerald-300 ring-1 ring-emerald-100' : 'border-gray-100'"
                >
                    <!-- Санал -->
                    <div class="flex flex-col items-center">
                        <button
                            class="flex h-9 w-9 items-center justify-center rounded-full transition"
                            :class="a.voted ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-brand-50 hover:text-brand-600'"
                            title="Тус болсон"
                            @click="vote(a)"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" /></svg>
                        </button>
                        <span class="mt-1 text-sm font-semibold text-gray-700">{{ a.votes }}</span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <span v-if="a.is_best" class="mb-1 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">✓ Шилдэг хариулт</span>
                        <p class="whitespace-pre-line text-sm text-gray-700">{{ a.body }}</p>
                        <div class="mt-2 flex items-center gap-3 text-xs text-gray-400">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="flex h-5 w-5 items-center justify-center overflow-hidden rounded-full bg-gray-100 text-[10px] font-bold text-gray-600">
                                    <img v-if="a.avatar" :src="a.avatar" alt="" class="h-full w-full object-cover" /><template v-else>{{ initial(a.user) }}</template>
                                </span>
                                {{ a.user }} · {{ a.created_at }}
                            </span>
                            <button v-if="question.owned" class="font-medium transition" :class="a.is_best ? 'text-emerald-600' : 'text-gray-400 hover:text-emerald-600'" @click="accept(a)">
                                {{ a.is_best ? 'Шилдэг ✓' : 'Шилдгээр тэмдэглэх' }}
                            </button>
                            <button v-if="user && user.id === a.user_id" class="text-gray-400 hover:text-red-500" @click="deleteAnswer(a)">Устгах</button>
                        </div>
                    </div>
                </div>
                <p v-if="!answers.length" class="rounded-2xl bg-gray-50 px-4 py-8 text-center text-sm text-gray-400">Эхний хариултыг өгөөрэй.</p>
            </div>

            <!-- Хариулт бичих -->
            <div v-if="user" class="mt-6 rounded-2xl border border-gray-100 bg-white p-4 shadow-soft">
                <h3 class="mb-2 font-semibold text-gray-900">Таны хариулт</h3>
                <textarea
                    v-model="form.body"
                    rows="4"
                    placeholder="Туршлага, зөвлөгөөгөө хуваалцаарай…"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100"
                ></textarea>
                <p v-if="form.errors.body" class="mt-1 text-sm text-destructive">{{ form.errors.body }}</p>
                <div class="mt-2 text-right">
                    <button type="button" :disabled="form.processing || !form.body.trim()" class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-brand-700 hover:shadow-brand-glow active:translate-y-0 disabled:opacity-50" @click="submitAnswer">Хариулт нэмэх</button>
                </div>
            </div>
            <p v-else class="mt-6 rounded-2xl bg-brand-50/60 px-4 py-3 text-center text-sm text-gray-600">
                Хариулт бичихийн тулд <Link href="/login" class="font-semibold text-brand-700 hover:underline">нэвтэрнэ</Link> үү.
            </p>
        </div>
    </PublicLayout>
</template>
