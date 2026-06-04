<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { timeAgo } from '@/lib/date';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    job: Object,
    related: { type: Array, default: () => [] },
});

const user = computed(() => usePage().props.auth?.user);
</script>

<template>
    <Head :title="job.title" />

    <PublicLayout>
        <Link href="/jobs" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">← Ажлын байр руу буцах</Link>

        <div class="mt-4 grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
            <article class="min-w-0">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700">{{ job.type_label }}</span>
                    <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-500">{{ job.category_label }}</span>
                    <span v-if="job.status === 'closed'" class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">Хаагдсан</span>
                </div>
                <h1 class="mt-2 text-3xl font-bold leading-tight text-gray-900">{{ job.title }}</h1>
                <p class="mt-1 text-gray-600">
                    <span v-if="job.company" class="font-medium">{{ job.company }}</span>
                    <span v-if="job.company && (job.city || job.country)"> · </span>
                    <span v-if="job.city || job.country">{{ job.city }}<span v-if="job.country">, {{ job.country }}</span></span>
                </p>
                <p class="mt-1 text-sm text-gray-400">{{ timeAgo(job.created_at) }} · {{ job.views }} үзсэн</p>

                <div class="rich-content mt-6 max-w-none whitespace-pre-line">{{ job.description }}</div>
            </article>

            <aside class="space-y-6 lg:sticky lg:top-20 lg:self-start">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-soft">
                    <p v-if="job.salary" class="text-lg font-bold text-gray-900">{{ job.salary }}</p>
                    <p class="text-sm text-gray-400">{{ job.type_label }}</p>

                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <h3 class="mb-2 text-sm font-semibold text-gray-900">Холбоо барих / Өргөдөл</h3>

                        <template v-if="job.contact">
                            <div class="space-y-2">
                                <a v-if="job.contact.apply_url" :href="job.contact.apply_url" target="_blank" rel="noopener" class="block rounded-lg bg-brand-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-brand-700">Өргөдөл гаргах →</a>
                                <a v-if="job.contact.email" :href="`mailto:${job.contact.email}`" class="block rounded-lg bg-brand-50 px-4 py-2.5 text-center text-sm font-semibold text-brand-700 hover:bg-brand-100">✉️ {{ job.contact.email }}</a>
                                <a v-if="job.contact.phone" :href="`tel:${job.contact.phone}`" class="block rounded-lg bg-gray-100 px-4 py-2.5 text-center text-sm font-semibold text-gray-700 hover:bg-gray-200">📞 {{ job.contact.phone }}</a>
                                <p v-if="!job.contact.apply_url && !job.contact.email && !job.contact.phone" class="text-sm text-gray-400">Холбоо барих мэдээлэл оруулаагүй.</p>
                            </div>
                        </template>
                        <template v-else>
                            <p class="mb-3 text-sm text-gray-500">Холбоо барих мэдээллийг харахын тулд нэвтэрнэ үү.</p>
                            <Link href="/login" class="block rounded-lg bg-brand-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-brand-700">Нэвтрэх</Link>
                        </template>
                    </div>

                    <Link v-if="job.owned" href="/my/jobs" class="mt-3 block text-center text-xs text-brand-700 hover:underline">Миний зар засах</Link>
                    <p class="mt-4 text-center text-xs text-gray-400">Урьдчилгаа төлбөр шаардсан ажил олгогчоос болгоомжил.</p>
                </div>

                <div v-if="related.length" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-soft">
                    <h3 class="mb-2 text-sm font-semibold text-gray-900">Төстэй ажил</h3>
                    <Link v-for="r in related" :key="r.id" :href="`/jobs/${r.slug}`" class="block border-b border-gray-50 py-2.5 last:border-0">
                        <p class="line-clamp-1 text-sm font-medium text-gray-800 hover:text-brand-700">{{ r.title }}</p>
                        <p class="mt-0.5 text-xs text-gray-400">{{ r.company || r.category_label }}<span v-if="r.city"> · {{ r.city }}</span></p>
                    </Link>
                </div>
            </aside>
        </div>
    </PublicLayout>
</template>
