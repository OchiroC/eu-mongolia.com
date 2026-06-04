<script setup>
import Button from '@/Components/ui/Button.vue';
import Dialog from '@/Components/ui/Dialog.vue';
import DialogContent from '@/Components/ui/DialogContent.vue';
import DialogFooter from '@/Components/ui/DialogFooter.vue';
import DialogTitle from '@/Components/ui/DialogTitle.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import SelectNative from '@/Components/ui/SelectNative.vue';
import Textarea from '@/Components/ui/Textarea.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ resources: Array, categories: Array });

const dialogOpen = ref(false);
const editingId = ref(null);
const form = useForm({
    title: '', category: 'language', description: '', url: '', city: '', country: '',
    contact: '', age_range: '', is_featured: false, sort_order: 0, is_active: true,
});

function openAdd() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
}
function openEdit(r) {
    editingId.value = r.id;
    form.clearErrors();
    Object.assign(form, {
        title: r.title, category: r.category, description: r.description ?? '', url: r.url ?? '',
        city: r.city ?? '', country: r.country ?? '', contact: r.contact ?? '', age_range: r.age_range ?? '',
        is_featured: !!r.is_featured, sort_order: r.sort_order ?? 0, is_active: !!r.is_active,
    });
    dialogOpen.value = true;
}
function submit() {
    const opts = { preserveScroll: true, onSuccess: () => { dialogOpen.value = false; } };
    if (editingId.value) form.put(`/admin/kids/${editingId.value}`, opts);
    else form.post('/admin/kids', opts);
}
function destroy(id) {
    if (confirm('Устгах уу?')) router.delete(`/admin/kids/${id}`, { preserveScroll: true });
}
const catLabel = (key, categories) => categories.find((c) => c.key === key)?.label ?? key;
</script>

<template>
    <Head title="Хүүхдийн булан" />

    <AdminLayout>
        <template #title>Хүүхдийн булан</template>

        <div class="mb-4 flex justify-end">
            <Button size="sm" @click="openAdd">+ Нэмэх</Button>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="divide-y divide-gray-50">
                <div v-for="r in resources" :key="r.id" class="flex items-center justify-between px-5 py-3">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-800">{{ r.title }} <span v-if="!r.is_active" class="text-xs text-gray-400">(идэвхгүй)</span></p>
                        <p class="text-xs text-gray-400">{{ catLabel(r.category, categories) }}<span v-if="r.city"> · {{ r.city }}</span><span v-if="r.is_featured"> · ★</span></p>
                    </div>
                    <div class="flex shrink-0 gap-1">
                        <Button variant="ghost" size="icon" title="Засах" @click="openEdit(r)">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </Button>
                        <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive" title="Устгах" @click="destroy(r.id)">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </Button>
                    </div>
                </div>
                <p v-if="!resources.length" class="px-5 py-8 text-center text-sm text-gray-400">Нөөц алга.</p>
            </div>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent class="max-w-lg">
                <DialogTitle>{{ editingId ? 'Засах' : 'Шинэ нөөц' }}</DialogTitle>
                <div class="max-h-[70vh] space-y-3 overflow-y-auto pr-1">
                    <div class="space-y-1.5">
                        <Label>Гарчиг</Label>
                        <Input v-model="form.title" type="text" />
                        <p v-if="form.errors.title" class="text-sm text-destructive">{{ form.errors.title }}</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label>Ангилал</Label>
                            <SelectNative v-model="form.category">
                                <option v-for="c in categories" :key="c.key" :value="c.key">{{ c.label }}</option>
                            </SelectNative>
                        </div>
                        <div class="space-y-1.5">
                            <Label>Нас (жнь: 5-10)</Label>
                            <Input v-model="form.age_range" type="text" />
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Тайлбар</Label>
                        <Textarea v-model="form.description" rows="2" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Холбоос (видео/апп/ном — заавал биш)</Label>
                        <Input v-model="form.url" type="text" placeholder="https://" />
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="space-y-1.5"><Label>Хот (сургууль бол)</Label><Input v-model="form.city" type="text" /></div>
                        <div class="space-y-1.5"><Label>Улс</Label><Input v-model="form.country" type="text" /></div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="space-y-1.5"><Label>Холбоо барих</Label><Input v-model="form.contact" type="text" /></div>
                        <div class="space-y-1.5"><Label>Эрэмбэ</Label><Input v-model.number="form.sort_order" type="number" min="0" /></div>
                    </div>
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center gap-2 text-sm text-gray-700"><input v-model="form.is_featured" type="checkbox" class="rounded border-gray-300 text-primary focus:ring-ring" /> Онцлох</label>
                        <label class="flex items-center gap-2 text-sm text-gray-700"><input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-primary focus:ring-ring" /> Идэвхтэй</label>
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
