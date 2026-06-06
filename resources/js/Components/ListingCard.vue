<script setup>
import { timeAgo } from '@/lib/date';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    listing: { type: Object, required: true },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const favorites = computed(() => page.props.auth?.favorites ?? []);
const isFav = computed(() => favorites.value.includes(props.listing.id));

function toggleFav() {
    if (!user.value) {
        router.visit('/login');
        return;
    }
    router.post(`/zar/${props.listing.id}/favorite`, {}, { preserveScroll: true, preserveState: true });
}

function priceLabel(l) {
    if (l.price_type === 'free') return 'Үнэгүй';
    if (l.price_type === 'giveaway') return 'Дайна';
    if (l.price === null || l.price === undefined) return 'Тохиролцоно';
    const p = Number(l.price).toLocaleString('mn-MN') + ' €';
    return l.price_type === 'negotiable' ? p + ' (VB)' : p;
}
</script>

<template>
    <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-card transition duration-300 hover:-translate-y-1 hover:border-gray-200 hover:shadow-card-lg">
        <!-- Хадгалах зүрх -->
        <button
            type="button"
            class="absolute right-2.5 top-2.5 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-white/85 shadow-sm ring-1 ring-black/5 backdrop-blur transition hover:scale-110 hover:bg-white active:scale-95"
            :aria-label="isFav ? 'Хадгалснаас хасах' : 'Хадгалах'"
            @click="toggleFav"
        >
            <svg class="h-5 w-5 transition" :class="isFav ? 'fill-red-500 text-red-500' : 'fill-none text-gray-500 group-hover:text-gray-700'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
        </button>

        <Link :href="`/zar/${listing.slug}`" class="flex flex-1 flex-col">
            <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                <img v-if="listing.cover" :src="listing.cover" :alt="listing.title" class="h-full w-full object-cover" />
                <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 text-gray-300">
                    <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <span v-if="listing.is_featured" class="absolute left-2.5 top-2.5 inline-flex items-center gap-1 rounded-full bg-amber-400 px-2 py-0.5 text-[11px] font-bold text-amber-900 shadow-sm">
                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l2.9 6.26L21 9.27l-4.5 4.39L17.8 21 12 17.27 6.2 21l1.3-7.34L3 9.27l6.1-1.01L12 2z" /></svg>
                    ОНЦЛОХ
                </span>
            </div>
            <div class="flex flex-1 flex-col p-3.5">
                <p class="text-base font-bold tracking-tight text-gray-900">{{ priceLabel(listing) }}</p>
                <h3 class="mt-0.5 line-clamp-2 text-sm text-gray-700 transition group-hover:text-brand-700">{{ listing.title }}</h3>
                <div class="mt-auto flex items-center justify-between gap-2 pt-2.5 text-xs text-gray-400">
                    <span class="inline-flex min-w-0 items-center gap-1">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span class="truncate">{{ listing.postal_code }} {{ listing.city }}</span>
                    </span>
                    <span class="shrink-0">{{ timeAgo(listing.created_at) }}</span>
                </div>
            </div>
        </Link>
    </div>
</template>
