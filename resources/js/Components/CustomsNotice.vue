<script setup>
// Ачаа, илгээмжтэй холбоотой хуудас бүрт гарах гаалийн сануулга.
import { Link } from '@inertiajs/vue3';
import { ShieldAlert } from 'lucide-vue-next';

defineProps({
    compact: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex gap-3 border border-l-[3px] border-amber-200 border-l-amber-500 bg-amber-50/60 px-4 py-3 text-sm text-amber-950">
        <ShieldAlert class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
        <div>
            <p class="font-medium">Гаалийн дүрмийг анхаарна уу</p>
            <p v-if="!compact" class="mt-1 leading-relaxed text-amber-900/80">
                Европын холбооны гаднаас мах, сүүн бүтээгдэхүүн (борц, ааруул, бяслаг гэх мэт) авч орохыг хориглодог.
                Танихгүй хүний ачааг дотор нь юу байгааг шалгалгүйгээр бүү аваарай. Гааль дээр ачааны хариуцлагыг тээж яваа хүн хүлээнэ.
            </p>
            <Link href="/guides/germand-nisexed-achaa-gaaliin-durem" class="mt-1 inline-block font-medium underline underline-offset-2">Гаалийн дүрмийг унших</Link>
        </div>
    </div>
</template>
