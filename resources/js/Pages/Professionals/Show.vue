<script setup>
import BannerDisplay from '@/Components/BannerDisplay.vue';
import ProfessionalCard from '@/Components/ProfessionalCard.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    professional: Object,
    related: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const contact = computed(() => page.props.flash?.contact ?? null);

function initial(name) {
    return (name || '?').charAt(0).toUpperCase();
}
function reveal() {
    if (!user.value) {
        router.visit('/login');
        return;
    }
    router.post(`/professionals/${props.professional.slug}/reveal`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="professional.name" />

    <PublicLayout>
        <Link href="/professionals" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">← Мэргэжилтэн рүү буцах</Link>

        <div class="mt-4 grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
            <article class="min-w-0">
                <!-- Толгой -->
                <div class="flex flex-col items-center gap-4 rounded-2xl border border-gray-100 bg-white p-6 text-center shadow-soft sm:flex-row sm:text-left">
                    <span class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-full bg-brand-100 text-3xl font-bold text-brand-700">
                        <img v-if="professional.photo" :src="professional.photo" :alt="professional.name" class="h-full w-full object-cover" />
                        <template v-else>{{ initial(professional.name) }}</template>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-center gap-1.5 sm:justify-start">
                            <h1 class="text-2xl font-bold text-gray-900">{{ professional.name }}</h1>
                            <svg v-if="professional.is_verified" class="h-5 w-5 text-brand-600" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2l2.39 1.74 2.95-.02 1.06 2.76 2.43 1.7-.92 2.81.92 2.81-2.43 1.7-1.06 2.76-2.95-.02L12 22l-2.39-1.76-2.95.02-1.06-2.76-2.43-1.7.92-2.81-.92-2.81 2.43-1.7L6.66 3.7l2.95.02L12 2zm-1.1 13.2l5.2-5.2-1.4-1.4-3.8 3.8-1.8-1.8-1.4 1.4 3.2 3.2z" clip-rule="evenodd" /></svg>
                        </div>
                        <p v-if="professional.profession" class="mt-0.5 font-medium text-brand-700">{{ professional.profession }}</p>
                        <div class="mt-2 flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-sm text-gray-500 sm:justify-start">
                            <span v-if="professional.category">{{ professional.category.name }}</span>
                            <span v-if="professional.city" class="inline-flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ professional.city }}<span v-if="professional.country">, {{ professional.country }}</span>
                            </span>
                        </div>
                        <div v-if="professional.languages.length" class="mt-2 flex flex-wrap justify-center gap-1.5 sm:justify-start">
                            <span v-for="l in professional.languages" :key="l" class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600">{{ l }}</span>
                        </div>
                    </div>
                </div>

                <!-- Танилцуулга -->
                <div v-if="professional.bio" class="mt-6">
                    <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Танилцуулга</h2>
                    <div class="rich-content max-w-none" v-html="professional.bio"></div>
                </div>

                <!-- Үйлчилгээ -->
                <div v-if="professional.services" class="mt-6">
                    <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Үйлчилгээ</h2>
                    <p class="whitespace-pre-line text-gray-700">{{ professional.services }}</p>
                </div>

                <!-- Холбоотой -->
                <div v-if="related.length" class="mt-8">
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Төстэй мэргэжилтэн</h2>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <ProfessionalCard v-for="p in related" :key="p.id" :pro="p" />
                    </div>
                </div>
            </article>

            <!-- Sidebar: холбоо барих -->
            <aside class="space-y-6 lg:sticky lg:top-20 lg:self-start">
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-soft">
                    <h3 class="border-b border-gray-100 px-4 py-3 text-sm font-semibold text-gray-900">Холбоо барих</h3>
                    <div class="p-4">
                        <!-- Нээгдсэн холбоо барих мэдээлэл -->
                        <div v-if="contact" class="space-y-2.5">
                            <a v-if="contact.phone" :href="`tel:${contact.phone}`" class="flex items-center gap-2.5 text-sm text-gray-700 hover:text-brand-700">
                                <svg class="h-4 w-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                {{ contact.phone }}
                            </a>
                            <a v-if="contact.email" :href="`mailto:${contact.email}`" class="flex items-center gap-2.5 text-sm text-gray-700 hover:text-brand-700">
                                <svg class="h-4 w-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                {{ contact.email }}
                            </a>
                            <a v-if="contact.website" :href="contact.website" target="_blank" rel="noopener" class="flex items-center gap-2.5 text-sm text-gray-700 hover:text-brand-700">
                                <svg class="h-4 w-4 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0zM3.6 9h16.8M3.6 15h16.8M12 3a15 15 0 010 18M12 3a15 15 0 000 18" /></svg>
                                Вэбсайт
                            </a>
                            <a v-if="contact.facebook" :href="contact.facebook" target="_blank" rel="noopener" class="flex items-center gap-2.5 text-sm text-gray-700 hover:text-brand-700">
                                <svg class="h-4 w-4 text-brand-600" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 10-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0022 12z" /></svg>
                                Facebook
                            </a>
                            <p v-if="!contact.phone && !contact.email && !contact.website && !contact.facebook" class="text-sm text-gray-400">Холбоо барих мэдээлэл оруулаагүй байна.</p>
                        </div>

                        <!-- Нээх товч -->
                        <template v-else>
                            <p class="mb-3 text-sm text-gray-500">Холбоо барих мэдээллийг харахын тулд дарна уу.</p>
                            <button class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700" @click="reveal">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11 11 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                {{ user ? 'Холбоо барих' : 'Нэвтэрч холбогдох' }}
                            </button>
                            <p v-if="!user" class="mt-2 text-center text-xs text-gray-400">Зөвхөн бүртгэлтэй хэрэглэгч харна.</p>
                        </template>

                        <Link v-if="professional.owned" href="/my/professional" class="mt-3 block text-center text-xs text-brand-700 hover:underline">Миний профайл засах</Link>
                    </div>
                </div>

                <BannerDisplay placement="home_sidebar" variant="box" :placeholder="true" />
            </aside>
        </div>
    </PublicLayout>
</template>
