<script setup>
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    postSlug: { type: String, required: true },
    comments: { type: Array, default: () => [] },
    enabled: { type: Boolean, default: true },
});

const user = computed(() => usePage().props.auth?.user);

const total = computed(() =>
    props.comments.reduce((n, c) => n + 1 + (c.replies?.length || 0), 0),
);

// Үндсэн сэтгэгдлийн форм
const form = useForm({ body: '', parent_id: null });

function submitMain() {
    if (!form.body.trim()) return;
    form.parent_id = null;
    form.post(`/news/${props.postSlug}/comments`, {
        preserveScroll: true,
        onSuccess: () => form.reset('body'),
    });
}

// Хариу бичих
const replyTo = ref(null);
const replyForm = useForm({ body: '', parent_id: null });

function openReply(id) {
    replyTo.value = replyTo.value === id ? null : id;
    replyForm.reset();
    replyForm.clearErrors();
}

function submitReply(parentId) {
    if (!replyForm.body.trim()) return;
    replyForm.parent_id = parentId;
    replyForm.post(`/news/${props.postSlug}/comments`, {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            replyTo.value = null;
        },
    });
}

function react(comment, value) {
    if (!user.value) {
        router.visit('/login');
        return;
    }
    router.post(`/comments/${comment.id}/react`, { value }, { preserveScroll: true });
}

function destroy(comment) {
    if (!window.confirm('Сэтгэгдлийг устгах уу?')) return;
    router.delete(`/comments/${comment.id}`, { preserveScroll: true });
}

function initial(name) {
    return (name || '?').charAt(0).toUpperCase();
}
</script>

<template>
    <section id="comments" class="mt-10 border-t border-gray-100 pt-8">
        <h2 class="text-lg font-bold text-gray-900">Сэтгэгдэл <span class="text-gray-400">({{ total }})</span></h2>

        <!-- Зохисгүй агуулгын анхааруулга -->
        <div v-if="enabled" class="mt-4 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.71-3L13.71 4a2 2 0 00-3.42 0L3.36 16a2 2 0 001.71 3z" /></svg>
            <p class="text-sm text-amber-800">
                Ёс бус хэллэг, доромжлол, заналхийлэл болон зохисгүй агуулга бүхий сэтгэгдэл <span class="font-semibold">нийтлэгдэхгүй</span>. Бүх сэтгэгдэл админ хянаж зөвшөөрсний дараа харагдана.
            </p>
        </div>

        <!-- Сэтгэгдэл хаалттай -->
        <p v-if="!enabled" class="mt-4 rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-500">
            Энэ мэдээнд сэтгэгдэл бичих хэсэг хаалттай байна.
        </p>

        <!-- Бичих форм -->
        <div v-else-if="user" class="mt-5">
            <div class="flex gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">{{ initial(user.name) }}</span>
                <div class="flex-1">
                    <textarea
                        v-model="form.body"
                        rows="3"
                        placeholder="Сэтгэгдлээ бичнэ үү…"
                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100"
                    ></textarea>
                    <p v-if="form.errors.body" class="mt-1 text-sm text-destructive">{{ form.errors.body }}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <p class="text-xs" :class="form.body.length > 2000 ? 'text-destructive' : 'text-gray-400'">{{ form.body.length }}/2000</p>
                        <button
                            type="button"
                            :disabled="form.processing || !form.body.trim() || form.body.length > 2000"
                            class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-700 disabled:opacity-50"
                            @click="submitMain"
                        >Илгээх</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Нэвтрээгүй -->
        <p v-else class="mt-4 rounded-xl bg-brand-50/60 px-4 py-3 text-sm text-gray-600">
            Сэтгэгдэл бичихийн тулд <Link href="/login" class="font-semibold text-brand-700 hover:underline">нэвтэрнэ</Link> үү.
        </p>

        <!-- Жагсаалт -->
        <div class="mt-7 space-y-6">
            <div v-for="c in comments" :key="c.id" class="space-y-4">
                <!-- Эх сэтгэгдэл -->
                <div class="flex gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-sm font-bold text-brand-700">
                        <img v-if="c.avatar" :src="c.avatar" alt="" class="h-full w-full object-cover" />
                        <template v-else>{{ initial(c.user) }}</template>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="rounded-2xl rounded-tl-sm bg-gray-50 px-4 py-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-gray-900">{{ c.user }}</span>
                                <span class="text-xs text-gray-400">{{ c.created_at }}</span>
                            </div>
                            <p class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ c.body }}</p>
                        </div>
                        <div class="mt-1.5 flex items-center gap-4 pl-1 text-xs">
                            <button class="inline-flex items-center gap-1 font-medium transition" :class="c.my_reaction === 1 ? 'text-brand-600' : 'text-gray-500 hover:text-brand-600'" @click="react(c, 1)">
                                <svg class="h-4 w-4" :fill="c.my_reaction === 1 ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" /></svg>
                                {{ c.likes }}
                            </button>
                            <button class="inline-flex items-center gap-1 font-medium transition" :class="c.my_reaction === -1 ? 'text-red-500' : 'text-gray-500 hover:text-red-500'" @click="react(c, -1)">
                                <svg class="h-4 w-4" :fill="c.my_reaction === -1 ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.737 3h4.017c.163 0 .326.02.485.06L17 4m-7 10v5a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5" /></svg>
                                {{ c.dislikes }}
                            </button>
                            <button v-if="enabled && user" class="font-medium text-gray-500 transition hover:text-gray-800" @click="openReply(c.id)">Хариулах</button>
                            <button v-if="user && user.id === c.user_id" class="font-medium text-gray-400 transition hover:text-red-500" @click="destroy(c)">Устгах</button>
                        </div>

                        <!-- Хариу бичих форм -->
                        <div v-if="replyTo === c.id" class="mt-3">
                            <textarea
                                v-model="replyForm.body"
                                rows="2"
                                placeholder="Хариу бичих…"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-100"
                            ></textarea>
                            <div class="mt-2 flex justify-end gap-2">
                                <button type="button" class="rounded-lg px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800" @click="openReply(c.id)">Болих</button>
                                <button type="button" :disabled="replyForm.processing || !replyForm.body.trim()" class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50" @click="submitReply(c.id)">Илгээх</button>
                            </div>
                        </div>

                        <!-- Хариунууд -->
                        <div v-if="c.replies?.length" class="mt-3 space-y-3 border-l-2 border-gray-100 pl-4">
                            <div v-for="r in c.replies" :key="r.id" class="flex gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gray-100 text-xs font-bold text-gray-600">
                                    <img v-if="r.avatar" :src="r.avatar" alt="" class="h-full w-full object-cover" />
                                    <template v-else>{{ initial(r.user) }}</template>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="rounded-2xl rounded-tl-sm bg-gray-50 px-3.5 py-2">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-sm font-semibold text-gray-900">{{ r.user }}</span>
                                            <span class="text-xs text-gray-400">{{ r.created_at }}</span>
                                        </div>
                                        <p class="mt-1 whitespace-pre-line text-sm text-gray-700">{{ r.body }}</p>
                                    </div>
                                    <div class="mt-1.5 flex items-center gap-4 pl-1 text-xs">
                                        <button class="inline-flex items-center gap-1 font-medium transition" :class="r.my_reaction === 1 ? 'text-brand-600' : 'text-gray-500 hover:text-brand-600'" @click="react(r, 1)">
                                            <svg class="h-3.5 w-3.5" :fill="r.my_reaction === 1 ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" /></svg>
                                            {{ r.likes }}
                                        </button>
                                        <button class="inline-flex items-center gap-1 font-medium transition" :class="r.my_reaction === -1 ? 'text-red-500' : 'text-gray-500 hover:text-red-500'" @click="react(r, -1)">
                                            <svg class="h-3.5 w-3.5" :fill="r.my_reaction === -1 ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.737 3h4.017c.163 0 .326.02.485.06L17 4m-7 10v5a2 2 0 002 2h.095c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5" /></svg>
                                            {{ r.dislikes }}
                                        </button>
                                        <button v-if="enabled && user" class="font-medium text-gray-500 transition hover:text-gray-800" @click="openReply(c.id)">Хариулах</button>
                                        <button v-if="user && user.id === r.user_id" class="font-medium text-gray-400 transition hover:text-red-500" @click="destroy(r)">Устгах</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p v-if="!comments.length" class="rounded-xl bg-gray-50 px-4 py-8 text-center text-sm text-gray-400">
                Эхний сэтгэгдлийг үлдээгээрэй.
            </p>
        </div>
    </section>
</template>
