<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    event: Object,
    stats: Object,
    ticketTypes: Array,
    orders: Array,
});

const statusMap = {
    paid: { label: 'Төлсөн', cls: 'bg-emerald-100 text-emerald-700' },
    pending: { label: 'Хүлээгдэж буй', cls: 'bg-amber-100 text-amber-700' },
    cancelled: { label: 'Цуцалсан', cls: 'bg-gray-100 text-gray-500' },
};

function money(n) {
    return Number(n || 0).toLocaleString('mn-MN') + '€';
}
</script>

<template>
    <Head :title="`${event.title} — борлуулалт`" />

    <AdminLayout>
        <template #title>Борлуулалтын тайлан</template>

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ event.title }}</h2>
                <p class="text-sm text-gray-400">{{ event.starts_at }}</p>
            </div>
            <div class="flex gap-2">
                <Link :href="`/admin/events/${event.id}/edit`" class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50">Засах</Link>
                <Link href="/admin/check-in" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Тасалбар шалгах</Link>
            </div>
        </div>

        <!-- Мэдээллийн эвент -->
        <div v-if="!event.has_tickets" class="rounded-2xl bg-white p-8 text-center shadow-sm ring-1 ring-gray-100">
            <p class="font-medium text-gray-700">Энэ бол мэдээллийн эвент</p>
            <p class="text-sm text-gray-400">Тасалбар зарагдаагүй тул борлуулалтын мэдээлэл байхгүй.</p>
        </div>

        <template v-else>
            <!-- Хураангуй картууд -->
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-400">Орлого (төлсөн)</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ money(stats.revenue) }}</p>
                    <p class="mt-0.5 text-xs text-gray-400">{{ stats.orders_paid }} захиалга</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-400">Зарагдсан тасалбар</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats.sold }} <span class="text-base font-normal text-gray-400">/ {{ stats.capacity }}</span></p>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-brand-500" :style="{ width: stats.capacity ? Math.min(100, (stats.sold / stats.capacity) * 100) + '%' : '0%' }"></div>
                    </div>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-400">Шалгасан (орсон)</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats.checked_in }}</p>
                    <p class="mt-0.5 text-xs text-gray-400">{{ stats.valid }} хүчинтэй үлдсэн</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100">
                    <p class="text-sm text-gray-400">Хүлээгдэж буй</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats.orders_pending }}</p>
                    <p class="mt-0.5 text-xs text-gray-400">төлбөр хүлээгдэж буй захиалга</p>
                </div>
            </div>

            <!-- Тасалбарын төрлөөр -->
            <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                <h3 class="border-b border-gray-100 px-5 py-3 font-semibold text-gray-900">Тасалбарын төрлөөр</h3>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-400">
                        <tr>
                            <th class="px-5 py-2.5 font-medium">Төрөл</th>
                            <th class="px-5 py-2.5 font-medium">Үнэ</th>
                            <th class="px-5 py-2.5 font-medium">Зарагдсан</th>
                            <th class="px-5 py-2.5 font-medium">Үлдсэн</th>
                            <th class="px-5 py-2.5 text-right font-medium">Орлого</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="t in ticketTypes" :key="t.name">
                            <td class="px-5 py-3 font-medium text-gray-800">{{ t.name }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ money(t.price) }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ t.sold }} / {{ t.quantity }}</td>
                            <td class="px-5 py-3 text-gray-600">{{ t.remaining }}</td>
                            <td class="px-5 py-3 text-right font-medium text-gray-800">{{ money(t.revenue) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Захиалгууд -->
            <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                <h3 class="border-b border-gray-100 px-5 py-3 font-semibold text-gray-900">Захиалгууд ({{ orders.length }})</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-400">
                            <tr>
                                <th class="px-5 py-2.5 font-medium">Дугаар</th>
                                <th class="px-5 py-2.5 font-medium">Худалдан авагч</th>
                                <th class="px-5 py-2.5 font-medium">Тасалбар</th>
                                <th class="px-5 py-2.5 font-medium">Дүн</th>
                                <th class="px-5 py-2.5 font-medium">Төлөв</th>
                                <th class="px-5 py-2.5 font-medium">Огноо</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="o in orders" :key="o.id" class="hover:bg-gray-50/50">
                                <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ o.reference }}</td>
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-800">{{ o.buyer_name }}</p>
                                    <p class="text-xs text-gray-400">{{ o.buyer_email }}</p>
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ o.tickets }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ money(o.total) }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusMap[o.status]?.cls">{{ statusMap[o.status]?.label || o.status }}</span>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-400">{{ o.created_at }}</td>
                            </tr>
                            <tr v-if="!orders.length">
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">Захиалга алга байна.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </AdminLayout>
</template>
