const CACHE_NAME = 'frais-v1-0-1';
const ASSETS_TO_CACHE = [
    '/',
    '/css/filament/filament/app.css',
    '/js/filament/filament/app.js',
    '/manifest.json',
    '/favicon.svg',
    '/apple-touch-icon.png'
];

/**
 * Installation du Service Worker : Mise en cache des ressources critiques
 */
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
    self.skipWaiting();
});

/**
 * Activation : Nettoyage des anciens caches
 */
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== CACHE_NAME) {
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

/**
 * Stratégie : Network First (Priorité Réseau) avec Fallback Cache
 * Idéal pour une application de gestion de paye où la donnée doit être fraîche.
 */
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // CORRECTION ULTIME : On ignore TOUT ce qui est dynamique (Livewire + Pages Filament)
    // Cela force le navigateur à toujours demander la page fraîche au serveur avec un nouveau CSRF.
    if (
        url.pathname.includes('livewire') ||
        url.pathname.startsWith('/app') ||
        url.pathname.startsWith('/admin') ||
        url.pathname === '/'
    ) {
        return; // On ne fait rien, le navigateur et le serveur Laravel communiquent en direct
    }

    // Pour les assets statiques, on sert le cache
    if (ASSETS_TO_CACHE.some(asset => url.pathname.startsWith(asset)) || url.pathname.endsWith('.woff2')) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                const fetchPromise = fetch(event.request).then((networkResponse) => {
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, networkResponse.clone());
                    });
                    return networkResponse;
                });
                return cachedResponse || fetchPromise;
            })
        );
        return;
    }

    // Par défaut : Priorité réseau
    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});
