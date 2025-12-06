// PWA Installation and Update Manager
// Real-time notifications and app-like behavior

class PWAManager {
  constructor() {
    this.deferredPrompt = null;
    this.isInstalled = false;
    this.isStandalone = false;
    this.updateAvailable = false;
    this.registration = null;
    
    this.init();
  }
  
  init() {
    // Check if running as PWA
    this.checkStandaloneMode();
    
    // Register service worker
    this.registerServiceWorker();
    
    // Setup install prompt
    this.setupInstallPrompt();
    
    // Setup update checker
    this.setupUpdateChecker();
    
    // Setup online/offline detection
    this.setupNetworkDetection();
    
    // Setup beforeinstallprompt
    this.setupBeforeInstallPrompt();
  }
  
  checkStandaloneMode() {
    this.isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                        window.navigator.standalone === true;
    
    if (this.isStandalone) {
      document.body.classList.add('pwa-standalone');
      console.log('[PWA] Running in standalone mode');
      
      // Add native app feeling
      this.addAppBehaviors();
    }
  }
  
  async registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
      console.warn('[PWA] Service Worker not supported');
      return;
    }
    
    try {
      this.registration = await navigator.serviceWorker.register('/sw.js', {
        scope: '/',
        updateViaCache: 'none'
      });
      
      console.log('[PWA] Service Worker registered:', this.registration.scope);
      
      // Check for updates every 5 minutes
      setInterval(() => {
        this.registration.update();
      }, 5 * 60 * 1000);
      
      // Listen for updates
      this.registration.addEventListener('updatefound', () => {
        const newWorker = this.registration.installing;
        
        newWorker.addEventListener('statechange', () => {
          if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
            this.updateAvailable = true;
            this.showUpdateNotification();
          }
        });
      });
      
      // Handle controller change
      navigator.serviceWorker.addEventListener('controllerchange', () => {
        console.log('[PWA] New service worker activated');
        if (this.updateAvailable) {
          window.location.reload();
        }
      });
      
      // Enable background sync
      if ('sync' in this.registration) {
        console.log('[PWA] Background sync enabled');
      }
      
      // Enable push notifications
      if ('pushManager' in this.registration) {
        console.log('[PWA] Push notifications available');
      }
      
    } catch (error) {
      console.error('[PWA] Service Worker registration failed:', error);
    }
  }
  
  setupInstallPrompt() {
    // Create install button
    const installBtn = document.createElement('button');
    installBtn.id = 'pwa-install-btn';
    installBtn.className = 'pwa-install-button';
    installBtn.innerHTML = `
      <i class="fas fa-download"></i>
      <span>Installer App</span>
    `;
    installBtn.style.cssText = `
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: white;
      border: none;
      padding: 14px 24px;
      border-radius: 50px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 4px 20px rgba(37, 99, 235, 0.4);
      display: none;
      align-items: center;
      gap: 8px;
      z-index: 10000;
      transition: all 0.3s ease;
    `;
    
    installBtn.addEventListener('mouseenter', () => {
      installBtn.style.transform = 'translateY(-2px)';
      installBtn.style.boxShadow = '0 6px 25px rgba(37, 99, 235, 0.5)';
    });
    
    installBtn.addEventListener('mouseleave', () => {
      installBtn.style.transform = 'translateY(0)';
      installBtn.style.boxShadow = '0 4px 20px rgba(37, 99, 235, 0.4)';
    });
    
    installBtn.addEventListener('click', () => this.promptInstall());
    
    document.body.appendChild(installBtn);
    this.installButton = installBtn;
  }
  
  setupBeforeInstallPrompt() {
    window.addEventListener('beforeinstallprompt', (e) => {
      console.log('[PWA] beforeinstallprompt event fired');
      e.preventDefault();
      this.deferredPrompt = e;
      
      // Show install button (only on platform pages)
      if (window.location.pathname.includes('platform-')) {
        this.installButton.style.display = 'flex';
      }
    });
    
    window.addEventListener('appinstalled', () => {
      console.log('[PWA] App installed successfully');
      this.isInstalled = true;
      this.deferredPrompt = null;
      this.installButton.style.display = 'none';
      
      this.showNotification('✅ App installeret!', 'Du kan nu bruge Ret til Familie som en app', 'success');
    });
  }
  
  async promptInstall() {
    if (!this.deferredPrompt) {
      console.log('[PWA] No deferred prompt available');
      return;
    }
    
    this.installButton.style.display = 'none';
    
    this.deferredPrompt.prompt();
    
    const { outcome } = await this.deferredPrompt.userChoice;
    console.log('[PWA] User choice:', outcome);
    
    if (outcome === 'accepted') {
      this.showNotification('🎉 Tak!', 'Appen installeres nu...', 'success');
    } else {
      this.installButton.style.display = 'flex';
    }
    
    this.deferredPrompt = null;
  }
  
  setupUpdateChecker() {
    // Check for updates on visibility change
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden && this.registration) {
        this.registration.update();
      }
    });
    
    // Check for updates on focus
    window.addEventListener('focus', () => {
      if (this.registration) {
        this.registration.update();
      }
    });
  }
  
  showUpdateNotification() {
    const notification = document.createElement('div');
    notification.className = 'pwa-update-notification';
    notification.innerHTML = `
      <div class="update-content">
        <i class="fas fa-sync-alt"></i>
        <div>
          <strong>Ny version tilgængelig</strong>
          <p>Klik for at opdatere til den nyeste version</p>
        </div>
      </div>
      <button class="update-btn">Opdater Nu</button>
    `;
    
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
      display: flex;
      align-items: center;
      gap: 15px;
      z-index: 10001;
      max-width: 90%;
      width: 400px;
      animation: slideDown 0.3s ease;
    `;
    
    const updateBtn = notification.querySelector('.update-btn');
    updateBtn.style.cssText = `
      background: #2563eb;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      white-space: nowrap;
    `;
    
    updateBtn.addEventListener('click', () => {
      if (this.registration && this.registration.waiting) {
        this.registration.waiting.postMessage({ type: 'SKIP_WAITING' });
      }
      notification.remove();
    });
    
    document.body.appendChild(notification);
    
    // Auto-remove after 30 seconds
    setTimeout(() => notification.remove(), 30000);
  }
  
  setupNetworkDetection() {
    window.addEventListener('online', () => {
      console.log('[PWA] Back online');
      this.showNotification('✅ Online', 'Forbindelse genoprettet', 'success');
      
      // Trigger background sync
      if (this.registration && 'sync' in this.registration) {
        this.registration.sync.register('sync-messages');
        this.registration.sync.register('sync-chat');
      }
    });
    
    window.addEventListener('offline', () => {
      console.log('[PWA] Gone offline');
      this.showNotification('⚠️ Offline', 'Du er offline - data gemmes og sendes når du er online igen', 'warning');
    });
  }
  
  addAppBehaviors() {
    // Prevent pull-to-refresh
    document.body.style.overscrollBehavior = 'none';
    
    // Hide address bar on scroll (mobile)
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
      const currentScroll = window.pageYOffset;
      if (currentScroll > lastScroll && currentScroll > 50) {
        window.scrollTo(0, currentScroll + 1);
      }
      lastScroll = currentScroll;
    }, { passive: true });
    
    // Add haptic feedback on buttons (if available)
    if ('vibrate' in navigator) {
      document.addEventListener('click', (e) => {
        if (e.target.matches('button, a.btn-primary, .chat-message, .room-item')) {
          navigator.vibrate(10);
        }
      });
    }
    
    // Prevent default context menu on long press
    document.addEventListener('contextmenu', (e) => {
      if (e.target.matches('button, a')) {
        e.preventDefault();
      }
    });
  }
  
  showNotification(title, message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `pwa-notification pwa-notification-${type}`;
    notification.innerHTML = `
      <div class="notification-content">
        <strong>${title}</strong>
        <p>${message}</p>
      </div>
    `;
    
    const colors = {
      success: '#10b981',
      warning: '#f59e0b',
      error: '#ef4444',
      info: '#2563eb'
    };
    
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: ${colors[type] || colors.info};
      color: white;
      padding: 16px 20px;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
      z-index: 10002;
      max-width: 90%;
      width: 350px;
      animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.style.animation = 'slideOutRight 0.3s ease';
      setTimeout(() => notification.remove(), 300);
    }, 5000);
  }
  
  // Public API
  async requestNotificationPermission() {
    if (!('Notification' in window)) {
      console.warn('[PWA] Notifications not supported');
      return false;
    }
    
    if (Notification.permission === 'granted') {
      return true;
    }
    
    if (Notification.permission !== 'denied') {
      const permission = await Notification.requestPermission();
      return permission === 'granted';
    }
    
    return false;
  }
  
  async subscribeToPush() {
    if (!this.registration || !('pushManager' in this.registration)) {
      console.warn('[PWA] Push notifications not supported');
      return null;
    }
    
    try {
      const subscription = await this.registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: this.urlBase64ToUint8Array(
          'YOUR_VAPID_PUBLIC_KEY' // Replace with actual VAPID key
        )
      });
      
      console.log('[PWA] Push subscription:', subscription);
      return subscription;
    } catch (error) {
      console.error('[PWA] Failed to subscribe to push:', error);
      return null;
    }
  }
  
  urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
      .replace(/\-/g, '+')
      .replace(/_/g, '/');
    
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
  }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
  @keyframes slideDown {
    from {
      transform: translateX(-50%) translateY(-100%);
      opacity: 0;
    }
    to {
      transform: translateX(-50%) translateY(0);
      opacity: 1;
    }
  }
  
  @keyframes slideInRight {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  
  @keyframes slideOutRight {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
  
  .pwa-standalone {
    padding-top: env(safe-area-inset-top);
    padding-bottom: env(safe-area-inset-bottom);
  }
  
  .pwa-notification-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  
  .pwa-notification-content strong {
    font-size: 16px;
  }
  
  .pwa-notification-content p {
    margin: 0;
    font-size: 14px;
    opacity: 0.9;
  }
  
  .update-content {
    display: flex;
    align-items: center;
    gap: 15px;
  }
  
  .update-content i {
    font-size: 24px;
    color: #2563eb;
  }
  
  .update-content strong {
    display: block;
    margin-bottom: 4px;
  }
  
  .update-content p {
    margin: 0;
    font-size: 14px;
    color: #64748b;
  }
`;
document.head.appendChild(style);

// Initialize PWA Manager
const pwaManager = new PWAManager();

// Expose to window for external access
window.pwaManager = pwaManager;

console.log('[PWA] Manager initialized successfully');
