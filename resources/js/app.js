import './bootstrap';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// import estática de React (sin JSX en este archivo)
import React from 'react';

async function mountOneTypeahead(el) {
    try {
        // Importación dinámica para evitar romper el resto del bundle si este componente falla
        const [{ createRoot }, mod] = await Promise.all([
            import('react-dom/client'),
            import('./components/Typeahead.jsx'),
        ]);
        const Typeahead = mod.default;

        // Evitar doble-montaje
        if (el.__mounted) return;
        el.__mounted = true;

        const props = {
            name: el.dataset.name,
            endpoint: el.dataset.endpoint,
            field: el.dataset.field,
            initial: el.dataset.initial || '',
            placeholder: el.dataset.placeholder || 'Escriu per filtrar...',
            widthClass: el.dataset.widthClass || 'w-64',
        };

        const root = createRoot(el);
        // Importante: sin JSX en app.js
        root.render(React.createElement(Typeahead, props));
    } catch (err) {
        console.error('[Typeahead] Error montando componente en', el, err);
    }
}

function mountTypeaheads() {
    document.querySelectorAll('.js-react-typeahead').forEach((el) => {
        mountOneTypeahead(el);
    });
}

// Monta si el DOM ya está listo o espera el evento
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountTypeaheads);
} else {
    mountTypeaheads();
}

// Re-montaje en HMR / actualizaciones de Vite (por si aplica)
if (import.meta && import.meta.hot) {
    import.meta.hot.accept(() => {
        mountTypeaheads();
    });
}
