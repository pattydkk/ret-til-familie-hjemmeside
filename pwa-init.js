/**
 * PWA Initialization Script for Ret til Familie Borgerplatform
 * Handles service worker registration, install prompts, and offline detection
 */

(function() {
  'use strict';

  // PWA Configuration
  const PWA_CONFIG = {
    swPath: '/sw.js',
    scope: '/',
    updateInterval: 60000, // Check for updates every 60 seconds
    offlinePageUrl: '/offline.html'
  };

  let deferredPrompt = null;
  let swRegistration = null;
  let isOnline = navigator.onLine;
  let updateAvailable = false;

  // Initialize PWA on page load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPWA);
  } else {
    initPWA();
  }

  async function initPWA() {
    console.log('[PWA] Initializing...');

    // Check if running in PWA mode
    checkPWAMode();

    // Register service worker
    if ('serviceWorker' in navigator) {
      registerServiceWorker();
    }

    // Setup install prompt
    setupInstallPrompt();

    // Setup online/offline detection
    setupNetworkDetection();

    // Setup notification permission
    setupNotifications();

    // Create install button if not installed
    createInstallUI();
  }

  // Register service worker
  async function registerServiceWorker() {
    try {
      swRegistration = await navigator.serviceWorker.register(PWA_CONFIG.swPath, {
        scope: PWA_CONFIG.scope
      });

      console.log('[PWA] Service Worker registered:', swRegistration.scope);

      // Check for updates
      swRegistration.addEventListener('updatefound', () => {
        const newWorker = swRegistration.installing;
        console.log('[PWA] Update found');

        newWorker.addEventListener('statechange', () => {
          if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
            updateAvailable = true;
            showUpdateNotification();
          }
        });
      });

      // Check for updates periodically
      setInterval(() => {
        swRegistration.update();
      }, PWA_CONFIG.updateInterval);

    } catch (err) {
      console.error('[PWA] Service Worker registration failed:', err);
    }
  }

  // Setup install prompt
  function setupInstallPrompt() {
    window.addEventListener('beforeinstallprompt', (e) => {
      console.log('[PWA] Install prompt available');
      e.preventDefault();
      deferredPrompt = e;
      showInstallButton();
    });

    window.addEventListener('appinstalled', () => {
      console.log('[PWA] App installed');
      deferredPrompt = null;
      hideInstallButton();
      showToast('✅ Borgerplatform installeret!', 'success');
    });
  }

  // Setup network detection
  function setupNetworkDetection() {
    window.addEventListener('online', () => {
      console.log('[PWA] Back online');
      isOnline = true;
      updateOnlineStatus(true);
      showToast('✅ Forbindelse genoprettet', 'success');
      
      // Trigger background sync
      if (swRegistration && swRegistration.sync) {
        swRegistration.sync.register('sync-messages');
        swRegistration.sync.register('sync-kate-ai');
      }
    });

    window.addEventListener('offline', () => {
      console.log('[PWA] Offline');
      isOnline = false;
      updateOnlineStatus(false);
      showToast('⚠️ Ingen internetforbindelse - arbejder offline', 'warning');
    });

    // Initial status
    updateOnlineStatus(isOnline);
  }

  // Update online status indicator
  function updateOnlineStatus(online) {
    const indicator = document.getElementById('pwa-online-status');
    if (indicator) {
      indicator.className = online ? 'online' : 'offline';
      indicator.textContent = online ? 'Online' : 'Offline';
    }

    // Update body class
    document.body.classList.toggle('pwa-offline', !online);
  }

  // Setup notifications
  async function setupNotifications() {
    if (!('Notification' in window)) {
      console.log('[PWA] Notifications not supported');
      return;
    }

    if (Notification.permission === 'default') {
      // Don't ask immediately, wait for user interaction
      console.log('[PWA] Notification permission not requested yet');
    }
  }

  // Request notification permission
  async function requestNotificationPermission() {
    if (!('Notification' in window)) {
      return false;
    }

    if (Notification.permission === 'granted') {
      return true;
    }

    const permission = await Notification.requestPermission();
    return permission === 'granted';
  }

  // Create install UI
  function createInstallUI() {
    // Create install button
    const installBtn = document.createElement('button');
    installBtn.id = 'pwa-install-btn';
    installBtn.className = 'pwa-install-btn';
    installBtn.innerHTML = '<i class="fas fa-download"></i> Installer App';
    installBtn.style.display = 'none';
    installBtn.addEventListener('click', installPWA);

    // Create online status indicator
    const statusIndicator = document.createElement('div');
    statusIndicator.id = 'pwa-online-status';
    statusIndicator.className = isOnline ? 'online' : 'offline';
    statusIndicator.textContent = isOnline ? 'Online' : 'Offline';

    // Append to body or header
    const header = document.querySelector('.platform-header') || document.body;
    if (header) {
      header.appendChild(installBtn);
      header.appendChild(statusIndicator);
    }
  }

  // Show install button
  function showInstallButton() {
    const btn = document.getElementById('pwa-install-btn');
    if (btn && !isPWAInstalled()) {
      btn.style.display = 'inline-flex';
    }
  }

  // Hide install button
  function hideInstallButton() {
    const btn = document.getElementById('pwa-install-btn');
    if (btn) {
      btn.style.display = 'none';
    }
  }

  // Install PWA
  async function installPWA() {
    if (!deferredPrompt) {
      console.log('[PWA] No install prompt available');
      return;
    }

    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    
    console.log('[PWA] Install prompt outcome:', outcome);
    
    if (outcome === 'accepted') {
      showToast('📲 Installerer app...', 'info');
    }
    
    deferredPrompt = null;
    hideInstallButton();
  }

  // Check if PWA is installed
  function isPWAInstalled() {
    return window.matchMedia('(display-mode: standalone)').matches ||
           window.navigator.standalone === true;
  }

  // Check PWA mode
  function checkPWAMode() {
    if (isPWAInstalled()) {
      console.log('[PWA] Running in standalone mode');
      document.body.classList.add('pwa-standalone');
      hideInstallButton();
    } else {
      console.log('[PWA] Running in browser');
      document.body.classList.add('pwa-browser');
    }
  }

  // Show update notification
  function showUpdateNotification() {
    const notification = document.createElement('div');
    notification.className = 'pwa-update-notification';
    notification.innerHTML = `
      <div class="pwa-update-content">
        <span>🎉 Ny version tilgængelig!</span>
        <button class="btn-update" onclick="window.location.reload()">Opdater nu</button>
        <button class="btn-dismiss" onclick="this.parentElement.parentElement.remove()">Senere</button>
      </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-dismiss after 10 seconds
    setTimeout(() => {
      if (notification.parentElement) {
        notification.remove();
      }
    }, 10000);
  }

  // Show toast notification
  function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `pwa-toast pwa-toast-${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 100);
    
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // Expose PWA API globally
  window.RTF_PWA = {
    isOnline: () => isOnline,
    isInstalled: isPWAInstalled,
    install: installPWA,
    requestNotifications: requestNotificationPermission,
    updateAvailable: () => updateAvailable,
    clearCache: async () => {
      if (swRegistration) {
        const sw = swRegistration.active;
        if (sw) {
          sw.postMessage({ type: 'CLEAR_CACHE' });
          showToast('🗑️ Cache ryddet', 'success');
        }
      }
    }
  };

  console.log('[PWA] Initialized successfully');
})();
