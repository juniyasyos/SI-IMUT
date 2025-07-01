importScripts('/serviceworker-files.js');

const CACHE_NAME = "siimut-cache-v1";
self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function (cache) {
      return cache.addAll(FILES_TO_CACHE);
    })
  );
});

// Install Service Worker dan cache assets
self.addEventListener("install", async (event) => {
    event.waitUntil(
        (async () => {
            const cache = await caches.open(CACHE_NAME);
            await cache.addAll(FILES_TO_CACHE);
        })()
    );
    self.skipWaiting();
});

// Hapus cache lama saat activate
self.addEventListener("activate", async (event) => {
    event.waitUntil(
        (async () => {
            const keys = await caches.keys();
            await Promise.all(
                keys
                    .filter((key) => key.startsWith("pwa-") && key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            );
        })()
    );
    self.clients.claim();
});

// Fetch event handler dengan strategi "Cache First, Fallback to Network"
self.addEventListener("fetch", async (event) => {
    event.respondWith(
        (async () => {
            try {
                // Coba cari di cache dulu
                const cachedResponse = await caches.match(event.request);
                if (cachedResponse) return cachedResponse;

                // Jika tidak ada di cache, fetch dari network
                const networkResponse = await fetch(event.request);

                // Simpan response ke cache jika request adalah GET
                if (event.request.method === "GET") {
                    const cache = await caches.open(CACHE_NAME);
                    cache.put(event.request, networkResponse.clone());
                }

                return networkResponse;
            } catch (error) {
                // Jika gagal, tampilkan halaman offline
                return caches.match(OFFLINE_URL);
            }
        })()
    );
});
