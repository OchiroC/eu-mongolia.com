<script setup>
import { timeAgo } from '@/lib/date';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Heart, ImageOff, MapPin } from 'lucide-vue-next';
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
    return l.price_type === 'negotiable' ? p + ' VB' : p;
}
</script>

<template>
    <!-- Каталогийн хэлбэр: хайрцаггүй, сүүдэргүй — зураг ба текст. -->
    <div class="group relative flex flex-col">
        <button
            type="button"
            class="absolute right-2 top-2 z-10 flex h-8 w-8 items-center justify-center rounded-[3px] border border-brand-100 bg-white transition-colors hover:border-brand-300"
            :aria-label="isFav ? 'Хадгалснаас хасах' : 'Хадгалах'"
            @click="toggleFav"
        >
            <Heart class="h-4 w-4" :class="isFav ? 'fill-red-600 text-red-600' : 'text-brand-500'" />
        </button>

        <Link :href="`/zar/${listing.slug}`" class="flex flex-1 flex-col">
            <div class="relative aspect-[4/3] overflow-hidden rounded-[3px] bg-brand-50">
                <img v-if="listing.cover" :src="listing.cover" :alt="listing.title" class="h-full w-full object-cover transition-opacity group-hover:opacity-90" />
                <div v-else class="flex h-full w-full items-center justify-center text-brand-300">
                    <ImageOff class="h-8 w-8" stroke-width="1.25" />
                </div>
                <span v-if="listing.is_featured" class="absolute left-2 top-2 rounded-[2px] bg-signal-400 px-1.5 py-0.5 font-mono text-[10px] font-semibold uppercase tracking-wider text-brand-600">
                    Онцлох
                </span>
            </div>
            <div class="flex flex-1 flex-col pt-3">
                <p class="tabular font-mono text-[15px] font-semibold text-brand-600">{{ priceLabel(listing) }}</p>
                <h3 class="mt-1 line-clamp-2 text-sm leading-snug text-brand-500 group-hover:text-brand-600 group-hover:underline group-hover:underline-offset-2">{{ listing.title }}</h3>
                <div class="mt-auto flex items-center justify-between gap-2 pt-2.5 text-xs text-brand-400">
                    <span class="inline-flex min-w-0 items-center gap-1">
                        <MapPin class="h-3 w-3 shrink-0" />
                        <span class="truncate">{{ listing.postal_code }} {{ listing.city }}</span>
                    </span>
                    <span class="shrink-0">{{ timeAgo(listing.created_at) }}</span>
                </div>
            </div>
        </Link>
    </div>
</template>
