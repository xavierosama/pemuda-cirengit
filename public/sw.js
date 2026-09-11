const CACHE_NAME = 'pemuda-cirengit-static-v1';
const STATIC_ASSETS = [
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/apple-touch-icon.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    const isStaticAsset = url.pathname.startsWith('/icons/')
        || url.pathname.startsWith('/build/assets/');

    if (! isStaticAsset) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(request).then((networkResponse) => {
                const responseClone = networkResponse.clone();

                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(request, responseClone);
                });

                return networkResponse;
            });
        })
    );
});

self.addEventListener('push', (event) => {
    let payload = {};

    if (event.data) {
        try {
            payload = event.data.json();
        } catch (error) {
            payload = {
                title: 'Pemuda Cirengit',
                body: event.data.text(),
            };
        }
    }

    const title = payload.title || 'Pemuda Cirengit';
    const options = {
        body: payload.body || payload.message || 'Ada informasi baru untuk Anda.',
        icon: payload.icon || '/icons/icon-192.png',
        badge: payload.badge || '/icons/apple-touch-icon.png',
        data: {
            url: normalizeNotificationUrl(payload.url || '/member'),
            type: payload.type || 'info',
        },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = normalizeNotificationUrl(event.notification.data?.url || '/member');

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if ('focus' in client && client.url === targetUrl) {
                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

function normalizeNotificationUrl(url) {
    try {
        const parsedUrl = new URL(url, self.location.origin);

        if (parsedUrl.origin !== self.location.origin) {
            return `${self.location.origin}/member`;
        }

        return parsedUrl.href;
    } catch (error) {
        return `${self.location.origin}/member`;
    }
}
