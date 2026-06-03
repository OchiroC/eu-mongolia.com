<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from '@/components/ui/Button.vue';
import Input from '@/components/ui/Input.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ users: Object, filters: Object });

const search = ref(props.filters?.search ?? '');
let t = null;
watch(search, (v) => {
    clearTimeout(t);
    t = setTimeout(() => router.get('/admin/users', { search: v || undefined }, { preserveState: true, replace: true }), 350);
});

function toggleRole(id) {
    router.post(`/admin/users/${id}/toggle-role`, {}, { preserveScroll: true });
}
function toggleBlock(id) {
    router.post(`/admin/users/${id}/toggle-block`, {}, { preserveScroll: true });
}
function initials(name) {
    return (name || '?').trim().charAt(0).toUpperCase();
}
</script>

<template>
    <Head title="Хэрэглэгч удирдах" />

    <AdminLayout>
        <template #title>Хэрэглэгч</template>

        <div class="mb-4 max-w-sm">
            <Input v-model="search" type="search" placeholder="Нэр эсвэл и-мэйлээр хайх..." />
        </div>

        <div class="overflow-x-auto rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Хэрэглэгч</th>
                        <th class="px-4 py-3">Дүр</th>
                        <th class="px-4 py-3">Зар</th>
                        <th class="px-4 py-3">Бүртгүүлсэн</th>
                        <th class="px-4 py-3">Төлөв</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="u in users.data" :key="u.id" :class="u.blocked ? 'bg-red-50/40' : ''">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="h-9 w-9 shrink-0 overflow-hidden rounded-full bg-brand-100">
                                    <img v-if="u.avatar_url" :src="u.avatar_url" alt="" class="h-full w-full object-cover" />
                                    <span v-else class="flex h-full w-full items-center justify-center text-sm font-bold text-brand-700">{{ initials(u.name) }}</span>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-medium text-gray-800">{{ u.name }}</p>
                                    <p class="truncate text-xs text-gray-400">{{ u.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="u.is_admin" class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-semibold text-brand-700">Админ</span>
                            <span v-else class="text-xs text-gray-400">Хэрэглэгч</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ u.listings_count }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ u.created_at }}</td>
                        <td class="px-4 py-3">
                            <span v-if="u.blocked" class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Блоклогдсон</span>
                            <span v-else class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Идэвхтэй</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <Button variant="secondary" size="sm" @click="toggleRole(u.id)">
                                    {{ u.is_admin ? 'Админ цуцлах' : 'Админ болгох' }}
                                </Button>
                                <Button :variant="u.blocked ? 'outline' : 'destructive'" size="sm" @click="toggleBlock(u.id)">
                                    {{ u.blocked ? 'Блок цуцлах' : 'Блоклох' }}
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!users.data.length">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Хэрэглэгч олдсонгүй.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="users.links.length > 3" class="mt-6 flex flex-wrap gap-1">
            <Link
                v-for="link in users.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="rounded-md px-3 py-1 text-sm"
                :class="[
                    link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200',
                    !link.url ? 'pointer-events-none opacity-50' : '',
                ]"
            />
        </div>
    </AdminLayout>
</template>
