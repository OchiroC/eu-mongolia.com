<script setup>
import Button from '@/Components/ui/Button.vue';
import Dialog from '@/Components/ui/Dialog.vue';
import DialogContent from '@/Components/ui/DialogContent.vue';
import DialogFooter from '@/Components/ui/DialogFooter.vue';
import DialogTitle from '@/Components/ui/DialogTitle.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import SelectNative from '@/Components/ui/SelectNative.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({ users: Object, filters: Object, roleOptions: { type: Array, default: () => [] } });

const search = ref(props.filters?.search ?? '');
let t = null;
watch(search, (v) => {
    clearTimeout(t);
    t = setTimeout(() => router.get('/admin/users', { search: v || undefined }, { preserveState: true, replace: true }), 350);
});

const roleLabels = computed(() => Object.fromEntries(props.roleOptions.map((r) => [r.key, r.label])));
function roleLabel(u) {
    if (u.is_admin) return 'Админ';
    return roleLabels.value[u.role] ?? 'Хэрэглэгч';
}

// Нэмэх / засах цонх
const dialogOpen = ref(false);
const editingId = ref(null);
const form = useForm({ name: '', email: '', password: '', role: 'editor' });

function openAdd() {
    editingId.value = null;
    form.reset();
    form.role = props.roleOptions[0]?.key ?? 'editor';
    form.clearErrors();
    dialogOpen.value = true;
}
function openEdit(u) {
    editingId.value = u.id;
    form.clearErrors();
    form.name = u.name;
    form.email = u.email;
    form.password = '';
    form.role = u.role ?? 'user';
    dialogOpen.value = true;
}
function submit() {
    const opts = { preserveScroll: true, onSuccess: () => { dialogOpen.value = false; } };
    if (editingId.value) form.put(`/admin/users/${editingId.value}`, opts);
    else form.post('/admin/users', opts);
}
function toggleBlock(id) {
    router.post(`/admin/users/${id}/toggle-block`, {}, { preserveScroll: true });
}
function destroy(id) {
    if (confirm('Энэ хэрэглэгчийг устгах уу?')) router.delete(`/admin/users/${id}`, { preserveScroll: true });
}
function initials(name) {
    return (name || '?').trim().charAt(0).toUpperCase();
}
</script>

<template>
    <Head title="Хэрэглэгч удирдах" />

    <AdminLayout>
        <template #title>Хэрэглэгч / Ажилтан</template>

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="max-w-sm flex-1">
                <Input v-model="search" type="search" placeholder="Нэр эсвэл и-мэйлээр хайх..." />
            </div>
            <Button size="sm" @click="openAdd">+ Ажилтан нэмэх</Button>
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
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="u.is_admin ? 'bg-gray-900 text-white' : 'bg-brand-50 text-brand-700'">{{ roleLabel(u) }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ u.listings_count }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ u.created_at }}</td>
                        <td class="px-4 py-3">
                            <span v-if="u.blocked" class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Блоклогдсон</span>
                            <span v-else class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">Идэвхтэй</span>
                        </td>
                        <td class="px-4 py-3">
                            <!-- Админ бүртгэлийг хамгаална -->
                            <div v-if="u.is_admin" class="flex items-center justify-end gap-1 text-xs text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                Хамгаалагдсан
                            </div>
                            <div v-else class="flex justify-end gap-2">
                                <Button variant="secondary" size="sm" @click="openEdit(u)">Засах</Button>
                                <Button :variant="u.blocked ? 'outline' : 'secondary'" size="sm" @click="toggleBlock(u.id)">
                                    {{ u.blocked ? 'Блок цуцлах' : 'Блоклох' }}
                                </Button>
                                <Button variant="destructive" size="sm" @click="destroy(u.id)">Устгах</Button>
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
                :class="[link.active ? 'bg-brand-700 text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200', !link.url ? 'pointer-events-none opacity-50' : '']"
            />
        </div>

        <!-- Нэмэх / засах -->
        <Dialog v-model:open="dialogOpen">
            <DialogContent class="max-w-md">
                <DialogTitle>{{ editingId ? 'Ажилтан засах' : 'Шинэ ажилтан' }}</DialogTitle>
                <div class="space-y-3">
                    <div class="space-y-1.5">
                        <Label>Нэр</Label>
                        <Input v-model="form.name" type="text" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label>И-мэйл</Label>
                        <Input v-model="form.email" type="email" />
                        <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label>{{ editingId ? 'Шинэ нууц үг (солихгүй бол хоосон)' : 'Нууц үг' }}</Label>
                        <Input v-model="form.password" type="password" autocomplete="new-password" />
                        <p v-if="form.errors.password" class="text-sm text-destructive">{{ form.errors.password }}</p>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Дүр</Label>
                        <SelectNative v-model="form.role">
                            <option v-for="r in roleOptions" :key="r.key" :value="r.key">{{ r.label }}</option>
                        </SelectNative>
                        <p v-if="form.errors.role" class="text-sm text-destructive">{{ form.errors.role }}</p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="dialogOpen = false">Болих</Button>
                    <Button :disabled="form.processing" @click="submit">Хадгалах</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
