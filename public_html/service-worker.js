/**
 * Service Worker — Trivya RH (PWA)
 * Cache-first para assets estáticos, network-first para HTML
 * @autor  Equipe Trivya RH  @versao 1.0.0  @data 2025-01-01
 */

'use strict';

const CACHE_VERSION  = 'trivya-v1';
const CACHE_ASSETS   = 'trivya-assets-v1';

// Assets estáticos para cache offline
const ASSETS_PARA_CACHE = [
  '/',
  '/assets/css/style.css',
  '/assets/css/components.css',
  '/assets/css/pages.css',
  '/assets/css/responsive.css',
  '/assets/js/main.js',
  '/assets/js/form.js',
  '/assets/js/cookies.js',
];

// ----------------------------------------------------------
// INSTALL: pré-cachear assets essenciais
// ----------------------------------------------------------
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_ASSETS).then(cache => {
      // addAll falha se qualquer asset não carregar — usar add individualmente
      return Promise.allSettled(
        ASSETS_PARA_CACHE.map(url => cache.add(url).catch(() => {}))
      );
    }).then(() => self.skipWaiting()) // Ativar imediatamente
  );
});

// ----------------------------------------------------------
// ACTIVATE: limpar caches antigos
// ----------------------------------------------------------
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key !== CACHE_ASSETS && key !== CACHE_VERSION)
          .map(key => caches.delete(key))
      )
    ).then(() => self.clients.claim()) // Controlar todas as abas abertas
  );
});

// ----------------------------------------------------------
// FETCH: estratégias por tipo de recurso
// ----------------------------------------------------------
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // Ignorar requisições não-GET e de outros domínios
  if (event.request.method !== 'GET' || url.origin !== self.location.origin) {
    return;
  }

  // Ignorar URLs de API e admin (sempre network)
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/admin/')) {
    return;
  }

  // Estratégia: CACHE FIRST para assets (CSS, JS, imagens, fontes)
  if (
    url.pathname.startsWith('/assets/') ||
    url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|woff|woff2|ttf)$/)
  ) {
    event.respondWith(cacheFirst(event.request));
    return;
  }

  // Estratégia: NETWORK FIRST para páginas HTML
  event.respondWith(networkFirst(event.request));
});

// ----------------------------------------------------------
// Estratégia Cache-First
// ----------------------------------------------------------
async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;

  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(CACHE_ASSETS);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    return new Response('Asset não disponível offline.', { status: 503 });
  }
}

// ----------------------------------------------------------
// Estratégia Network-First
// ----------------------------------------------------------
async function networkFirst(request) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(CACHE_VERSION);
      cache.put(request, response.clone());
    }
    return response;
  } catch {
    // Offline: tentar cache
    const cached = await caches.match(request);
    if (cached) return cached;

    // Fallback para home se for uma página HTML
    const homeCached = await caches.match('/');
    if (homeCached) return homeCached;

    return new Response(
      '<h1>Sem conexão</h1><p>Verifique sua internet e tente novamente.</p>',
      { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
    );
  }
}
