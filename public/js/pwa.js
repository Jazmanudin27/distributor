// PWA Registration and Installation Logic
let deferredPrompt = null;

document.addEventListener('DOMContentLoaded', () => {
    const banner = document.getElementById('pwa-install-banner');
    if (!banner) return;

    const btnInstall = document.getElementById('pwa-btn-install');
    const btnDismiss = document.getElementById('pwa-btn-dismiss');
    const iosInstructions = document.getElementById('pwa-ios-instructions');
    const installActionArea = document.getElementById('pwa-install-action-area');

    // Check if app is running in standalone mode (already installed)
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

    // Check if user previously dismissed the banner
    const isDismissed = localStorage.getItem('pwa-banner-dismissed') === 'true';

    // Device detection
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

    // Register Service Worker
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
            .then((reg) => console.log('Service Worker registered successfully with scope:', reg.scope))
            .catch((err) => console.error('Service Worker registration failed:', err));
    }

    // Determine banner visibility
    if (!isStandalone && !isDismissed) {
        if (isIOS) {
            // iOS installation workflow (Safari manual Add to Home Screen)
            if (iosInstructions && installActionArea) {
                installActionArea.style.display = 'none';
                iosInstructions.style.display = 'block';
                banner.style.display = 'flex';
            }
        }
        // For Android / Desktop Chrome, banner will be shown by beforeinstallprompt handler below
    }

    // Handle standard browser install prompt (Android, Chrome, etc.)
    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent default browser prompt
        e.preventDefault();
        // Store event
        deferredPrompt = e;

        // Only show banner if not already standalone and not dismissed
        if (!isStandalone && !isDismissed) {
            if (iosInstructions && installActionArea) {
                iosInstructions.style.display = 'none';
                installActionArea.style.display = 'block';
            }
            banner.style.display = 'flex';
        }
    });

    // Handle "Install" button click
    if (btnInstall) {
        btnInstall.addEventListener('click', async () => {
            if (!deferredPrompt) return;

            // Show browser prompt
            deferredPrompt.prompt();

            // Wait for user choice
            const { outcome } = await deferredPrompt.userChoice;
            console.log(`User response to install prompt: ${outcome}`);

            // Clear prompt
            deferredPrompt = null;

            // Hide banner
            banner.style.display = 'none';
        });
    }

    // Handle "Dismiss" button click
    if (btnDismiss) {
        btnDismiss.addEventListener('click', () => {
            banner.style.display = 'none';
            // Save state to local storage
            localStorage.setItem('pwa-banner-dismissed', 'true');
        });
    }
});
