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

const props = defineProps({ embassies: Array, kinds: Array });

const dialogOpen = ref(false);
const editingId = ref(null);
const form = useForm({
    name: '', kind: 'embassy', country: '', city: '', address: '',
    phone: '', emergency_phone: '', email: '', website: '', hours: '', notes: '',
    sort_order: 0, is_active: true,
});

function openAdd() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    dialogOpen.value = true;
}
function openEdit(e) {
    editingId.value = e.id;
    form.clearErrors();
    Object.assign(form, {
        name: e.name, kind: e.kind, country: e.country, city: e.city ?? '', address: e.address ?? '',
        phone: e.phone ?? '', emergency_phone: e.emergency_phone ?? '', email: e.email ?? '',
        website: e.website ?? '', hours: e.hours ?? '', notes: e.notes ?? '',
        sort_order: e.sort_order ?? 0, is_active: !!e.is_active,
    });
    dialogOpen.value = true;
}
function submit() {
    const opts = { preserveScroll: true, onSuccess: () => { dialogOpen.value = false; } };
    if (editingId.value) form.put(`/admin/embassies/${editingId.value}`, opts);
    else form.post('/admin/embassies', opts);
}
function destroy(id) {
    if (confirm('Устгах уу?')) router.delete(`/admin/embassies/${id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Элчин сайдын яам" />

    <AdminLayout>
        <template #title>Элчин сайдын яам / Тусламж</template>

        <div class="mb-4 flex justify-end">
            <Button size="sm" @click="openAdd">+ Нэмэх</Button>
        </div>

        <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <div class="divide-y divide-gray-50">
                <div v-for="e in embassies" :key="e.id" class="flex items-center justify-between px-5 py-3">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-800">{{ e.name }} <span v-if="!e.is_active" class="text-xs text-gray-400">(идэвхгүй)</span></p>
                        <p class="text-xs text-gray-400">{{ e.country }}<span v-if="e.city"> · {{ e.city }}</span><span v-if="e.phone"> · {{ e.phone }}</span></p>
                    </div>
                    <div class="flex shrink-0 gap-1">
                        <Button variant="ghost" size="icon" title="Засах" @click="openEdit(e)">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        </Button>
                        <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive" title="Устгах" @click="destroy(e.id)">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </Button>
                    </div>
                </div>
                <p v-if="!embassies.length" class="px-5 py-8 text-center text-sm text-gray-400">Мэдээлэл алга.</p>
            </div>
        </div>

        <Dialog v-model:open="dialogOpen">
            <DialogContent class="max-w-lg">
                <DialogTitle>{{ editingId ? 'Засах' : 'Шинэ төлөөлөгчийн газар' }}</DialogTitle>
                <div class="max-h-[70vh] space-y-3 overflow-y-auto pr-1">
                    <div class="space-y-1.5">
                        <Label>Нэр</Label>
                        <Input v-model="form.name" type="text" placeholder="Монгол Улсаас ... суугаа ЭСЯ" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label>Төрөл</Label>
                            <SelectNative v-model="form.kind">
                                <option v-for="k in kinds" :key="k.key" :value="k.key">{{ k.label }}</option>
                            </SelectNative>
                        </div>
                        <div class="space-y-1.5">
                            <Label>Улс</Label>
                            <Input v-model="form.country" type="text" placeholder="Герман" />
                            <p v-if="form.errors.country" class="text-sm text-destructive">{{ form.errors.country }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <Label>Хот</Label>
                            <Input v-model="form.city" type="text" />
                        </div>
                        <div class="space-y-1.5">
                            <Label>Эрэмбэ</Label>
                            <Input v-model.number="form.sort_order" type="number" min="0" />
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Хаяг</Label>
                        <Input v-model="form.address" type="text" />
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label>Утас</Label>
                            <Input v-model="form.phone" type="text" />
                        </div>
                        <div class="space-y-1.5">
                            <Label>Яаралтай утас</Label>
                            <Input v-model="form.emergency_phone" type="text" />
                        </div>
                        <div class="space-y-1.5">
                            <Label>И-мэйл</Label>
                            <Input v-model="form.email" type="email" />
                        </div>
                        <div class="space-y-1.5">
                            <Label>Вэбсайт</Label>
                            <Input v-model="form.website" type="text" placeholder="https://" />
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <Label>Ажиллах цаг</Label>
                        <Input v-model="form.hours" type="text" placeholder="Да–Ба 09:00–17:00" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Тэмдэглэл</Label>
                        <Textarea v-model="form.notes" rows="2" />
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-primary focus:ring-ring" />
                        Идэвхтэй (нийтэд харагдана)
                    </label>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="dialogOpen = false">Болих</Button>
                    <Button :disabled="form.processing" @click="submit">Хадгалах</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
