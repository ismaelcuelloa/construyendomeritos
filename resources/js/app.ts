import 'vue3-toastify/dist/index.css';
import '../css/dark_table.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import Vue3Toastify, { toast, type ToastContainerOptions } from 'vue3-toastify';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';
import { useCart } from './composables/useCart';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';

const appName = import.meta.env.VITE_APP_NAME || 'Construyendo M�ritos con Excelencia';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: Aura,
                    options: {
                        darkModeSelector: '.dark',
                    },
                },
            })
            .use(Vue3Toastify, {
                theme: 'auto',
                autoClose: 3000,
                position: toast.POSITION.TOP_RIGHT,
                pauseOnHover: false,
            } as ToastContainerOptions)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Deshabilitar click derecho
document.addEventListener('contextmenu', (e) => {
    e.preventDefault();
    return false;
});

// Deshabilitar teclas de desarrollador
document.addEventListener('keydown', (e) => {
    // Deshabilitar F12
    if (e.key === 'F12') {
        e.preventDefault();
        return false;
    }

    // Deshabilitar Ctrl+Shift+I / Cmd+Option+I
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'I') {
        e.preventDefault();
        return false;
    }

    // Deshabilitar Ctrl+Shift+C / Cmd+Option+C
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'C') {
        e.preventDefault();
        return false;
    }

    // Deshabilitar Ctrl+P / Cmd+P (impresión)
    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
        e.preventDefault();
        return false;
    }
});

// Deshabilitar impresión
window.addEventListener('beforeprint', (e) => {
    e.preventDefault();
    return false;
});

// This will set light / dark mode on page load...
initializeTheme();
useCart().loadCart();
