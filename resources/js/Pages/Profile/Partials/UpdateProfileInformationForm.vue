<script setup>
import AvatarCropper from '@/Components/AvatarCropper.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/ui/Button.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import Textarea from '@/Components/ui/Textarea.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = usePage().props.auth.user;

const form = useForm({
    _method: 'patch',
    name: user.name,
    email: user.email,
    phone: user.phone ?? '',
    city: user.city ?? '',
    bio: user.bio ?? '',
    avatar: null,
    remove_avatar: false,
});

const fileInput = ref(null);
const preview = ref(null);
const cropSrc = ref(null); // cropper-т дамжуулах түүхий зураг

const currentAvatar = computed(() => {
    if (form.remove_avatar) return null;
    return preview.value ?? user.avatar_url;
});

const initials = computed(() => (user.name || '?').trim().charAt(0).toUpperCase());

function pickFile() {
    fileInput.value?.click();
}

// Зураг сонгоход cropper-ийг нээнэ.
function onFile(e) {
    const file = e.target.files[0];
    if (!file) return;
    cropSrc.value = URL.createObjectURL(file);
    e.target.value = ''; // дахин ижил файл сонгох боломжтой
}

// Cropper-аас огтолсон үр дүн ирэхэд.
function onCropped({ file, url }) {
    form.avatar = file;
    form.remove_avatar = false;
    preview.value = url;
    cropSrc.value = null;
}

function removeAvatar() {
    form.avatar = null;
    preview.value = null;
    form.remove_avatar = true;
}

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.avatar = null;
            preview.value = null;
            form.remove_avatar = false;
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-semibold text-gray-900">Профайлын мэдээлэл</h2>
            <p class="mt-1 text-sm text-gray-500">Нэр, холбоо барих мэдээлэл болон аватараа шинэчлэх.</p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <!-- Аватар -->
            <div class="flex items-center gap-5">
                <button type="button" class="group relative h-24 w-24 shrink-0 overflow-hidden rounded-full bg-brand-100 ring-2 ring-white shadow-sm" @click="pickFile">
                    <img v-if="currentAvatar" :src="currentAvatar" alt="" class="h-full w-full object-cover" />
                    <div v-else class="flex h-full w-full items-center justify-center text-3xl font-bold text-brand-700">{{ initials }}</div>
                    <span class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition group-hover:opacity-100">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </span>
                </button>
                <div>
                    <div class="flex gap-2">
                        <Button type="button" size="sm" @click="pickFile">Зураг сонгох</Button>
                        <Button v-if="currentAvatar" type="button" size="sm" variant="outline" @click="removeAvatar">Устгах</Button>
                    </div>
                    <p class="mt-2 text-xs text-gray-400">JPG, PNG, WebP — 2MB хүртэл. Сонгосны дараа тохируулна.</p>
                    <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onFile" />
                </div>
            </div>
            <InputError :message="form.errors.avatar" />

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <Label>Нэр</Label>
                    <Input v-model="form.name" type="text" required autocomplete="name" />
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>
                <div class="space-y-1.5">
                    <Label>И-мэйл</Label>
                    <Input v-model="form.email" type="email" required autocomplete="username" />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>
                <div class="space-y-1.5">
                    <Label>Утас</Label>
                    <Input v-model="form.phone" type="text" placeholder="+49 ..." />
                    <InputError class="mt-1" :message="form.errors.phone" />
                </div>
                <div class="space-y-1.5">
                    <Label>Хот</Label>
                    <Input v-model="form.city" type="text" placeholder="Berlin" />
                    <InputError class="mt-1" :message="form.errors.city" />
                </div>
            </div>

            <div class="space-y-1.5">
                <Label>Танилцуулга</Label>
                <Textarea v-model="form.bio" rows="3" placeholder="Өөрийнхөө тухай товч..." />
                <InputError class="mt-1" :message="form.errors.bio" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="rounded-lg bg-amber-50 p-3 text-sm text-amber-800">
                Таны и-мэйл хаяг баталгаажаагүй байна.
                <Link :href="route('verification.send')" method="post" as="button" class="font-medium underline">Дахин илгээх</Link>
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" :disabled="form.processing">Хадгалах</Button>
                <transition enter-active-class="transition" enter-from-class="opacity-0" leave-active-class="transition" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-emerald-600">Хадгалагдлаа ✓</p>
                </transition>
            </div>
        </form>

        <!-- Crop modal -->
        <AvatarCropper v-if="cropSrc" :src="cropSrc" @cropped="onCropped" @close="cropSrc = null" />
    </section>
</template>
