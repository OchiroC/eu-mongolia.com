<script setup>
import Button from '@/components/ui/Button.vue';
import Separator from '@/components/ui/Separator.vue';
import { cn } from '@/lib/utils';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import Youtube from '@tiptap/extension-youtube';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    placeholder: { type: String, default: 'Энд бичнэ үү…' },
    class: { type: null, default: '' },
    uploadUrl: { type: String, default: '/admin/media' },
});

const model = defineModel({ type: String, default: '' });

const fileInput = ref(null);
const uploading = ref(false);

const editor = useEditor({
    content: model.value || '',
    extensions: [
        // StarterKit v3 нь Link-ийг дотроо агуулдаг тул тусад нь нэмэхгүй (давхцлаас сэргийлж тохируулна).
        StarterKit.configure({
            link: { openOnClick: false, autolink: true, HTMLAttributes: { rel: 'noopener nofollow', target: '_blank' } },
        }),
        Image.configure({ inline: false, HTMLAttributes: { class: 'rounded-lg' } }),
        Youtube.configure({ controls: true, nocookie: true, width: 640, height: 360, HTMLAttributes: { class: 'rounded-lg overflow-hidden' } }),
        Placeholder.configure({ placeholder: props.placeholder }),
    ],
    onUpdate: ({ editor }) => {
        const html = editor.getHTML();
        model.value = html === '<p></p>' ? '' : html;
    },
});

// Гаднаас утга солигдвол засварлагчид тусгана (reset гэх мэт).
watch(model, (val) => {
    if (editor.value && (val || '') !== editor.value.getHTML()) {
        editor.value.commands.setContent(val || '', false);
    }
});

onBeforeUnmount(() => editor.value?.destroy());

function setLink() {
    const prev = editor.value.getAttributes('link').href;
    const url = window.prompt('Холбоосын URL:', prev || 'https://');
    if (url === null) return;
    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
        return;
    }
    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
}

function addYoutube() {
    const url = window.prompt('YouTube видеоны холбоос:');
    if (!url) return;
    editor.value.commands.setYoutubeVideo({ src: url });
}

function pickImage() {
    fileInput.value?.click();
}

// Зургийг шууд upload хийхгүй — data URL болгож засварлагчид түр суулгана.
// Жинхэнэ upload нь мэдээ/эвентийг ХАДГАЛАХ үед (uploadInlineImages) хийгдэнэ.
function onImageFile(e) {
    const file = e.target.files?.[0];
    e.target.value = '';
    if (!file) return;

    if (file.size > 6 * 1024 * 1024) {
        window.alert('Зураг хэт том байна (6MB-ээс бага байх ёстой).');
        return;
    }

    uploading.value = true;
    const reader = new FileReader();
    reader.onload = () => {
        editor.value.chain().focus().setImage({ src: reader.result }).run();
        uploading.value = false;
    };
    reader.onerror = () => {
        uploading.value = false;
        window.alert('Зураг уншихад алдаа гарлаа.');
    };
    reader.readAsDataURL(file);
}
</script>

<template>
    <div :class="cn('overflow-hidden rounded-md border border-input bg-background focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2 ring-offset-background', props.class)">
        <!-- Toolbar -->
        <div v-if="editor" class="flex flex-wrap items-center gap-0.5 border-b border-gray-100 bg-gray-50/70 p-1.5">
            <Button type="button" size="icon" :variant="editor.isActive('bold') ? 'secondary' : 'ghost'" class="h-8 w-8 font-bold" title="Тод (Bold)" @click="editor.chain().focus().toggleBold().run()">B</Button>
            <Button type="button" size="icon" :variant="editor.isActive('italic') ? 'secondary' : 'ghost'" class="h-8 w-8 italic" title="Налуу (Italic)" @click="editor.chain().focus().toggleItalic().run()">I</Button>
            <Button type="button" size="icon" :variant="editor.isActive('strike') ? 'secondary' : 'ghost'" class="h-8 w-8 line-through" title="Дарсан (Strike)" @click="editor.chain().focus().toggleStrike().run()">S</Button>

            <Separator orientation="vertical" class="mx-1 h-6" />

            <Button type="button" size="sm" :variant="editor.isActive('heading', { level: 2 }) ? 'secondary' : 'ghost'" class="h-8 px-2" title="Гарчиг 2" @click="editor.chain().focus().toggleHeading({ level: 2 }).run()">H2</Button>
            <Button type="button" size="sm" :variant="editor.isActive('heading', { level: 3 }) ? 'secondary' : 'ghost'" class="h-8 px-2" title="Гарчиг 3" @click="editor.chain().focus().toggleHeading({ level: 3 }).run()">H3</Button>

            <Separator orientation="vertical" class="mx-1 h-6" />

            <Button type="button" size="icon" :variant="editor.isActive('bulletList') ? 'secondary' : 'ghost'" class="h-8 w-8" title="Цэгтэй жагсаалт" @click="editor.chain().focus().toggleBulletList().run()">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h.01M4 12h.01M4 18h.01M8 6h12M8 12h12M8 18h12" /></svg>
            </Button>
            <Button type="button" size="icon" :variant="editor.isActive('orderedList') ? 'secondary' : 'ghost'" class="h-8 w-8" title="Дугаартай жагсаалт" @click="editor.chain().focus().toggleOrderedList().run()">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 6h13M7 12h13M7 18h13M3 6h.01M3 12h.01M3 18h.01" /></svg>
            </Button>
            <Button type="button" size="icon" :variant="editor.isActive('blockquote') ? 'secondary' : 'ghost'" class="h-8 w-8" title="Эшлэл" @click="editor.chain().focus().toggleBlockquote().run()">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h6v6H5a2 2 0 01-2-2V5zm0 0v8a4 4 0 004 4M15 5h6v6h-4a2 2 0 01-2-2V5zm0 0v8a4 4 0 004 4" /></svg>
            </Button>
            <Button type="button" size="icon" :variant="editor.isActive('link') ? 'secondary' : 'ghost'" class="h-8 w-8" title="Холбоос" @click="setLink">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101M10.172 13.828a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
            </Button>

            <Separator orientation="vertical" class="mx-1 h-6" />

            <Button type="button" size="icon" variant="ghost" class="h-8 w-8" :disabled="uploading" title="Зураг оруулах" @click="pickImage">
                <svg v-if="!uploading" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" /></svg>
            </Button>
            <Button type="button" size="icon" variant="ghost" class="h-8 w-8" title="YouTube видео" @click="addYoutube">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.6 7.2a2.5 2.5 0 00-1.76-1.77C18.25 5 12 5 12 5s-6.25 0-7.84.43A2.5 2.5 0 002.4 7.2 26 26 0 002 12a26 26 0 00.4 4.8 2.5 2.5 0 001.76 1.77C5.75 19 12 19 12 19s6.25 0 7.84-.43a2.5 2.5 0 001.76-1.77A26 26 0 0022 12a26 26 0 00-.4-4.8zM10 15V9l5 3-5 3z" /></svg>
            </Button>

            <Separator orientation="vertical" class="mx-1 h-6" />

            <Button type="button" size="icon" variant="ghost" class="h-8 w-8" :disabled="!editor.can().undo()" title="Буцаах" @click="editor.chain().focus().undo().run()">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v1M3 10l4-4m-4 4l4 4" /></svg>
            </Button>
            <Button type="button" size="icon" variant="ghost" class="h-8 w-8" :disabled="!editor.can().redo()" title="Дахин хийх" @click="editor.chain().focus().redo().run()">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 10H11a5 5 0 00-5 5v1m15-6l-4-4m4 4l-4 4" /></svg>
            </Button>
        </div>

        <!-- Контент -->
        <EditorContent :editor="editor" class="px-3 py-2" />
        <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden" @change="onImageFile" />
    </div>
</template>
