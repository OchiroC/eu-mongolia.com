<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { formatDateTime } from '@/lib/date';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    event: Object,
    ticketTypes: Array,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const form = useForm({
    items: props.ticketTypes.map((t) => ({ ticket_type_id: t.id, quantity: 0 })),
    buyer_name: user.value?.name ?? '',
    buyer_email: user.value?.email ?? '',
});

const total = computed(() =>
    props.ticketTypes.reduce((sum, t, i) => sum + Number(t.price) * form.items[i].quantity, 0),
);

const hasSelection = computed(() => form.items.some((i) => i.quantity > 0));


function submit() {
    form.post(`/events/${props.event.slug}/checkout`);
}
</script>

<template>
    <Head :title="event.title" />

    <PublicLayout>
        <Link href="/events" class="text-sm text-brand-700 hover:underline">← Эвентүүд рүү буцах</Link>

        <div class="mt-4 grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <img v-if="event.cover_image" :src="event.cover_image" :alt="event.title" class="mb-6 w-full rounded-lg" />
                <h1 class="text-3xl font-bold">{{ event.title }}</h1>
                <div class="mt-3 space-y-1 text-gray-600">
                    <p>📅 {{ formatDateTime(event.starts_at) }}</p>
                    <p v-if="event.venue">📍 {{ event.venue }}<span v-if="event.city">, {{ event.city }}</span><span v-if="event.country"> ({{ event.country }})</span></p>
                    <p v-if="event.organizer">👤 {{ event.organizer.name }}</p>
                </div>
                <div class="rich-content mt-6 max-w-none" v-html="event.description"></div>
            </div>

            <!-- Тасалбар захиалах -->
            <div v-if="event.has_tickets" class="h-fit rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <h2 class="mb-4 text-lg font-semibold">Тасалбар авах</h2>

                <div v-if="!user" class="rounded-md bg-brand-50 p-4 text-sm text-brand-800">
                    Тасалбар авахын тулд эхлээд
                    <Link href="/login" class="font-medium underline">нэвтэрнэ</Link> үү.
                </div>

                <form v-else @submit.prevent="submit" class="space-y-4">
                    <div v-for="(t, i) in ticketTypes" :key="t.id" class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ t.name }}</p>
                            <p class="text-sm text-gray-500">{{ t.price }}€ · үлдсэн {{ t.remaining }}</p>
                        </div>
                        <input
                            v-model.number="form.items[i].quantity"
                            type="number"
                            min="0"
                            :max="t.remaining"
                            class="w-20 rounded-md border-gray-300"
                        />
                    </div>

                    <div class="border-t pt-4">
                        <label class="block text-sm font-medium text-gray-700">Нэр</label>
                        <input v-model="form.buyer_name" type="text" class="mt-1 w-full rounded-md border-gray-300" />
                        <p v-if="form.errors.buyer_name" class="mt-1 text-sm text-red-600">{{ form.errors.buyer_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">И-мэйл</label>
                        <input v-model="form.buyer_email" type="email" class="mt-1 w-full rounded-md border-gray-300" />
                        <p v-if="form.errors.buyer_email" class="mt-1 text-sm text-red-600">{{ form.errors.buyer_email }}</p>
                    </div>

                    <div class="flex items-center justify-between border-t pt-4 text-lg font-semibold">
                        <span>Нийт:</span>
                        <span>{{ total }}€</span>
                    </div>

                    <button
                        type="submit"
                        :disabled="!hasSelection || form.processing"
                        class="w-full rounded-md bg-brand-700 px-4 py-2.5 font-medium text-white hover:bg-brand-800 disabled:opacity-50"
                    >
                        Захиалах
                    </button>
                </form>
            </div>

            <!-- Мэдээллийн эвент (тасалбаргүй) -->
            <div v-else class="h-fit rounded-lg bg-white p-5 shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <h2 class="text-lg font-semibold">Мэдээллийн эвент</h2>
                </div>
                <p class="mt-2 text-sm text-gray-500">Энэ эвент тасалбаргүй, нээлттэй оролцоно.</p>
                <div class="mt-4 space-y-2 border-t pt-4 text-sm text-gray-600">
                    <p>📅 {{ formatDateTime(event.starts_at) }}</p>
                    <p v-if="event.venue">📍 {{ event.venue }}<span v-if="event.city">, {{ event.city }}</span></p>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
