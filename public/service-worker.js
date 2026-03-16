const CACHE_NAME = 'primus-point-v1';
const ASSETS = [
    '/sistemadeponto/',
    '/sistemadeponto/index.php',
    '/sistemadeponto/login.php',
    '/sistemadeponto/style.css',
    '/sistemadeponto/admin.css',
    '/sistemadeponto/login.css',
    '/sistemadeponto/funcionario.js',
    '/sistemadeponto/primuslogocompleta.png',
    'https://cdn.tailwindcss.com',
    'https://unpkg.com/@phosphor-icons/web',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'
];

// Install Event
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS);
        })
    );
});

// Activate Event
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        })
    );
});

// Fetch Event
self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            return cachedResponse || fetch(event.request);
        })
    );
});
