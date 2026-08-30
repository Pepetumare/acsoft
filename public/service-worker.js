const CACHE_NAME = 'acsoft-public-v1';
const PUBLIC_ASSET_PREFIXES = ['/build/assets/', '/images/acsoft/'];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll([
            '/images/acsoft/logo.svg',
            '/images/acsoft/favicon.svg',
        ]))
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Never cache navigation, POST requests, authenticated routes, or responses
    // that could contain private business/user data. This excludes /admin,
    // /gestion/*, /login, forms, reports, PDFs, and authenticated responses.
    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (!PUBLIC_ASSET_PREFIXES.some((prefix) => url.pathname.startsWith(prefix))) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cached) => cached || fetch(request).then((response) => {
            if (!response.ok) {
                return response;
            }

            const copy = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
            return response;
        }))
    );
});
