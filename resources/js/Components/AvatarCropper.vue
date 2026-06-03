<script setup>
import Button from '@/Components/ui/Button.vue';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const emit = defineEmits(['cropped', 'close']);
const props = defineProps({
    src: { type: String, required: true },
});

const imgRef = ref(null);
let cropper = null;
let minZoomRatio = 0; // зургийг хүрээнээс жижигрүүлэхгүй доод хязгаар

// Зураг crop хүрээг бүрэн дүүргэх хамгийн бага zoom харьцааг тооцоолно.
function computeMinZoom() {
    const image = cropper.getImageData();
    const cropBox = cropper.getCropBoxData();
    minZoomRatio = Math.max(
        cropBox.width / image.naturalWidth,
        cropBox.height / image.naturalHeight,
    );
}

onMounted(() => {
    cropper = new Cropper(imgRef.value, {
        aspectRatio: 1,
        // viewMode 3 — зургийг контейнерийг үргэлж бүрэн бүрхэхээр албадаж,
        // хүрээнээс жижигрэх (дотогш орох) боломжгүй болгоно.
        viewMode: 3,
        dragMode: 'move',
        autoCropArea: 1,
        background: false,
        guides: false,
        center: false,
        movable: true,
        zoomable: true,
        // Хүрээг тогтмол байлгаж, зөвхөн зургийг чирж/томруулна.
        cropBoxResizable: false,
        cropBoxMovable: false,
        toggleDragModeOnDblclick: false,
        minContainerHeight: 320,
        responsive: true,
        ready() {
            computeMinZoom();
            // Эхэлж хүрээг бүрэн дүүргэхээр томруулна.
            if (cropper.getImageData().width < cropper.getCropBoxData().width ||
                cropper.getImageData().height < cropper.getCropBoxData().height) {
                cropper.zoomTo(minZoomRatio);
            }
        },
        zoom(event) {
            // Хүрээнээс жижиг болгох zoom-ийг хориглоно.
            if (minZoomRatio && event.detail.ratio < minZoomRatio) {
                event.preventDefault();
            }
        },
    });
});

onBeforeUnmount(() => cropper?.destroy());

function confirm() {
    const canvas = cropper.getCroppedCanvas({
        width: 400,
        height: 400,
        imageSmoothingQuality: 'high',
    });
    canvas.toBlob(
        (blob) => {
            const file = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
            emit('cropped', { file, url: canvas.toDataURL('image/jpeg') });
        },
        'image/jpeg',
        0.9,
    );
}

function zoom(delta) {
    cropper?.zoom(delta);
}
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="emit('close')">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
            <div class="border-b border-gray-100 px-5 py-4">
                <h3 class="font-semibold text-gray-900">Зургийг тохируулах</h3>
                <p class="text-sm text-gray-400">Хүрээг чирж байрлуулаад, булангаас нь чирж томруул.</p>
            </div>

            <!-- Cropper талбар -->
            <div class="avatar-cropper bg-gray-900">
                <img ref="imgRef" :src="src" alt="" class="block max-w-full" />
            </div>

            <!-- Zoom удирдлага -->
            <div class="flex items-center justify-center gap-3 border-t border-gray-100 py-3">
                <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200" @click="zoom(-0.1)">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" /></svg>
                </button>
                <span class="text-xs text-gray-400">Томруулах / жижигрүүлэх</span>
                <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-700 hover:bg-gray-200" @click="zoom(0.1)">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                </button>
            </div>

            <div class="flex justify-end gap-3 border-t border-gray-100 px-5 py-4">
                <Button type="button" variant="outline" @click="emit('close')">Цуцлах</Button>
                <Button type="button" @click="confirm">Тохируулах</Button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.avatar-cropper {
    height: 360px;
}
.avatar-cropper :deep(img) {
    max-height: 360px;
}
/* Crop хүрээг дугуй болгох */
.avatar-cropper :deep(.cropper-view-box),
.avatar-cropper :deep(.cropper-face) {
    border-radius: 50%;
}
.avatar-cropper :deep(.cropper-view-box) {
    outline: 2px solid #fff;
    outline-color: rgba(255, 255, 255, 0.9);
}
</style>
