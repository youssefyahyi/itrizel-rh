const CACHE = 'itrizel-rh-v2';

const PRECACHE = [
    '/',
    '/dashboard',
    '/offline',
];

// Installation : mise en cache des assets statiques
self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE).then(cache => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

// Activation : purge des anciens caches
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

// Stratégie : Network first, cache fallback
self.addEventListener('fetch', e => {
    // Ne pas intercepter les requêtes non-GET ni les API/admin
    if (e.request.method !== 'GET') return;
    const url = new URL(e.request.url);
    if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api')) return;

    e.respondWith(
        fetch(e.request)
            .then(response => {
                // Mettre en cache les réponses HTML et assets réussis
                if (response.ok && (
                    e.request.destination === 'document' ||
                    e.request.destination === 'style' ||
                    e.request.destination === 'script' ||
                    e.request.destination === 'image'
                )) {
                    const clone = response.clone();
                    caches.open(CACHE).then(cache => cache.put(e.request, clone));
                }
                return response;
            })
            .catch(() => {
                // Offline : retourner depuis le cache
                return caches.match(e.request).then(cached => {
                    if (cached) return cached;
                    // Page offline si rien en cache
                    if (e.request.destination === 'document') {
                        return caches.match('/offline') || new Response(
                            '<html><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;background:#0F1923;color:#fff"><div style="text-align:center"><h2>Hors ligne</h2><p>Vérifiez votre connexion internet.</p></div></body></html>',
                            { headers: { 'Content-Type': 'text/html' } }
                        );
                    }
                });
            })
    );
});
