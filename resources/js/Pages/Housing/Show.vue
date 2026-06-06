<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    post: Object,
    similar: { type: Array, default: () => [] },
});

const user = computed(() => usePage().props.auth?.user);
const active = ref(0);

function price(v) {
    return v ? Number(v).toLocaleString('mn-MN') + '€' : 'Тохиролцоно';
}
</script>

<template>
    <Head :title="post.title" />

    <PublicLayout>
        <Link href="/housing" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">← Орон сууц руу буцах</Link>

        <div class="mt-4 grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="min-w-0">
                <!-- Зураг -->
                <div v-if="post.images.length" class="overflow-hidden rounded-2xl bg-gray-100">
                    <img :src="post.images[active]" :alt="post.title" class="max-h-[460px] w-full object-cover" />
                </div>
                <div v-if="post.images.length > 1" class="mt-2 flex gap-2 overflow-x-auto">
                    <button v-for="(img, i) in post.images" :key="i" class="h-16 w-20 shrink-0 overflow-hidden rounded-lg ring-2" :class="i === active ? 'ring-brand-500' : 'ring-transparent'" @click="active = i">
                        <img :src="img" alt="" class="h-full w-full object-cover" />
                    </button>
                </div>

                <h1 class="mt-5 text-2xl font-bold text-gray-900">{{ post.title }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ post.type_label }} · {{ post.district ? post.district + ', ' : '' }}{{ post.city }}<span v-if="post.country">, {{ post.country }}</span></p>

                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-xl bg-gray-50 p-3">
                        <p class="text-xs text-gray-400">Үнэ</p>
                        <p class="font-semibold text-gray-900">{{ price(post.price) }}<span v-if="post.price" class="text-xs font-normal text-gray-400">/сар</span></p>
                    </div>
                    <div v-if="post.deposit" class="rounded-xl bg-gray-50 p-3">
                        <p class="text-xs text-gray-400">Барьцаа</p>
                        <p class="font-semibold text-gray-900">{{ price(post.deposit) }}</p>
                    </div>
                    <div v-if="post.rooms" class="rounded-xl bg-gray-50 p-3">
                        <p class="text-xs text-gray-400">Өрөө</p>
                        <p class="font-semibold text-gray-900">{{ post.rooms }}</p>
                    </div>
                    <div v-if="post.size" class="rounded-xl bg-gray-50 p-3">
                        <p class="text-xs text-gray-400">Талбай</p>
                        <p class="font-semibold text-gray-900">{{ post.size }}м²</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2 text-sm">
                    <span v-if="post.available_from" class="rounded-full bg-brand-50 px-3 py-1 text-brand-700">Орох: {{ post.available_from }}</span>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-600">{{ post.furnished ? 'Тавилгатай' : 'Тавилгагүй' }}</span>
                    <span v-if="post.gender_pref" class="rounded-full bg-gray-100 px-3 py-1 text-gray-600">Хүйс: {{ post.gender_pref }}</span>
                </div>

                <div v-if="post.description" class="mt-6">
                    <h2 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">Тайлбар</h2>
                    <p class="whitespace-pre-line text-gray-700">{{ post.description }}</p>
                </div>

                <div v-if="similar.length" class="mt-8">
                    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Ижил хотын зар</h2>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <Link v-for="s in similar" :key="s.id" :href="`/housing/${s.slug}`" class="group overflow-hidden rounded-xl border border-gray-100 bg-white">
                            <div class="aspect-[4/3] overflow-hidden bg-gray-100">
                                <img v-if="s.cover" :src="s.cover" alt="" class="h-full w-full object-cover" />
                            </div>
                            <div class="p-2">
                                <p class="text-sm font-semibold text-gray-900">{{ s.price ? s.price + '€' : 'Тохиролцоно' }}</p>
                                <p class="line-clamp-1 text-xs text-gray-500">{{ s.title }}</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Холбоо барих -->
            <aside class="space-y-6 lg:sticky lg:top-20 lg:self-start">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-soft">
                    <p class="text-2xl font-bold text-gray-900">{{ price(post.price) }}<span v-if="post.price" class="text-sm font-normal text-gray-400">/сар</span></p>
                    <p class="mt-1 text-sm text-gray-400">Эзэмшигч: {{ post.user }}</p>

                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <template v-if="user">
                            <a v-if="post.contact_phone" :href="`tel:${post.contact_phone}`" class="block rounded-lg bg-brand-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-brand-700">📞 {{ post.contact_phone }}</a>
                            <p v-else class="text-sm text-gray-400">Утас оруулаагүй байна.</p>
                        </template>
                        <Link v-else href="/login" class="block rounded-lg bg-brand-600 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-brand-700">Холбоо барихын тулд нэвтрэх</Link>
                    </div>

                    <Link v-if="post.owned" href="/my/housing" class="mt-3 block text-center text-xs text-brand-700 hover:underline">Миний зар засах</Link>
                    <p class="mt-4 text-center text-xs text-gray-400">Урьдчилгаа төлбөр шаардсан этгээдээс болгоомжил.</p>
                </div>
            </aside>
        </div>
    </PublicLayout>
</template>
