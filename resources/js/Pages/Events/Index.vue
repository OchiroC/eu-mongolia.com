<script setup>
import BannerDisplay from '@/Components/BannerDisplay.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDateTime } from '@/lib/date';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    events: Object,
    filters: Object,
});

const search = ref(props.filters.search ?? '');

let timer = null;
watch(search, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/events', { ...props.filters, search: value || undefined }, {
            preserveState: true,
            replace: true,
        });
    }, 350);
});

</script>

<template>
    <Head title="Эвент" />

    <PublicLayout>
        <BannerDisplay placement="home_top" class="mb-6" />

        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-2xl font-bold">Эвент, арга хэмжээ</h1>
            <input v-model="search" type="search" placeholder="Хайх..." class="w-full rounded-md border-gray-300 sm:w-64" />
        </div>

        <div v-if="events.data.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="event in events.data"
                :key="event.id"
                :href="`/events/${event.slug}`"
                class="group overflow-hidden rounded-lg bg-white shadow-card ring-1 ring-gray-100 transition duration-300 hover:-translate-y-1 hover:shadow-card-lg"
            >
                <div class="aspect-video overflow-hidden bg-gray-100">
                    <img v-if="event.cover_image" :src="event.cover_image" :alt="event.title" class="h-full w-full object-cover" />
                </div>
                <div class="p-4">
                    <p class="text-xs font-medium text-brand-700">{{ formatDateTime(event.starts_at) }}</p>
                    <h2 class="mt-1 font-semibold group-hover:text-brand-700">{{ event.title }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ event.venue }}<span v-if="event.city">, {{ event.city }}</span></p>
                    <p class="mt-2 text-sm font-medium text-gray-800">
                        <span v-if="event.ticket_types_min_price > 0">{{ event.ticket_types_min_price }}€-с эхэлнэ</span>
                        <span v-else class="text-green-600">Үнэгүй</span>
                    </p>
                </div>
            </Link>
        </div>

        <p v-else class="rounded-lg bg-white py-12 text-center text-gray-500 ring-1 ring-gray-100">Эвент олдсонгүй.</p>
    </PublicLayout>
</template>
