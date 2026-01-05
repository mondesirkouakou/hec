/**
 * Thème HEC - JavaScript pour effets spectaculaires
 * Animations, transitions et interactions époustouflantes
 */

(function () {
    if (window.__hecThemeSplitLoaded) return;
    window.__hecThemeSplitLoaded = true;

    const current = document.currentScript && document.currentScript.src ? document.currentScript.src : '';
    const base = current ? current.replace(/theme-effects\.js(\?.*)?$/i, '') : '';

    const load = (src) => new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = src;
        s.async = false;
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
    });

    if (window.HECTheme) return;

    const core = base + 'theme-effects.core.js';
    const init = base + 'theme-effects.init.js';

    load(core)
        .then(() => load(init))
        .catch(() => {});
})();