<script setup>
import Logo from '@/Components/Logo.vue';
import NotificationBell from '@/Components/NotificationBell.vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ChevronDown, Heart, LayoutGrid, LogOut, Menu, MessageSquare,
    Plus, Search, ShieldCheck, Tag, UserRound, X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

defineProps({
    // true бол main контейнергүй — хуудас өөрөө бүтэн өргөнтэй хэсгүүдээ удирдана (нүүр гэх мэт).
    bleed: { type: Boolean, default: false },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.roles?.includes('admin'));
const unreadMessages = computed(() => page.props.unreadMessages ?? 0);
const appName = import.meta.env.VITE_APP_NAME || 'OM137';

/*
 * Цэсийг 12-оос 6 үндсэн + "Бусад" болгон цөөлсөн. Дараалал нь шинээр
 * ирсэн хүний замыг дагана: буух → чиглэл авах → байр → ажил → зах → нийгэмлэг.
 */
const primaryNav = [
    // Нислэг, хамт аялах, ачаа гурав нэг төв (TravelTabs).
    { name: 'Нислэг', href: '/flights', match: ['/flights', '/damjih', '/rides', '/achaa'] },
    { name: 'Гарын авлага', href: '/guides' },
    { name: 'Орон сууц', href: '/housing' },
    { name: 'Ажил', href: '/jobs' },
    { name: 'Зар', href: '/zar' },
    { name: 'Эвент', href: '/events' },
];
const moreNav = [
    { name: 'Газрын зураг', href: '/map' },
    { name: 'Мэдээ', href: '/news' },
    { name: 'Асуулт хариулт', href: '/questions' },
    { name: 'Мэргэжлийн туслах', href: '/professionals' },
    { name: 'Бизнес', href: '/businesses' },
    { name: 'Хүүхэд, гэр бүл', href: '/kids' },
    { name: 'Элчин сайдын яам', href: '/embassy' },
];

function isActive(href) {
    if (href === '/') return page.url === '/';
    return page.url === href || page.url.startsWith(href + '/') || page.url.startsWith(href + '?');
}
const itemActive = (item) => (item.match ?? [item.href]).some(isActive);
const moreActive = computed(() => moreNav.some((i) => isActive(i.href)));

const initials = computed(() => (user.value?.name || '?').trim().charAt(0).toUpperCase());

const mobileOpen = ref(false);
const userOpen = ref(false);
const moreOpen = ref(false);
const userRef = ref(null);
const moreRef = ref(null);

function onClickOutside(e) {
    if (userRef.value && !userRef.value.contains(e.target)) userOpen.value = false;
    if (moreRef.value && !moreRef.value.contains(e.target)) moreOpen.value = false;
}
function onKey(e) {
    if (e.key === 'Escape') {
        userOpen.value = false;
        moreOpen.value = false;
        mobileOpen.value = false;
    }
}
onMounted(() => {
    document.addEventListener('click', onClickOutside);
    document.addEventListener('keydown', onKey);
});
onUnmounted(() => {
    document.removeEventListener('click', onClickOutside);
    document.removeEventListener('keydown', onKey);
});

const accountLinks = computed(() => [
    { name: 'Миний самбар', href: '/dashboard', icon: LayoutGrid },
    { name: 'Миний зар', href: '/my/zar', icon: Tag },
    { name: 'Зурвас', href: '/messages', icon: MessageSquare, badge: unreadMessages.value },
    { name: 'Хадгалсан', href: '/my/favorites', icon: Heart },
    { name: 'Профайл', href: '/profile', icon: UserRound },
    ...(isAdmin.value ? [{ name: 'Удирдлага', href: '/admin', icon: ShieldCheck }] : []),
]);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white text-brand-600">
        <!-- Толгой: хавтгай, доод талдаа нэг үсэн зураас. -->
        <header class="sticky top-0 z-30 border-b border-brand-100 bg-white">
            <div class="mx-auto flex h-16 max-w-7xl items-center gap-8 px-4 sm:px-6 lg:px-8">
                <Logo size="md" />

                <!-- Үндсэн цэс — идэвхтэйг нь доогуур зураасаар тэмдэглэнэ. -->
                <nav class="hidden h-full items-stretch gap-6 lg:flex">
                    <Link
                        v-for="item in primaryNav"
                        :key="item.href"
                        :href="item.href"
                        class="relative flex items-center text-[14px] font-medium transition-colors"
                        :class="itemActive(item) ? 'text-brand-600' : 'text-brand-400 hover:text-brand-600'"
                    >
                        {{ item.name }}
                        <span v-if="itemActive(item)" class="absolute inset-x-0 -bottom-px h-0.5 bg-brand-600" />
                    </Link>

                    <div ref="moreRef" class="relative flex items-stretch">
                        <button
                            type="button"
                            class="relative flex items-center gap-1 text-[14px] font-medium transition-colors"
                            :class="moreActive || moreOpen ? 'text-brand-600' : 'text-brand-400 hover:text-brand-600'"
                            :aria-expanded="moreOpen"
                            @click="moreOpen = !moreOpen"
                        >
                            Бусад
                            <ChevronDown class="h-3.5 w-3.5 transition-transform" :class="moreOpen ? 'rotate-180' : ''" />
                            <span v-if="moreActive" class="absolute inset-x-0 -bottom-px h-0.5 bg-brand-600" />
                        </button>
                        <div
                            v-if="moreOpen"
                            class="absolute left-0 top-full mt-px w-60 border border-brand-100 bg-white py-1.5"
                            @click="moreOpen = false"
                        >
                            <Link
                                v-for="item in moreNav"
                                :key="item.href"
                                :href="item.href"
                                class="block px-4 py-2 text-sm transition-colors hover:bg-brand-50"
                                :class="isActive(item.href) ? 'font-medium text-brand-600' : 'text-brand-500'"
                            >
                                {{ item.name }}
                            </Link>
                        </div>
                    </div>
                </nav>

                <div class="ml-auto flex items-center gap-1">
                    <Link href="/search" class="flex h-9 w-9 items-center justify-center rounded-md text-brand-400 transition-colors hover:bg-brand-50 hover:text-brand-600" aria-label="Хайх">
                        <Search class="h-[18px] w-[18px]" />
                    </Link>

                    <template v-if="user">
                        <Link href="/messages" class="relative hidden h-9 w-9 items-center justify-center rounded-md text-brand-400 transition-colors hover:bg-brand-50 hover:text-brand-600 sm:flex" aria-label="Зурвас">
                            <MessageSquare class="h-[18px] w-[18px]" />
                            <span v-if="unreadMessages" class="tabular absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-[3px] bg-signal-400 px-1 font-mono text-[10px] font-semibold text-brand-600">
                                {{ unreadMessages > 9 ? '9+' : unreadMessages }}
                            </span>
                        </Link>
                        <NotificationBell class="hidden sm:block" />

                        <Link href="/zar/new" class="ml-2 hidden h-9 items-center gap-1.5 rounded-md bg-brand-600 px-3.5 text-sm font-medium text-white transition-colors hover:bg-brand-500 sm:inline-flex">
                            <Plus class="h-4 w-4" />
                            Зар нэмэх
                        </Link>

                        <!-- Хэрэглэгчийн цэс -->
                        <div ref="userRef" class="relative ml-2 hidden sm:block">
                            <button
                                type="button"
                                class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full border border-brand-100 bg-brand-50 transition-colors hover:border-brand-300"
                                :aria-expanded="userOpen"
                                aria-label="Хэрэглэгчийн цэс"
                                @click="userOpen = !userOpen"
                            >
                                <img v-if="user.avatar_url" :src="user.avatar_url" alt="" class="h-full w-full object-cover" />
                                <span v-else class="text-sm font-semibold text-brand-600">{{ initials }}</span>
                            </button>

                            <div
                                v-if="userOpen"
                                class="absolute right-0 mt-2 w-64 border border-brand-100 bg-white"
                                @click="userOpen = false"
                            >
                                <div class="border-b border-brand-100 px-4 py-3">
                                    <p class="truncate text-sm font-semibold text-brand-600">{{ user.name }}</p>
                                    <p class="truncate text-xs text-brand-400">{{ user.email }}</p>
                                </div>
                                <div class="py-1.5">
                                    <Link
                                        v-for="item in accountLinks"
                                        :key="item.href"
                                        :href="item.href"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-brand-500 transition-colors hover:bg-brand-50 hover:text-brand-600"
                                    >
                                        <component :is="item.icon" class="h-4 w-4 text-brand-400" />
                                        {{ item.name }}
                                        <span v-if="item.badge" class="tabular ml-auto rounded-[3px] bg-signal-400 px-1.5 font-mono text-[10px] font-semibold text-brand-600">{{ item.badge }}</span>
                                    </Link>
                                </div>
                                <div class="border-t border-brand-100 py-1.5">
                                    <Link href="/logout" method="post" as="button" class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-red-600 transition-colors hover:bg-red-50">
                                        <LogOut class="h-4 w-4" />
                                        Гарах
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-else>
                        <Link href="/login" class="ml-2 hidden h-9 items-center px-3 text-sm font-medium text-brand-500 transition-colors hover:text-brand-600 sm:inline-flex">Нэвтрэх</Link>
                        <Link href="/register" class="hidden h-9 items-center rounded-md bg-brand-600 px-3.5 text-sm font-medium text-white transition-colors hover:bg-brand-500 sm:inline-flex">Бүртгүүлэх</Link>
                    </template>

                    <button
                        type="button"
                        class="ml-1 flex h-9 w-9 items-center justify-center rounded-md text-brand-600 hover:bg-brand-50 lg:hidden"
                        :aria-expanded="mobileOpen"
                        aria-label="Цэс"
                        @click="mobileOpen = !mobileOpen"
                    >
                        <X v-if="mobileOpen" class="h-5 w-5" />
                        <Menu v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Мобайл цэс: бүлэглэсэн жагсаалт. -->
            <div v-if="mobileOpen" class="max-h-[calc(100vh-4rem)] overflow-y-auto border-t border-brand-100 bg-white lg:hidden">
                <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6">
                    <p class="kicker px-3 pb-2">Үндсэн</p>
                    <Link
                        v-for="item in primaryNav"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center justify-between border-l-2 px-3 py-2.5 text-[15px]"
                        :class="itemActive(item) ? 'border-brand-600 font-medium text-brand-600' : 'border-transparent text-brand-500'"
                        @click="mobileOpen = false"
                    >{{ item.name }}</Link>

                    <p class="kicker px-3 pb-2 pt-5">Бусад</p>
                    <Link
                        v-for="item in moreNav"
                        :key="item.href"
                        :href="item.href"
                        class="block border-l-2 px-3 py-2.5 text-[15px]"
                        :class="isActive(item.href) ? 'border-brand-600 font-medium text-brand-600' : 'border-transparent text-brand-500'"
                        @click="mobileOpen = false"
                    >{{ item.name }}</Link>

                    <div class="mt-5 border-t border-brand-100 pt-4">
                        <template v-if="user">
                            <Link href="/zar/new" class="mb-3 flex h-11 items-center justify-center gap-1.5 rounded-md bg-brand-600 text-sm font-medium text-white" @click="mobileOpen = false">
                                <Plus class="h-4 w-4" /> Зар нэмэх
                            </Link>
                            <Link
                                v-for="item in accountLinks"
                                :key="item.href"
                                :href="item.href"
                                class="flex items-center gap-3 px-3 py-2.5 text-[15px] text-brand-500"
                                @click="mobileOpen = false"
                            >
                                <component :is="item.icon" class="h-4 w-4 text-brand-400" />
                                {{ item.name }}
                                <span v-if="item.badge" class="tabular ml-auto rounded-[3px] bg-signal-400 px-1.5 font-mono text-[10px] font-semibold text-brand-600">{{ item.badge }}</span>
                            </Link>
                            <Link href="/logout" method="post" as="button" class="flex w-full items-center gap-3 px-3 py-2.5 text-left text-[15px] text-red-600">
                                <LogOut class="h-4 w-4" /> Гарах
                            </Link>
                        </template>
                        <div v-else class="grid grid-cols-2 gap-2">
                            <Link href="/login" class="flex h-11 items-center justify-center rounded-md border border-brand-200 text-sm font-medium text-brand-600">Нэвтрэх</Link>
                            <Link href="/register" class="flex h-11 items-center justify-center rounded-md bg-brand-600 text-sm font-medium text-white">Бүртгүүлэх</Link>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash мессеж: хавтгай, зүүн талдаа өнгөт зураас. -->
        <div v-if="page.props.flash?.success || page.props.flash?.error" class="mx-auto mt-6 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div v-if="page.props.flash?.success" class="border border-l-[3px] border-emerald-200 border-l-emerald-600 bg-emerald-50/60 px-4 py-3 text-sm text-emerald-900">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="border border-l-[3px] border-red-200 border-l-red-600 bg-red-50/60 px-4 py-3 text-sm text-red-900">
                {{ page.props.flash.error }}
            </div>
        </div>

        <main :class="bleed ? 'flex-1' : 'mx-auto w-full max-w-7xl flex-1 px-4 py-10 sm:px-6 lg:px-8'">
            <slot />
        </main>

        <!-- Хөл -->
        <footer class="border-t border-brand-100 bg-white">
            <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-12 lg:px-8">
                <div class="md:col-span-4">
                    <Logo size="md" />
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-brand-500">
                        Франкфурт орчмын болон Франкфуртаар дамжин ирж буй монголчуудын мэдээллийн сайт.
                    </p>
                    <p class="mt-3 max-w-xs text-xs leading-relaxed text-brand-400">
                        {{ appName }} нь хувийн санаачилгаар ажилладаг хараат бус сайт бөгөөд МИАТ болон бусад агаарын тээврийн компанитай холбоогүй.
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-8 sm:grid-cols-3 md:col-span-8">
                    <div>
                        <p class="kicker mb-4">Үйлчилгээ</p>
                        <ul class="space-y-2.5 text-sm">
                            <li v-for="item in primaryNav.slice(0, 5)" :key="item.href">
                                <Link :href="item.href" class="text-brand-500 transition-colors hover:text-brand-600">{{ item.name }}</Link>
                            </li>
                            <li><Link href="/damjih" class="text-brand-500 transition-colors hover:text-brand-600">Франкфуртаар дамжих</Link></li>
                            <li><Link href="/achaa" class="text-brand-500 transition-colors hover:text-brand-600">Ачаа, илгээмж</Link></li>
                            <li><Link href="/rides" class="text-brand-500 transition-colors hover:text-brand-600">Хамт аялах</Link></li>
                        </ul>
                    </div>
                    <div>
                        <p class="kicker mb-4">Нийгэмлэг</p>
                        <ul class="space-y-2.5 text-sm">
                            <li><Link href="/events" class="text-brand-500 transition-colors hover:text-brand-600">Эвент</Link></li>
                            <li v-for="item in moreNav.slice(0, 4)" :key="item.href">
                                <Link :href="item.href" class="text-brand-500 transition-colors hover:text-brand-600">{{ item.name }}</Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p class="kicker mb-4">Тусламж</p>
                        <ul class="space-y-2.5 text-sm">
                            <li><a href="/ireh" class="text-brand-500 transition-colors hover:text-brand-600">Ирэх өдрийн багц</a></li>
                            <li><Link href="/about" class="text-brand-500 transition-colors hover:text-brand-600">Бидний тухай</Link></li>
                            <li><Link href="/embassy" class="text-brand-500 transition-colors hover:text-brand-600">Элчин сайдын яам</Link></li>
                            <li><Link href="/contact" class="text-brand-500 transition-colors hover:text-brand-600">Холбоо барих</Link></li>
                            <li><Link href="/terms" class="text-brand-500 transition-colors hover:text-brand-600">Нөхцөл</Link></li>
                            <li><Link href="/privacy" class="text-brand-500 transition-colors hover:text-brand-600">Нууцлал</Link></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="border-t border-brand-100">
                <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-5 font-mono text-[11px] uppercase tracking-[0.12em] text-brand-400 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                    <span>© 2026 {{ appName }}</span>
                    <span>FRA · Frankfurt am Main</span>
                </div>
            </div>
        </footer>
    </div>
</template>
