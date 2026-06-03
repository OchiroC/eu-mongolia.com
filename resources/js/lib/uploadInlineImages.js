// Rich text HTML доторх data: URL зургуудыг (засварлагчид түр суулгасан) хадгалах
// үед серверт upload хийж, эргэж ирсэн storage URL-аар сольж буцаана.
// Cover/gallery шиг — зураг зөвхөн хадгалах үед л storage-д орно.

function dataUrlToFile(dataUrl) {
    const [meta, b64] = dataUrl.split(',');
    const mime = meta.match(/data:(.*?);base64/)?.[1] || 'image/jpeg';
    const bin = atob(b64);
    const arr = new Uint8Array(bin.length);
    for (let i = 0; i < bin.length; i++) arr[i] = bin.charCodeAt(i);
    const ext = (mime.split('/')[1] || 'jpg').replace('jpeg', 'jpg');
    return new File([arr], `content-${Date.now()}.${ext}`, { type: mime });
}

export async function uploadInlineImages(html, uploadUrl = '/admin/media') {
    if (!html || !html.includes('data:image')) return html;

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const doc = new DOMParser().parseFromString(html, 'text/html');
    const imgs = [...doc.querySelectorAll('img')].filter((img) => img.getAttribute('src')?.startsWith('data:'));

    for (const img of imgs) {
        const body = new FormData();
        body.append('file', dataUrlToFile(img.getAttribute('src')));
        const res = await fetch(uploadUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' },
            body,
        });
        if (!res.ok) throw new Error('upload failed');
        const { url } = await res.json();
        img.setAttribute('src', url);
    }

    return doc.body.innerHTML;
}
