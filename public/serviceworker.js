importScripts('/serviceworker-files.js');

const CACHE_NAME = "siimut-cache-v2";

// Install Service Worker dan cache assets statis
self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function (cache) {
      return cache.addAll(FILES_TO_CACHE);
    })
  );
  self.skipWaiting();
});

// Hapus cache lama saat activate
self.addEventListener("activate", function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys
          .filter(function (key) {
            return key !== CACHE_NAME;
          })
          .map(function (key) {
            return caches.delete(key);
          })
      );
    })
  );
  self.clients.claim();
});

// Fetch event handler
self.addEventListener("fetch", function (event) {
  if (event.request.method !== "GET") return;

  const url = new URL(event.request.url);
  const excludedPaths = ["/login", "/register", "/logout"];
  const isExcluded = excludedPaths.some(path =>
    url.pathname === path || url.pathname.startsWith(path + "/")
  );

  event.respondWith(
    caches.match(event.request).then(function (cachedResponse) {
      // Jika ada di cache, langsung return
      if (cachedResponse) {
        return cachedResponse;
      }

      return fetch(event.request)
        .then(function (networkResponse) {
          // Hanya cache jika bukan halaman dinamis
          if (!isExcluded && event.request.mode !== "navigate") {
            caches.open(CACHE_NAME).then(function (cache) {
              cache.put(event.request, networkResponse.clone());
            });
          }
          return networkResponse;
        })
        .catch(function () {
          // Jika offline dan ini adalah halaman navigasi (misalnya ke `/`)
          if (event.request.mode === "navigate") {
            return caches.match("/offline");
          }
        });
    })
  );
});
