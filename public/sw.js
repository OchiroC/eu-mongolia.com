/*
 * Service worker: зөвхөн "Ирэх өдрийн багц" (/ireh) хуудсыг хадгална.
 * Франкфуртад буусан хүн Германы SIM картгүй, интернэтгүй байх нь элбэг тул
 * интернэт тасарсан үед аль ч хуудас руу орох оролдлого энэ багцыг харуулна.
 * Inertia-ийн XHR хүсэлт, Vite-ийн файлуудад хөндлөнгөөс оролцохгүй.
 */
const CACHE = 'om137-kit-v1';
const KIT = '/ireh';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE)
            .then((cache) => cache.addAll([KIT, '/favicon.svg']))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET' || request.mode !== 'navigate') return;

    const url = new URL(request.url);
    if (url.pathname === KIT) {
        // Эхлээд сүлжээнээс шинэ хувилбар авч хадгална; интернэтгүй бол хадгалсныг харуулна.
        event.respondWith(
            fetch(request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE).then((cache) => cache.put(KIT, copy));
                    return response;
                })
                .catch(() => caches.match(KIT)),
        );
        return;
    }

    event.respondWith(fetch(request).catch(() => caches.match(KIT).then((cached) => cached || Response.error())));
});
