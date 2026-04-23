/**
 * LUMIFIC — Shared Theme Logic
 * Handles global light/dark/sepia theme toggling and cross-tab synchronization.
 */

(function initializeThemeSystem() {
    const LIGHT_CLASS = 'light-mode';
    const THEME_KEY = 'theme';
    const root = document.documentElement;

    // 1. Core Apply Function
    function applyTheme(isLight) {
        if (isLight) {
            root.classList.add(LIGHT_CLASS);
        } else {
            root.classList.remove(LIGHT_CLASS);
        }
        
        // Provide visual feedback
        if (window.gsap) {
            gsap.fromTo('body', 
                { opacity: 0.98 }, 
                { opacity: 1, duration: 0.3, ease: 'power2.out' }
            );
        }
    }

    // 2. Initial Sync
    const savedTheme = localStorage.getItem(THEME_KEY);
    applyTheme(savedTheme === LIGHT_CLASS);

    // 3. Toggle Logic
    function toggleTheme() {
        const isNowLight = !root.classList.contains(LIGHT_CLASS);
        applyTheme(isNowLight);
        localStorage.setItem(THEME_KEY, isNowLight ? LIGHT_CLASS : 'dark');
        
        // Dispatch a local storage event so current page's other components can react (if needed)
        // Note: standard 'storage' event ONLY fires on other tabs
        console.log('Lumific Theme:', isNowLight ? 'Light Mode' : 'Dark Mode');
    }

    // 4. Click Handler
    function toggleThemeHandler(e) {
        if (e) e.preventDefault();
        toggleTheme();
    }

    function attachToggleEvent() {
        const btn = document.getElementById('themeToggle');
        if (btn) {
            btn.removeEventListener('click', toggleThemeHandler);
            btn.addEventListener('click', toggleThemeHandler);
        }
    }

    // Attach on load and on visibility change
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachToggleEvent);
    } else {
        attachToggleEvent();
    }

    // 5. Cross-Tab Sync (Standard and Aggressive)
    window.addEventListener('storage', (event) => {
        if (event.key === THEME_KEY) {
            applyTheme(event.newValue === LIGHT_CLASS);
        }
    });

    // Handle back/forward cache (bfcache)
    window.addEventListener('pageshow', (event) => {
        const currentTheme = localStorage.getItem(THEME_KEY);
        applyTheme(currentTheme === LIGHT_CLASS);
        attachToggleEvent(); // Realign event listener
    });

})();
