/**
 * LUMIFIC — Shared Theme Logic
 * Handles global light/dark/sepia theme toggling and cross-tab synchronization.
 */

(function initializeThemeSystem() {
    const LIGHT_CLASS = 'light-mode';
    const THEME_KEY = 'theme';
    const root = document.documentElement;

    // 1. Initial State: Apply theme immediately
    const savedTheme = localStorage.getItem(THEME_KEY);
    if (savedTheme === LIGHT_CLASS) {
        root.classList.add(LIGHT_CLASS);
    } else {
        root.classList.remove(LIGHT_CLASS);
    }

    // 2. Toggle Function
    function toggleTheme() {
        const isNowLight = root.classList.toggle(LIGHT_CLASS);
        localStorage.setItem(THEME_KEY, isNowLight ? LIGHT_CLASS : 'dark');
        
        console.log('Lumific Theme Toggled:', isNowLight ? 'Light' : 'Dark');

        // Visual Feedback Animations
        if (window.gsap) {
            gsap.fromTo('#themeToggle .toggle-thumb', 
                { scale: 0.7 }, 
                { scale: 1, duration: 0.4, ease: 'back.out(2)' }
            );
            gsap.fromTo('body', 
                { opacity: 0.9 }, 
                { opacity: 1, duration: 0.35, ease: 'power2.out' }
            );
        }
    }

    // 3. Event Listener: Manual Toggle
    // Use a small delay to ensure DOM is ready even if script is moved
    function attachToggleEvent() {
        const btn = document.getElementById('themeToggle');
        if (btn) {
            btn.removeEventListener('click', toggleThemeHandler); // Avoid double binding
            btn.addEventListener('click', toggleThemeHandler);
        }
    }

    function toggleThemeHandler(e) {
        if (e) e.preventDefault();
        toggleTheme();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachToggleEvent);
    } else {
        attachToggleEvent();
    }

    // 4. Synchronization
    window.addEventListener('storage', (event) => {
        if (event.key === THEME_KEY) {
            const isLightRequested = event.newValue === LIGHT_CLASS;
            if (isLightRequested) {
                root.classList.add(LIGHT_CLASS);
            } else {
                root.classList.remove(LIGHT_CLASS);
            }
            console.log('Lumific Theme Synced from other tab:', isLightRequested ? 'Light' : 'Dark');
        }
    });

})();
