<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Dialog from '@/Components/ui/Dialog.vue';
import DialogContent from '@/Components/ui/DialogContent.vue';
import DialogFooter from '@/Components/ui/DialogFooter.vue';
import DialogTitle from '@/Components/ui/DialogTitle.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import SelectNative from '@/Components/ui/SelectNative.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ listingCategories: Array, newsCategories: Array, professionalCategories: { type: Array, default: () => [] } });

const icons = ['tag', 'car', 'home', 'briefcase', 'device', 'sofa', 'shirt', 'wrench'];

// Мэдээний ангилал: дээд түвшин + дэд категориуд
const newsTopLevel = computed(() => props.newsCategories.filter((c) => !c.parent_id));
function childrenOf(id) {
    return props.newsCategories.filter((c) => c.parent_id === id);
}

const dialogOpen = ref(false);
const type = ref('listing');
const editingId = ref(null);
const form = useForm({ name: '', icon: 'tag', sort_order: 0, parent_id: null });

// SelectNative-д null-ийг 'none' sentinel-ээр
const parentModel = computed({
    get: () => form.parent_id ?? 'none',
    set: (v) => { form.parent_id = v === 'none' ? null : v; },
});

function openAdd(t, parentId = null) {
    type.value = t;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.parent_id = parentId;
    dialogOpen.value = true;
}
function openEdit(t, cat) {
    type.value = t;
    editingId.value = cat.id;
    form.name = cat.name;
    form.icon = cat.icon || 'tag';
    form.sort_order = cat.sort_order || 0;
    form.parent_id = cat.parent_id ?? null;
    form.clearErrors();
    dialogOpen.value = true;
}
function submit() {
    const opts = { preserveScroll: true, onSuccess: () => { dialogOpen.value = false; } };
    if (editingId.value) {
        form.put(`/admin/categories/${type.value}/${editingId.value}`, opts);
    } else {
        form.post(`/admin/categories/${type.value}`, opts);
    }
}
function remove(t, id) {
    if (confirm('Энэ ангилалыг устгах уу?')) {
        router.delete(`/admin/categories/${t}/${id}`, { preserveScroll: true });
    }
}

// Мэдээний ангилал устгах — дэд ангилал/мэдээний дүрмийг харгалзана.
function removeNews(cat) {
    if (cat.slug === 'uncategorized') return;

    if (childrenOf(cat.id).length) {
        alert('Энэ ангилалд дэд ангилал байна. Устгахын тулд эхлээд доторх дэд ангилалуудыг устгана уу.');
        return;
    }

    const msg = cat.posts_count > 0
        ? `Энэ ангилалд ${cat.posts_count} мэдээ байна. Устгавал тэдгээр "Ангилалгүй" рүү шилжинэ. Үргэлжлүүлэх үү?`
        : 'Энэ ангилалыг устгах уу?';

    if (confirm(msg)) {
        router.delete(`/admin/categories/news/${cat.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Ангилал удирдах" />

    <AdminLayout>
        <template #title>Ангилал</template>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Зарын ангилал -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
                    <h2 class="font-semibold text-gray-900">Зарын ангилал</h2>
                    <Button size="sm" @click="openAdd('listing')">+ Нэмэх</Button>
                </div>
                <div class="divide-y divide-gray-50">
                    <div v-for="cat in listingCategories" :key="cat.id" class="flex items-center justify-between px-5 py-3">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-800">{{ cat.name }}</p>
                            <p class="text-xs text-gray-400">{{ cat.icon || '—' }} · {{ cat.listings_count }} зар</p>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <Button variant="ghost" size="icon" title="Засах" @click="openEdit('listing', cat)">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </Button>
                            <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive" title="Устгах" @click="remove('listing', cat.id)">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </Button>
                        </div>
                    </div>
                    <p v-if="!listingCategories.length" class="px-5 py-8 text-center text-sm text-gray-400">Ангилал алга.</p>
                </div>
            </div>

            <!-- Мэргэжилтний ангилал -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
                    <h2 class="font-semibold text-gray-900">Мэргэжилтний ангилал</h2>
                    <Button size="sm" @click="openAdd('professional')">+ Нэмэх</Button>
                </div>
                <div class="divide-y divide-gray-50">
                    <div v-for="cat in professionalCategories" :key="cat.id" class="flex items-center justify-between px-5 py-3">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-800">{{ cat.name }}</p>
                            <p class="text-xs text-gray-400">{{ cat.icon || '—' }} · {{ cat.professionals_count }} мэргэжилтэн</p>
                        </div>
                        <div class="flex shrink-0 gap-1">
                            <Button variant="ghost" size="icon" title="Засах" @click="openEdit('professional', cat)">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </Button>
                            <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive" title="Устгах" @click="remove('professional', cat.id)">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </Button>
                        </div>
                    </div>
                    <p v-if="!professionalCategories.length" class="px-5 py-8 text-center text-sm text-gray-400">Ангилал алга.</p>
                </div>
            </div>

            <!-- Мэдээний ангилал -->
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
                    <h2 class="font-semibold text-gray-900">Мэдээний ангилал</h2>
                    <Button size="sm" @click="openAdd('news')">+ Нэмэх</Button>
                </div>
                <div class="divide-y divide-gray-50">
                    <template v-for="parent in newsTopLevel" :key="parent.id">
                        <!-- Дээд ангилал -->
                        <div class="flex items-center justify-between px-5 py-3">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-800">{{ parent.name }}</p>
                                <p class="text-xs text-gray-400">{{ parent.posts_count }} мэдээ</p>
                            </div>
                            <div class="flex shrink-0 gap-1">
                                <Button variant="ghost" size="icon" title="Дэд ангилал нэмэх" @click="openAdd('news', parent.id)">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                </Button>
                                <Button variant="ghost" size="icon" title="Засах" @click="openEdit('news', parent)">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </Button>
                                <span v-if="parent.slug === 'uncategorized'" class="flex h-9 w-9 items-center justify-center text-gray-300" title="Энэ ангилал хамгаалагдсан — устгах боломжгүй">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                </span>
                                <Button v-else variant="ghost" size="icon" class="text-destructive hover:text-destructive" title="Устгах" @click="removeNews(parent)">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </Button>
                            </div>
                        </div>
                        <!-- Дэд ангилалууд -->
                        <div v-for="child in childrenOf(parent.id)" :key="child.id" class="flex items-center justify-between bg-gray-50/50 px-5 py-2.5 pl-9">
                            <div class="min-w-0">
                                <p class="text-sm text-gray-700"><span class="text-gray-300">↳</span> {{ child.name }}</p>
                                <p class="text-xs text-gray-400">{{ child.posts_count }} мэдээ</p>
                            </div>
                            <div class="flex shrink-0 gap-1">
                                <Button variant="ghost" size="icon" title="Засах" @click="openEdit('news', child)">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </Button>
                                <Button variant="ghost" size="icon" class="text-destructive hover:text-destructive" title="Устгах" @click="removeNews(child)">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </Button>
                            </div>
                        </div>
                    </template>
                    <p v-if="!newsCategories.length" class="px-5 py-8 text-center text-sm text-gray-400">Ангилал алга.</p>
                </div>
            </div>
        </div>

        <!-- Нэмэх/засах цонх -->
        <Dialog v-model:open="dialogOpen">
            <DialogContent class="max-w-md">
                <DialogTitle>{{ editingId ? 'Ангилал засах' : 'Шинэ ангилал' }}</DialogTitle>

                <div class="space-y-1.5">
                    <Label>Нэр</Label>
                    <Input v-model="form.name" type="text" placeholder="Ангиллын нэр" />
                    <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                </div>

                <div v-if="type === 'listing' || type === 'professional'" class="space-y-1.5">
                    <Label>Icon</Label>
                    <SelectNative v-model="form.icon">
                        <option v-for="ic in icons" :key="ic" :value="ic">{{ ic }}</option>
                    </SelectNative>
                </div>

                <div v-if="type === 'news'" class="space-y-1.5">
                    <Label>Эцэг ангилал</Label>
                    <SelectNative v-model="parentModel">
                        <option value="none">— Үндсэн (дээд түвшин) —</option>
                        <option v-for="p in newsTopLevel" :key="p.id" :value="p.id" :disabled="editingId === p.id">{{ p.name }}</option>
                    </SelectNative>
                </div>

                <div class="space-y-1.5">
                    <Label>Эрэмбэ</Label>
                    <Input v-model.number="form.sort_order" type="number" min="0" />
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="dialogOpen = false">Болих</Button>
                    <Button :disabled="form.processing" @click="submit">Хадгалах</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>
