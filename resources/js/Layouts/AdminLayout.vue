<script setup>
import NotificationBell from '@/components/NotificationBell.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const nav = [
    { name: 'Хяналтын самбар', href: '/admin', icon: 'grid' },
    { name: 'Мэдээ', href: '/admin/posts', icon: 'news' },
    { name: 'Зар / Баннер', href: '/admin/banners', icon: 'ad' },
    { name: 'Эвент', href: '/admin/events', icon: 'calendar' },
    { name: 'Модерац', href: '/admin/reports', icon: 'flag' },
    { name: 'Сэтгэгдэл', href: '/admin/comments', icon: 'comment' },
    { name: 'Мэргэжилтэн', href: '/admin/professionals', icon: 'badge' },
    { name: 'Хэрэглэгч', href: '/admin/users', icon: 'users' },
    { name: 'Ангилал', href: '/admin/categories', icon: 'folder' },
    { name: 'Тасалбар шалгах', href: '/admin/check-in', icon: 'ticket' },
    { name: 'Тохиргоо', href: '/admin/settings', icon: 'settings' },
];

const pendingReports = computed(() => page.props.pendingReports ?? 0);
const pendingComments = computed(() => page.props.pendingComments ?? 0);
const pendingProfessionals = computed(() => page.props.pendingProfessionals ?? 0);

const mobileOpen = ref(false);

function isActive(href) {
    if (href === '/admin') return page.url === '/admin';
    return page.url.startsWith(href);
}

function initials(name) {
    return (name || '?').trim().charAt(0).toUpperCase();
}
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 transform border-r border-gray-200 bg-white transition-transform md:sticky md:top-0 md:h-screen md:translate-x-0 md:self-start"
            :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-full flex-col">
                <!-- Лого -->
                <div class="flex items-center gap-2.5 border-b border-gray-100 px-5 py-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-600 font-bold text-white">EU</span>
                    <div>
                        <p class="text-sm font-bold leading-none text-gray-900">Mongolia</p>
                        <p class="mt-0.5 text-[11px] text-gray-400">Удирдлага</p>
                    </div>
                </div>

                <!-- Цэс -->
                <nav class="flex-1 space-y-1 overflow-y-auto p-3">
                    <Link
                        v-for="item in nav"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                        :class="isActive(item.href) ? 'bg-brand-600 text-white shadow-md shadow-brand-600/20' : 'text-gray-600 hover:bg-gray-100'"
                        @click="mobileOpen = false"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <template v-if="item.icon === 'grid'"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></template>
                            <template v-else-if="item.icon === 'news'"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h9l5 5v9a2 2 0 01-2 2zM7 9h6M7 13h10M7 17h10" /></template>
                            <template v-else-if="item.icon === 'ad'"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></template>
                            <template v-else-if="item.icon === 'calendar'"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M3 11h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z" /></template>
                            <template v-else-if="item.icon === 'flag'"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 2H21l-3 6 3 6h-8.5l-1-2H5a2 2 0 00-2 2z" /></template>
                            <template v-else-if="item.icon === 'users'"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4z" /></template>
                            <template v-else-if="item.icon === 'folder'"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" /></template>
                            <template v-else-if="item.icon === 'comment'"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.5C3.5 15.3 3 13.7 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></template>
                            <template v-else-if="item.icon === 'badge'"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></template>
                            <template v-else-if="item.icon === 'settings'"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></template>
                            <template v-else><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></template>
                        </svg>
                        <span class="flex-1">{{ item.name }}</span>
                        <span
                            v-if="item.icon === 'flag' && pendingReports > 0"
                            class="flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-xs font-bold"
                            :class="isActive(item.href) ? 'bg-white/25 text-white' : 'bg-red-500 text-white'"
                        >{{ pendingReports }}</span>
                        <span
                            v-if="item.icon === 'comment' && pendingComments > 0"
                            class="flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-xs font-bold"
                            :class="isActive(item.href) ? 'bg-white/25 text-white' : 'bg-red-500 text-white'"
                        >{{ pendingComments }}</span>
                        <span
                            v-if="item.icon === 'badge' && pendingProfessionals > 0"
                            class="flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-xs font-bold"
                            :class="isActive(item.href) ? 'bg-white/25 text-white' : 'bg-red-500 text-white'"
                        >{{ pendingProfessionals }}</span>
                    </Link>
                </nav>

                <!-- Доод хэсэг — аккаунт -->
                <div class="border-t border-gray-100 p-3">
                    <!-- Толгой: аватар + нэр + и-мэйл -->
                    <div class="flex items-center gap-3 px-2 py-2">
                        <span class="h-10 w-10 shrink-0 overflow-hidden rounded-full bg-brand-100">
                            <img v-if="user?.avatar_url" :src="user.avatar_url" alt="" class="h-full w-full object-cover" />
                            <span v-else class="flex h-full w-full items-center justify-center text-sm font-bold text-brand-700">{{ initials(user?.name) }}</span>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-gray-900">{{ user?.name }}</p>
                            <p class="truncate text-xs text-gray-400">{{ user?.email }}</p>
                        </div>
                    </div>
                    <div class="my-1 border-t border-gray-100"></div>

                    <div class="flex items-center justify-between">
                        <Link href="/profile" class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-600 transition hover:bg-gray-100 hover:text-gray-900">
                            <svg class="h-[18px] w-[18px] text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            Профайл
                        </Link>
                        <Link href="/logout" method="post" as="button" class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm text-red-600 transition hover:bg-red-50">
                            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                            Гарах
                        </Link>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Мобайл overlay -->
        <div v-if="mobileOpen" class="fixed inset-0 z-30 bg-black/30 md:hidden" @click="mobileOpen = false"></div>

        <!-- Гол хэсэг -->
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex items-center justify-between border-b border-gray-200 bg-white/80 px-5 py-3.5 backdrop-blur">
                <div class="flex items-center gap-3">
                    <button class="rounded-lg p-1.5 text-gray-600 hover:bg-gray-100 md:hidden" @click="mobileOpen = true">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <h1 class="text-lg font-semibold text-gray-900"><slot name="title">Удирдлага</slot></h1>
                </div>
                <NotificationBell />
            </header>

            <div v-if="page.props.flash?.success" class="mx-5 mt-4">
                <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 ring-1 ring-emerald-100">
                    {{ page.props.flash.success }}
                </div>
            </div>

            <main class="flex-1 p-5 lg:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
