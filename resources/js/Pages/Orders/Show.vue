<script setup>
import TicketQr from '@/components/TicketQr.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import Button from '@/components/ui/Button.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
});

function pay() {
    router.post(`/orders/${props.order.id}/pay`);
}

function formatDate(value) {
    if (!value) return '';
    return new Date(value).toLocaleString('mn-MN', { dateStyle: 'long', timeStyle: 'short' });
}
</script>

<template>
    <Head :title="`Захиалга ${order.reference}`" />

    <PublicLayout>
        <div class="mx-auto max-w-2xl">
            <div class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between">
                    <h1 class="text-xl font-bold">Захиалга #{{ order.reference }}</h1>
                    <span
                        class="rounded-full px-3 py-1 text-sm"
                        :class="order.status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                    >
                        {{ order.status === 'paid' ? 'Төлөгдсөн' : 'Төлбөр хүлээгдэж буй' }}
                    </span>
                </div>

                <div class="mt-4 space-y-1 text-gray-600">
                    <p class="font-medium text-gray-900">{{ order.event?.title }}</p>
                    <p>📅 {{ formatDate(order.event?.starts_at) }}</p>
                    <p v-if="order.event?.venue">📍 {{ order.event.venue }}<span v-if="order.event.city">, {{ order.event.city }}</span></p>
                </div>

                <div class="mt-4 flex items-center justify-between border-t pt-4 text-lg font-semibold">
                    <span>Нийт дүн:</span>
                    <span>{{ order.total }}€</span>
                </div>

                <!-- Төлбөр хийгдээгүй бол mock төлбөрийн товч -->
                <div v-if="order.status !== 'paid'" class="mt-6">
                    <Button size="lg" class="w-full" @click="pay">Төлбөр төлөх (mock)</Button>
                    <p class="mt-2 text-center text-xs text-gray-400">
                        * Туршилтын төлбөр — карт холбоогүй. Дарвал захиалга баталгаажна.
                    </p>
                </div>
            </div>

            <!-- Тасалбарууд QR кодтой -->
            <div v-if="order.tickets.length" class="mt-6 space-y-4">
                <h2 class="text-lg font-semibold">Таны тасалбарууд</h2>
                <div
                    v-for="ticket in order.tickets"
                    :key="ticket.id"
                    class="flex items-center gap-4 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-100"
                >
                    <TicketQr :value="ticket.code" :size="120" />
                    <div>
                        <p class="font-semibold">{{ ticket.type ?? 'Тасалбар' }}</p>
                        <p class="mt-1 font-mono text-xs text-gray-400">{{ ticket.code }}</p>
                        <span
                            class="mt-2 inline-block rounded-full px-2 py-0.5 text-xs"
                            :class="ticket.status === 'used' ? 'bg-gray-200 text-gray-600' : 'bg-green-100 text-green-700'"
                        >
                            {{ ticket.status === 'used' ? 'Ашигласан' : 'Хүчинтэй' }}
                        </span>
                    </div>
                </div>
                <p class="text-center text-sm text-gray-400">Орох үед энэ QR кодыг үзүүлнэ үү.</p>
            </div>

            <div class="mt-6 text-center">
                <Link href="/my/tickets" class="text-sm text-brand-700 hover:underline">Миний бүх тасалбар →</Link>
            </div>
        </div>
    </PublicLayout>
</template>
