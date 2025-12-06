// Service Worker for Ret til Familie PWA
// Real-time sync + offline support

const CACHE_NAME = 'rtf-platform-v1.0.0';
const API_CACHE = 'rtf-api-cache-v1';
const RUNTIME_CACHE = 'rtf-runtime-v1';

// Core files to cache immediately
const CORE_ASSETS = [
  '/',
  '/platform-home/',
  '/platform-kate-ai/',
  '/platform-chatrooms/',
  '/platform-profil/',
  '/platform-sagshjaelp/',
  '/style.css',
  '/wp-content/themes/ret-til-familie/assets/icon-192x192.png',
  '/wp-content/themes/ret-til-familie/assets/icon-512x512.png'
];

// API endpoints to cache
const API_ENDPOINTS = [
  '/wp-json/kate/v1/message',
  '/wp-json/kate/v1/chat-rooms/list',
  '/wp-json/kate/v1/messages/list'
];

// Install event - cache core assets
self.addEventListener('install', (event) => {
  console.log('[SW] Installing Service Worker...');
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[SW] Caching core assets');
      return cache.addAll(CORE_ASSETS).catch(err => {
        console.warn('[SW] Failed to cache some assets:', err);
      });
    })
  );
  self.skipWaiting();
});

// Activate event - cleanup old caches
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating Service Worker...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME && cacheName !== API_CACHE && cacheName !== RUNTIME_CACHE) {
            console.log('[SW] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Fetch event - network first, fallback to cache (for real-time data)
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip cross-origin requests
  if (url.origin !== location.origin) {
    return;
  }

  // API requests - Network First (for real-time data)
  if (url.pathname.includes('/wp-json/')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          // Clone and cache successful responses
          if (response && response.status === 200) {
            const responseClone = response.clone();
            caches.open(API_CACHE).then((cache) => {
              cache.put(request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          // Fallback to cache if network fails
          return caches.match(request).then((cachedResponse) => {
            return cachedResponse || new Response(
              JSON.stringify({ 
                success: false, 
                error: 'Offline - data ikke tilgængelig',
                offline: true 
              }),
              { 
                headers: { 'Content-Type': 'application/json' },
                status: 503
              }
            );
          });
        })
    );
    return;
  }

  // Platform pages - Network First with cache fallback
  if (url.pathname.includes('platform-')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          if (response && response.status === 200) {
            const responseClone = response.clone();
            caches.open(RUNTIME_CACHE).then((cache) => {
              cache.put(request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          return caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }
            // Return offline page
            return caches.match('/platform-home/').then(fallback => {
              return fallback || new Response('Offline', { status: 503 });
            });
          });
        })
    );
    return;
  }

  // Static assets - Cache First (images, CSS, JS)
  if (request.destination === 'image' || request.destination === 'style' || request.destination === 'script') {
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        if (cachedResponse) {
          return cachedResponse;
        }
        return fetch(request).then((response) => {
          if (response && response.status === 200) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(request, responseClone);
            });
          }
          return response;
        });
      })
    );
    return;
  }

  // Default - Network First
  event.respondWith(
    fetch(request).catch(() => {
      return caches.match(request);
    })
  );
});

// Background sync for offline messages
self.addEventListener('sync', (event) => {
  console.log('[SW] Background sync triggered:', event.tag);
  
  if (event.tag === 'sync-messages') {
    event.waitUntil(syncMessages());
  }
  
  if (event.tag === 'sync-chat') {
    event.waitUntil(syncChatMessages());
  }
});

// Push notifications
self.addEventListener('push', (event) => {
  console.log('[SW] Push notification received');
  
  const data = event.data ? event.data.json() : {};
  const title = data.title || 'Ret til Familie';
  const options = {
    body: data.body || 'Du har en ny besked',
    icon: '/wp-content/themes/ret-til-familie/assets/icon-192x192.png',
    badge: '/wp-content/themes/ret-til-familie/assets/icon-72x72.png',
    vibrate: [200, 100, 200],
    tag: data.tag || 'rtf-notification',
    requireInteraction: false,
    data: {
      url: data.url || '/platform-home/'
    }
  };

  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
  console.log('[SW] Notification clicked');
  event.notification.close();
  
  const urlToOpen = event.notification.data.url || '/platform-home/';
  
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then((windowClients) => {
        // Check if already open
        for (let client of windowClients) {
          if (client.url === urlToOpen && 'focus' in client) {
            return client.focus();
          }
        }
        // Open new window
        if (clients.openWindow) {
          return clients.openWindow(urlToOpen);
        }
      })
  );
});

// Sync helper functions
async function syncMessages() {
  try {
    // Get pending messages from IndexedDB
    const db = await openDB();
    const tx = db.transaction('pending-messages', 'readonly');
    const store = tx.objectStore('pending-messages');
    const messages = await store.getAll();
    
    // Send each pending message
    for (let msg of messages) {
      await fetch('/wp-json/kate/v1/message', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(msg)
      });
      
      // Remove from pending on success
      const deleteTx = db.transaction('pending-messages', 'readwrite');
      await deleteTx.objectStore('pending-messages').delete(msg.id);
    }
    
    console.log('[SW] Messages synced successfully');
  } catch (error) {
    console.error('[SW] Message sync failed:', error);
    throw error;
  }
}

async function syncChatMessages() {
  try {
    const db = await openDB();
    const tx = db.transaction('pending-chat', 'readonly');
    const store = tx.objectStore('pending-chat');
    const messages = await store.getAll();
    
    for (let msg of messages) {
      await fetch('/wp-json/kate/v1/chat-rooms/send', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(msg)
      });
      
      const deleteTx = db.transaction('pending-chat', 'readwrite');
      await deleteTx.objectStore('pending-chat').delete(msg.id);
    }
    
    console.log('[SW] Chat messages synced successfully');
  } catch (error) {
    console.error('[SW] Chat sync failed:', error);
    throw error;
  }
}

// IndexedDB helper
function openDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('rtf-offline-db', 1);
    
    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);
    
    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      
      if (!db.objectStoreNames.contains('pending-messages')) {
        db.createObjectStore('pending-messages', { keyPath: 'id', autoIncrement: true });
      }
      
      if (!db.objectStoreNames.contains('pending-chat')) {
        db.createObjectStore('pending-chat', { keyPath: 'id', autoIncrement: true });
      }
    };
  });
}

// Message to clients
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    event.waitUntil(
      caches.keys().then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => caches.delete(cacheName))
        );
      }).then(() => {
        return self.clients.matchAll();
      }).then((clients) => {
        clients.forEach(client => client.postMessage({ type: 'CACHE_CLEARED' }));
      })
    );
  }
});

console.log('[SW] Service Worker loaded successfully');
