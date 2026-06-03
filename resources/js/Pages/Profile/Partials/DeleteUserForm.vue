<script setup>
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/ui/Button.vue';
import Dialog from '@/Components/ui/Dialog.vue';
import DialogContent from '@/Components/ui/DialogContent.vue';
import DialogDescription from '@/Components/ui/DialogDescription.vue';
import DialogFooter from '@/Components/ui/DialogFooter.vue';
import DialogTitle from '@/Components/ui/DialogTitle.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => { confirmingUserDeletion.value = false; },
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

// Цонх хаагдах бүрт (ESC, гадуур дарах, Болих) формыг цэвэрлэнэ.
watch(confirmingUserDeletion, (open) => {
    if (!open) {
        form.clearErrors();
        form.reset();
    }
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-semibold text-gray-900">Бүртгэл устгах</h2>
            <p class="mt-1 text-sm text-gray-500">Бүртгэлээ устгасны дараа бүх мэдээлэл бүрмөсөн устах болно. Үргэлжлүүлэхээсээ өмнө хадгалах шаардлагатай мэдээллээ татаж авна уу.</p>
        </header>

        <div class="mt-6 rounded-xl border border-red-100 bg-red-50/60 p-5">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" /></svg>
                <div>
                    <p class="text-sm font-medium text-red-800">Энэ үйлдлийг буцаах боломжгүй.</p>
                    <p class="mt-0.5 text-sm text-red-700/80">Профайл, түүх болон бусад бүх өгөгдөл бүрмөсөн устана.</p>
                </div>
            </div>
            <Button type="button" variant="destructive" size="sm" class="mt-4" @click="confirmingUserDeletion = true">
                Бүртгэл устгах
            </Button>
        </div>

        <Dialog v-model:open="confirmingUserDeletion">
            <DialogContent class="max-w-md">
                <DialogTitle>Та бүртгэлээ устгахдаа итгэлтэй байна уу?</DialogTitle>
                <DialogDescription>
                    Бүртгэл устгасны дараа бүх мэдээлэл бүрмөсөн устах болно. Баталгаажуулахын тулд нууц үгээ оруулна уу.
                </DialogDescription>

                <div class="space-y-1.5">
                    <Label>Нууц үг</Label>
                    <Input
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        placeholder="Нууц үг"
                        autocomplete="current-password"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-1" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="confirmingUserDeletion = false">Болих</Button>
                    <Button type="button" variant="destructive" :disabled="form.processing" @click="deleteUser">Бүртгэл устгах</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </section>
</template>
