import { usePage } from '@inertiajs/vue3';

interface MetaPixel {
    (command: 'init', pixelId: string): void;
    (command: 'track', event: string, data?: Record<string, any>): void;
    (command: 'trackCustom', event: string, data?: Record<string, any>): void;
}

declare global {
    interface Window {
        fbq?: MetaPixel;
        _fbq?: MetaPixel;
    }
}

export function useMetaPixel() {
    const page = usePage();
    const pixelId = (page.props as any).metaPixelId as string | undefined;

    /**
     * Inicializa Meta Pixel
     */
    function init() {
        if (!pixelId) {
            console.warn('Meta Pixel ID not configured');
            return;
        }

        if (window.fbq) {
            console.log('Meta Pixel already initialized with ID:', pixelId);
            return;
        }

        console.log('Initializing Meta Pixel with ID:', pixelId);

        // Código de inicialización del pixel
        const f = window as any;
        const b = document;
        const e = 'script';

        if (f.fbq) return;

        const n: any = (f.fbq = function (...args: any[]) {
            if (n.callMethod) {
                n.callMethod(...args);
            } else {
                n.queue.push(args);
            }
        });

        if (!f._fbq) f._fbq = n;
        n.push = n;
        n.loaded = true;
        n.version = '2.0';
        n.queue = [];

        const t: HTMLScriptElement = b.createElement(e) as HTMLScriptElement;
        t.async = true;
        t.src = 'https://connect.facebook.net/en_US/fbevents.js';

        const s = b.getElementsByTagName(e)[0];
        s.parentNode?.insertBefore(t, s);

        window.fbq?.('init', pixelId);
        window.fbq?.('track', 'PageView');

        console.log('Meta Pixel initialized successfully');
    }

    /**
     * Rastrea un evento estándar
     */
    function track(event: string, data?: Record<string, any>) {
        if (!window.fbq) {
            console.warn('Meta Pixel not initialized - cannot track event:', event);
            return;
        }

        console.log('Meta Pixel tracking event:', event, data);
        window.fbq('track', event, data);
    }

    /**
     * Rastrea un evento personalizado
     */
    function trackCustom(event: string, data?: Record<string, any>) {
        if (!window.fbq) {
            console.warn('Meta Pixel not initialized');
            return;
        }

        window.fbq('trackCustom', event, data);
    }

    /**
     * Rastrea una compra (Purchase)
     */
    function trackPurchase(params: {
        value: number;
        currency: string;
        content_ids?: string[];
        content_type?: 'product' | 'product_group';
        contents?: Array<{ id: string; quantity: number }>;
        num_items?: number;
    }) {
        track('Purchase', params);
    }

    /**
     * Rastrea cuando se inicia el checkout
     */
    function trackInitiateCheckout(params?: {
        value?: number;
        currency?: string;
        content_ids?: string[];
        content_type?: 'product' | 'product_group';
        contents?: Array<{ id: string; quantity: number }>;
        num_items?: number;
    }) {
        track('InitiateCheckout', params);
    }

    /**
     * Rastrea cuando se agrega al carrito
     */
    function trackAddToCart(params: {
        value: number;
        currency: string;
        content_ids: string[];
        content_type?: 'product' | 'product_group';
        contents?: Array<{ id: string; quantity: number }>;
    }) {
        track('AddToCart', params);
    }

    /**
     * Rastrea cuando se visualiza contenido
     */
    function trackViewContent(params: {
        value?: number;
        currency?: string;
        content_ids: string[];
        content_type?: 'product' | 'product_group';
        content_name?: string;
    }) {
        track('ViewContent', params);
    }

    return {
        init,
        track,
        trackCustom,
        trackPurchase,
        trackInitiateCheckout,
        trackAddToCart,
        trackViewContent,
    };
}
