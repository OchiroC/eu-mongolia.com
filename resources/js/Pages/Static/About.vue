<script setup>
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Бидний тухай" />

    <PublicLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="text-3xl font-bold text-gray-900">Бидний тухай</h1>
            <p class="mt-2 text-lg text-gray-500">Европ дахь монголчуудын нэгдсэн платформ</p>

            <div class="rich-content mt-8">
                <p><strong>EU Mongolia</strong> нь Европын орнуудад амьдарч буй монголчуудад зориулсан зар, мэдээ, эвентийн нэгдсэн платформ юм. Бид олон нийтийг нэг дороос холбож, өдөр тутмын хэрэгцээг хялбар болгохыг зорьдог.</p>

                <h2>Бидний санал болгох зүйлс</h2>
                <ul>
                    <li><strong>Зар</strong> — худалдаа, орон сууц, ажил, үйлчилгээ зэргийг үнэгүй нийтэлж, хайх.</li>
                    <li><strong>Мэдээ</strong> — Европ дахь монголчуудад хамаатай мэдээ мэдээлэл.</li>
                    <li><strong>Эвент</strong> — арга хэмжээ зарлах, тасалбар авах.</li>
                    <li><strong>Реклам</strong> — бизнесээ зорилтот үзэгчдэд хүргэх.</li>
                </ul>

                <h2>Бидний зорилго</h2>
                <p>Европ дахь монгол хүн бүр өөрт хэрэгтэй мэдээллээ хурдан, найдвартай олж авах, бие биедээ туслах боломжийг бүрдүүлэх.</p>

                <p>Бид <strong>хараат бус</strong> олон нийтийн платформ бөгөөд Европын Холбоо (EU)-той албан ёсны хамааралгүй болохыг анхаарна уу.</p>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <Link href="/zar" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">Зар үзэх</Link>
                <Link href="/contact" class="rounded-lg px-5 py-2.5 text-sm font-semibold text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50">Холбоо барих</Link>
            </div>
        </div>
    </PublicLayout>
</template>
