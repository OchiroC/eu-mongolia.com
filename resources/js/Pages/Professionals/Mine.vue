<script setup>
import Button from '@/components/ui/Button.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ professional: Object });

const statusMap = {
    pending: { label: 'Хяналтад байна', cls: 'bg-amber-100 text-amber-700' },
    active: { label: 'Нийтлэгдсэн', cls: 'bg-emerald-100 text-emerald-700' },
    inactive: { label: 'Идэвхгүй', cls: 'bg-gray-100 text-gray-600' },
};

function promote() {
    if (confirm('30 хоног онцлох болгох уу? (mock төлбөр)')) {
        router.post(`/professionals/${props.professional.id}/promote`, {}, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Миний мэргэжилтний профайл" />

    <PublicLayout>
        <div class="mx-auto max-w-2xl">
            <h1 class="text-2xl font-bold text-gray-900">Миний мэргэжилтний профайл</h1>

            <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-soft">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-semibold text-gray-900">{{ professional.name }}</h2>
                            <span v-if="professional.is_verified" class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700">Баталгаажсан</span>
                        </div>
                        <p v-if="professional.profession" class="text-sm text-brand-700">{{ professional.profession }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-3 py-1 text-xs font-medium" :class="statusMap[professional.status]?.cls">
                        {{ statusMap[professional.status]?.label }}
                    </span>
                </div>

                <p v-if="professional.status === 'pending'" class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Таны профайл админы хяналтад байна. Баталгаажсаны дараа лавлахад харагдана.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <Link :href="`/professionals/${professional.id}/edit`">
                        <Button variant="secondary">Засах</Button>
                    </Link>
                    <Link v-if="professional.status === 'active'" :href="`/professionals/${professional.slug}`">
                        <Button variant="outline">Профайл үзэх</Button>
                    </Link>
                </div>
            </div>

            <!-- Онцлох болгох (mock) -->
            <div class="mt-5 rounded-2xl border p-6 shadow-soft" :class="professional.is_currently_featured ? 'border-amber-200 bg-amber-50/40' : 'border-gray-100 bg-white'">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.9 6.26L22 9.27l-5 4.87L18.18 22 12 18.27 5.82 22 7 14.14l-5-4.87 7.1-1.01L12 2z" /></svg>
                    <h3 class="font-semibold text-gray-900">Онцлох байршуулалт</h3>
                </div>
                <p class="mt-1 text-sm text-gray-500">Онцлох мэргэжилтэн лавлахын дээд талд, тэмдэгтэйгээр харагдана.</p>

                <p v-if="professional.is_currently_featured" class="mt-3 text-sm font-medium text-amber-700">
                    Идэвхтэй · {{ professional.featured_until }} хүртэл
                </p>
                <Button class="mt-3" @click="promote">{{ professional.is_currently_featured ? 'Сунгах (30 хоног)' : 'Онцлох болгох (30 хоног)' }}</Button>
            </div>
        </div>
    </PublicLayout>
</template>
