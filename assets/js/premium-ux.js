/**
 * HEC Premium UX - JavaScript pour interactions premium
 * Micro-interactions, animations fluides et feedback visuel
 */

(function () {
    if (window.__premiumUxSplitLoaded) return;
    window.__premiumUxSplitLoaded = true;

    const current = document.currentScript && document.currentScript.src ? document.currentScript.src : '';
    const base = current ? current.replace(/premium-ux\.js(\?.*)?$/i, '') : '';

    const load = (src) => new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = src;
        s.async = false;
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
    });

    if (window.PremiumUX && window.premiumUX) return;

    const core = base + 'premium-ux.core.js';
    const globals = base + 'premium-ux.globals.js';
    const init = base + 'premium-ux.init.js';

    load(core)
        .then(() => load(globals))
        .then(() => load(init))
        .catch(() => {});
})();
