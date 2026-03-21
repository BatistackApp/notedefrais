const CACHE_NAME = 'frais-v1';
const ASSETS_TO_CACHE = [
    '/app',
    '/admin',
    '/css/filament/filament/app.css',
    '/js/filament/filament/app.js',
    '/manifest.json',
    '/favicon.svg',
    '/apple-touch-icon.png'
];

// Installation : Mise en cache des ressources statiques de Filament
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
});

// Activation : Nettoyage des anciens caches
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

// Stratégie de Fetch : "Stale-While-Revalidate" pour les assets, "Network First" pour le reste
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Pour les assets statiques (CSS, JS, Polices), on sert le cache et on met à jour en arrière-plan
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

    // Par défaut : Priorité réseau pour éviter les problèmes avec Livewire et Filament
    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});
