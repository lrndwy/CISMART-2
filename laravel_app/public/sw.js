const CACHE_NAME = 'cismart-pwa-cache-v1';
const OFFLINE_URL = '/offline';

const URLS_TO_CACHE = [
  '/',
  OFFLINE_URL,
  '/logo-nobg.png',
  '/manifest.json'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Opened cache');
        // Cache files, ignoring errors for individual missing files
        return Promise.all(
          URLS_TO_CACHE.map(url => {
            return cache.add(url).catch(reason => {
              console.log(`[PWA] Failed to cache ${url}: ${reason}`);
            });
          })
        );
      })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // Hanya intercept GET requests
  if (event.request.method !== 'GET') return;

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        return response;
      })
      .catch(() => {
        // Jika offline atau fetch gagal
        return caches.match(event.request)
          .then((response) => {
            if (response) {
              return response; // Return dari cache jika ada
            }
            // Jika tidak ada di cache dan merupakan request halaman HTML (bukan asset), return halaman offline
            if (event.request.headers.get('accept').includes('text/html')) {
              return caches.match(OFFLINE_URL);
            }
            return new Response('', { status: 408, statusText: 'Request timeout.' });
          });
      })
  );
});
