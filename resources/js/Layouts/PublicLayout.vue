<script setup>
import NotificationBell from '@/Components/NotificationBell.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.roles?.includes('admin'));
const unreadMessages = computed(() => page.props.unreadMessages ?? 0);

const mobileOpen = ref(false);
const menuOpen = ref(false);
const menuRef = ref(null);

const nav = [
    { name: 'Нүүр', href: '/' },
    { name: 'Зар', href: '/zar' },
    { name: 'Орон сууц', href: '/housing' },
    { name: 'Ажил', href: '/jobs' },
    { name: 'Аялал', href: '/rides' },
    { name: 'Асуулт', href: '/questions' },
    { name: 'Guide', href: '/guides' },
    { name: 'Мэдээ', href: '/news' },
    { name: 'Эвент', href: '/events' },
    { name: 'Туслах', href: '/professionals' },
    { name: 'Бизнес', href: '/businesses' },
    { name: 'Хүүхэд', href: '/kids' },
];

function isActive(href) {
    if (href === '/') return page.url === '/';
    return page.url.startsWith(href);
}

const initials = computed(() => (user.value?.name || '?').trim().charAt(0).toUpperCase());

function onClickOutside(e) {
    if (menuRef.value && !menuRef.value.contains(e.target)) {
        menuOpen.value = false;
    }
}
onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => document.removeEventListener('click', onClickOutside));
</script>

<template>
    <div class="flex min-h-screen flex-col bg-gray-50 text-gray-900">
        <!-- Толгой -->
        <header class="sticky top-0 z-30 border-b border-gray-100 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
                <Link href="/" class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 font-bold text-white">EU</span>
                    <span class="text-lg font-bold">Mongolia</span>
                </Link>

                <div class="flex items-center gap-2 text-sm">
                    <Link href="/search" class="rounded-lg p-2 text-gray-500 transition hover:bg-gray-50 hover:text-brand-600" aria-label="Хайх">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </Link>
                    <template v-if="user">
                        <Link href="/zar/new" class="hidden rounded-full bg-brand-600 px-4 py-2 font-medium text-white hover:bg-brand-700 sm:inline-flex">
                            + Зар нэмэх
                        </Link>
                        <Link href="/messages" class="relative hidden rounded-lg p-2 text-gray-500 hover:bg-gray-50 hover:text-brand-600 sm:block" aria-label="Зурвас">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.5C3.5 15.3 3 13.7 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            <span v-if="unreadMessages" class="absolute right-0.5 top-0.5 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-brand-600 px-1 text-[10px] font-bold text-white">{{ unreadMessages > 9 ? '9+' : unreadMessages }}</span>
                        </Link>
                        <NotificationBell class="hidden sm:block" />
                        <Link href="/my/favorites" class="hidden rounded-lg p-2 text-gray-500 hover:bg-gray-50 hover:text-red-500 sm:block" aria-label="Хадгалсан">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                        </Link>

                        <!-- Аватар dropdown -->
                        <div ref="menuRef" class="relative hidden sm:block">
                            <button class="flex items-center gap-2 rounded-full p-1 pr-2 transition hover:bg-gray-100" @click="menuOpen = !menuOpen">
                                <span class="h-8 w-8 overflow-hidden rounded-full bg-brand-100">
                                    <img v-if="user.avatar_url" :src="user.avatar_url" alt="" class="h-full w-full object-cover" />
                                    <span v-else class="flex h-full w-full items-center justify-center text-sm font-bold text-brand-700">{{ initials }}</span>
                                </span>
                                <svg class="h-4 w-4 text-gray-400 transition" :class="menuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </button>

                            <transition
                                enter-active-class="transition duration-100" enter-from-class="opacity-0 scale-95"
                                leave-active-class="transition duration-75" leave-to-class="opacity-0 scale-95"
                            >
                                <div v-if="menuOpen" class="absolute right-0 mt-2 max-h-[calc(100vh-5rem)] w-64 overflow-y-auto overflow-x-hidden rounded-2xl border border-gray-700/60 bg-gray-900 p-1.5 shadow-2xl ring-1 ring-black/20" @click="menuOpen = false">
                                    <!-- Толгой: аватар + нэр + и-мэйл -->
                                    <div class="flex items-center gap-3 px-2.5 py-2.5">
                                        <span class="h-10 w-10 shrink-0 overflow-hidden rounded-full bg-gray-700">
                                            <img v-if="user.avatar_url" :src="user.avatar_url" alt="" class="h-full w-full object-cover" />
                                            <span v-else class="flex h-full w-full items-center justify-center text-sm font-bold text-white">{{ initials }}</span>
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-white">{{ user.name }}</p>
                                            <p class="truncate text-xs text-gray-400">{{ user.email }}</p>
                                        </div>
                                    </div>
                                    <div class="my-1 border-t border-gray-700/60"></div>

                                    <Link href="/dashboard" class="flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm text-gray-300 transition hover:bg-gray-800 hover:text-white">
                                        <svg class="h-[18px] w-[18px] text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                        Миний самбар
                                    </Link>
                                    <Link href="/my/zar" class="flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm text-gray-300 transition hover:bg-gray-800 hover:text-white">
                                        <svg class="h-[18px] w-[18px] text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.99 1.99 0 013 12V7a4 4 0 014-4z" /></svg>
                                        Миний зар
                                    </Link>
                                    <Link href="/messages" class="flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm text-gray-300 transition hover:bg-gray-800 hover:text-white">
                                        <svg class="h-[18px] w-[18px] text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.5C3.5 15.3 3 13.7 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                                        Зурвас
                                        <span v-if="unreadMessages" class="ml-auto rounded-full bg-brand-600 px-1.5 text-[10px] font-bold text-white">{{ unreadMessages }}</span>
                                    </Link>
                                    <Link href="/my/favorites" class="flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm text-gray-300 transition hover:bg-gray-800 hover:text-white">
                                        <svg class="h-[18px] w-[18px] text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                                        Хадгалсан зар
                                    </Link>
                                    <Link href="/profile" class="flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm text-gray-300 transition hover:bg-gray-800 hover:text-white">
                                        <svg class="h-[18px] w-[18px] text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                        Профайл
                                    </Link>
                                    <Link v-if="isAdmin" href="/admin" class="flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm text-gray-300 transition hover:bg-gray-800 hover:text-white">
                                        <svg class="h-[18px] w-[18px] text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        Удирдлага
                                    </Link>
                                    <div class="my-1 border-t border-gray-700/60"></div>
                                    <Link href="/logout" method="post" as="button" class="flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-left text-sm text-red-400 transition hover:bg-gray-800 hover:text-red-300">
                                        <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                        Гарах
                                    </Link>
                                </div>
                            </transition>
                        </div>
                    </template>
                    <template v-else>
                        <Link href="/login" class="hidden px-3 py-2 font-medium text-gray-600 hover:text-brand-700 sm:block">Нэвтрэх</Link>
                        <Link href="/register" class="hidden rounded-full bg-brand-600 px-4 py-2 font-medium text-white hover:bg-brand-700 sm:inline-flex">Бүртгүүлэх</Link>
                    </template>

                    <!-- Мобайл цэс товч -->
                    <button class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 md:hidden" @click="mobileOpen = !mobileOpen">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                </div>
            </div>

            <!-- Нав мөр (дэлгэцэн дээр доор нь тусдаа) -->
            <div class="hidden border-t border-gray-100 md:block">
                <nav class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-4 py-1.5">
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        class="shrink-0 whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium transition"
                        :class="isActive(item.href) ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-brand-700'"
                    >
                        {{ item.name }}
                    </Link>
                </nav>
            </div>

            <!-- Мобайл цэс -->
            <div v-if="mobileOpen" class="border-t border-gray-100 bg-white md:hidden">
                <nav class="mx-auto max-w-7xl space-y-1 px-4 py-3">
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        class="block rounded-lg px-3 py-2 text-sm font-medium"
                        :class="isActive(item.href) ? 'bg-brand-50 text-brand-700' : 'text-gray-600'"
                        @click="mobileOpen = false"
                    >
                        {{ item.name }}
                    </Link>
                    <div class="my-2 border-t border-gray-100"></div>
                    <template v-if="user">
                        <Link href="/zar/new" class="block rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white">+ Зар нэмэх</Link>
                        <Link href="/dashboard" class="block rounded-lg px-3 py-2 text-sm text-gray-700">Миний самбар</Link>
                        <Link href="/my/zar" class="block rounded-lg px-3 py-2 text-sm text-gray-700">Миний зар</Link>
                        <Link href="/messages" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-700">
                            Зурвас
                            <span v-if="unreadMessages" class="rounded-full bg-brand-600 px-1.5 text-[10px] font-bold text-white">{{ unreadMessages }}</span>
                        </Link>
                        <Link href="/my/favorites" class="block rounded-lg px-3 py-2 text-sm text-gray-700">Хадгалсан зар</Link>
                        <Link href="/profile" class="block rounded-lg px-3 py-2 text-sm text-gray-700">Профайл тохиргоо</Link>
                        <Link v-if="isAdmin" href="/admin" class="block rounded-lg px-3 py-2 text-sm text-gray-700">Удирдлага</Link>
                        <Link href="/logout" method="post" as="button" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-red-600">Гарах</Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="block rounded-lg px-3 py-2 text-sm text-gray-600">Нэвтрэх</Link>
                        <Link href="/register" class="block rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white">Бүртгүүлэх</Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Flash мессеж -->
        <div v-if="page.props.flash?.success || page.props.flash?.error" class="mx-auto mt-4 w-full max-w-7xl px-4">
            <div v-if="page.props.flash?.success" class="rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700 ring-1 ring-green-100">
                {{ page.props.flash.success }}
            </div>
            <div v-if="page.props.flash?.error" class="rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700 ring-1 ring-red-100">
                {{ page.props.flash.error }}
            </div>
        </div>

        <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8">
            <slot />
        </main>

        <!-- Хөл -->
        <footer class="border-t border-gray-100 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-6 sm:flex-row">
                <div class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-sm font-bold text-white">EU</span>
                    <span class="font-bold">Mongolia</span>
                </div>
                <p class="text-sm text-gray-400">© 2026 EU Mongolia — Европ дахь монголчуудын платформ</p>
                <div class="flex flex-wrap justify-center gap-x-4 gap-y-1 text-sm text-gray-500">
                    <Link href="/about" class="hover:text-brand-700">Бидний тухай</Link>
                    <Link href="/embassy" class="hover:text-brand-700">Элчин / Тусламж</Link>
                    <Link href="/contact" class="hover:text-brand-700">Холбоо барих</Link>
                    <Link href="/terms" class="hover:text-brand-700">Нөхцөл</Link>
                    <Link href="/privacy" class="hover:text-brand-700">Нууцлал</Link>
                </div>
            </div>
        </footer>
    </div>
</template>
