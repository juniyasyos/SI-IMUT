importScripts('/serviceworker-files.js');

const CACHE_NAME = "siimut-cache-v2";

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function (cache) {
      return cache.addAll(FILES_TO_CACHE);
    })
  );
  self.skipWaiting();
});

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

self.addEventListener("fetch", function (event) {
  if (event.request.method !== "GET") return;

  const url = new URL(event.request.url);
  const excludedPaths = ["/login", "/register", "/logout"];
  const isExcluded = excludedPaths.some(path =>
    url.pathname === path || url.pathname.startsWith(path + "/")
  );

  event.respondWith(
    caches.match(event.request, { ignoreSearch: true }).then(function (cachedResponse) {
      if (cachedResponse) {
        return cachedResponse;
      }

      return fetch(event.request)
        .then(function (networkResponse) {
          if (!isExcluded && event.request.mode !== "navigate") {
            caches.open(CACHE_NAME).then(function (cache) {
              cache.put(event.request, networkResponse.clone());
            });
          }
          return networkResponse;
        })
        .catch(function () {
          if (event.request.mode === "navigate") {
            return caches.match("/offline");
          }
        });
    })
  );
});
