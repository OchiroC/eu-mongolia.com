<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = computed(() => usePage().props.auth.user);

// Админ бол admin sidebar-тай, бусад нь нийтийн layout-тай.
const isAdmin = computed(() => (user.value?.roles ?? []).includes('admin'));
const Layout = computed(() => (isAdmin.value ? AdminLayout : PublicLayout));
const initials = computed(() => (user.value?.name || '?').trim().charAt(0).toUpperCase());

const allTabs = [
    { key: 'info', name: 'Профайл', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { key: 'password', name: 'Нууц үг', icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' },
    { key: 'danger', name: 'Бүртгэл', icon: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16' },
];
// Админ бүртгэлийг устгах боломжгүй тул "Бүртгэл" табыг нуух.
const tabs = computed(() => allTabs.filter((t) => t.key !== 'danger' || !isAdmin.value));
const active = ref('info');
</script>

<template>
    <Head title="Профайл" />

    <component :is="Layout">
        <template #title>Профайл</template>
        <div class="mx-auto max-w-3xl">
            <!-- Identity толгой -->
            <div class="flex flex-col items-center gap-4 text-center sm:flex-row sm:items-center sm:text-left">
                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-full bg-brand-100 ring-4 ring-white shadow-sm">
                    <img v-if="user.avatar_url" :src="user.avatar_url" alt="" class="h-full w-full object-cover" />
                    <div v-else class="flex h-full w-full items-center justify-center text-2xl font-bold text-brand-700">{{ initials }}</div>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center justify-center gap-2 sm:justify-start">
                        <h1 class="truncate text-2xl font-bold text-gray-900">{{ user.name }}</h1>
                        <span v-if="isAdmin" class="shrink-0 rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 ring-1 ring-brand-100">Админ</span>
                    </div>
                    <p class="truncate text-gray-500">{{ user.email }}</p>
                </div>
            </div>

            <!-- Хэвтээ таб -->
            <div class="mt-8 border-b border-gray-200">
                <nav class="-mb-px flex gap-1 sm:gap-6">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        class="flex items-center gap-2 border-b-2 px-2 py-3 text-sm font-medium transition sm:px-1"
                        :class="active === tab.key ? 'border-brand-600 text-brand-700' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-800'"
                        @click="active = tab.key"
                    >
                        <svg class="h-[18px] w-[18px] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon" /></svg>
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

            <!-- Контент -->
            <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-soft sm:p-8">
                <UpdateProfileInformationForm
                    v-show="active === 'info'"
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
                <UpdatePasswordForm v-show="active === 'password'" />
                <DeleteUserForm v-if="!isAdmin" v-show="active === 'danger'" />
            </div>
        </div>
    </component>
</template>
