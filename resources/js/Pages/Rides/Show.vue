<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDateTime } from '@/lib/date';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({ ride: Object });

const user = computed(() => usePage().props.auth?.user);
</script>

<template>
    <Head :title="`${ride.from_city} → ${ride.to_city}`" />

    <PublicLayout>
        <div class="mx-auto max-w-2xl">
            <Link href="/rides" class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline">← Аялал руу буцах</Link>

            <div class="mt-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-soft">
                <div class="flex items-center gap-3 text-2xl font-bold text-gray-900">
                    <span>{{ ride.from_city }}</span>
                    <svg class="h-6 w-6 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    <span>{{ ride.to_city }}</span>
                    <span v-if="ride.status === 'closed'" class="rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">Хаагдсан</span>
                </div>
                <p v-if="ride.from_country || ride.to_country" class="mt-1 text-sm text-gray-400">{{ ride.from_country }} → {{ ride.to_country }}</p>

                <div class="mt-5 grid grid-cols-2 gap-4 border-t border-gray-100 pt-5 text-sm sm:grid-cols-3">
                    <div>
                        <p class="text-gray-400">Хөдлөх</p>
                        <p class="mt-0.5 font-medium text-gray-900">{{ formatDateTime(ride.depart_at) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Сул суудал</p>
                        <p class="mt-0.5 font-medium text-gray-900">{{ ride.seats }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Нэг суудлын үнэ</p>
                        <p class="mt-0.5 font-medium text-gray-900">{{ ride.price || 'Тохиролцоно' }}</p>
                    </div>
                </div>

                <div v-if="ride.notes" class="mt-5 border-t border-gray-100 pt-5">
                    <p class="mb-1 text-sm font-medium text-gray-700">Нэмэлт мэдээлэл</p>
                    <p class="whitespace-pre-line text-sm text-gray-600">{{ ride.notes }}</p>
                </div>

                <div class="mt-5 border-t border-gray-100 pt-5">
                    <p class="mb-2 text-sm font-medium text-gray-700">Жолооч: {{ ride.user }}</p>
                    <template v-if="user">
                        <a v-if="ride.contact_phone" :href="`tel:${ride.contact_phone}`" class="block rounded-lg bg-brand-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-brand-700 hover:shadow-brand-glow active:translate-y-0">📞 {{ ride.contact_phone }}</a>
                        <p v-else class="text-sm text-gray-400">Утас оруулаагүй байна.</p>
                    </template>
                    <Link v-else href="/login" class="block rounded-lg bg-brand-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-brand-700 hover:shadow-brand-glow active:translate-y-0">Холбоо барихын тулд нэвтрэх</Link>
                    <Link v-if="ride.owned" href="/my/rides" class="mt-2 block text-center text-xs text-brand-700 hover:underline">Миний аялал засах</Link>
                </div>

                <p class="mt-4 text-center text-xs text-gray-400">Урьдчилгаа төлбөр шаардсан этгээдээс болгоомжил. Аюулгүй байдлаа эрхэмлээрэй.</p>
            </div>
        </div>
    </PublicLayout>
</template>
