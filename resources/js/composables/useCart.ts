import { Client } from '@/lib/client';
import { CartItem, Course } from '@/types/project';
import { ref, watch } from 'vue';

const STORAGE_KEY = 'cart';

const items = ref<CartItem[]>([]);
const paying = ref(false);

export function useCart() {
    const open = () => {
        document.querySelector('.rbt-cart-side-menu')?.classList.add('side-menu-active');
        document.body.classList.add('cart-sidenav-menu-active');
    };

    const close = () => {
        document.querySelector('.rbt-cart-side-menu')?.classList.remove('side-menu-active');
        document.body.classList.remove('cart-sidenav-menu-active');
    };
    const loadCart = () => {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            items.value = JSON.parse(saved);
        }
    };

    watch(
        items,
        () => {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items.value));
        },
        { deep: true },
    );

    const addItem = (item: CartItem) => {
        if (!has(item.id)) {
            items.value.push({ ...item, quantity: 1 });
        }
    };

    const has = (id: number) => {
        const existing = items.value.find((i) => i.id === id);
        return existing !== undefined;
    };

    const removeItem = (id: number) => {
        items.value = items.value.filter((i) => i.id !== id);
    };

    const clearCart = () => {
        items.value = [];
    };

    const total = () => {
        return items.value.reduce((sum, item) => sum + item.price * item.quantity, 0);
    };

    const totalItems = () => {
        return items.value.reduce((sum, item) => sum + item.quantity, 0);
    };

    const addFromCourse = (course: Course) => {
        addItem({
            id: course.id,
            title: course.title,
            quantity: 1,
            price: course.price,
        });
    };

    const checkout = async () => {
        paying.value = true;

        try {
            const response = await Client.post('/payments/checkout', { items: items.value });
            console.log(response.data);
            await redirectToPayment(response.data);
        } catch (e: any) {
            console.log(e);

            // Si el usuario no está autenticado (401), redirigir al registro
            if (e.response && e.response.status === 401) {
                // Guardar la URL actual para volver después del registro
                localStorage.setItem('checkout_return_url', window.location.pathname);
                // Guardar flag para mostrar mensaje en el registro
                localStorage.setItem('checkout_redirect', 'true');
                // El carrito ya está guardado en localStorage
                window.location.href = '/register';
                return;
            }

            paying.value = false;
        }
    };

    const redirectToPayment = async (data: any) => {
        if (data.method == 'epayco') {
            await redirectToEpayco(data);
        } else if (data.method == 'payu') {
            await redirectToPayU(data);
        } else if (data.method == 'wompi') {
            await redirectToWompi(data);
        } else {
            console.log('No se encontro el metodo de pago');
        }
    };

    const redirectToEpayco = async (data: any) => {
        console.log(data.data);
        // @ts-expect-error - ePayco is a global variable from external script
        const handler = ePayco.checkout.configure({
            key: data.data.key,
            test: data.data.test === 'true',
        });

        handler.open(data.data);
    };

    const redirectToPayU = async (data: any) => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = data.url;

        const fields: Record<string, string> = {
            merchantId: data.data.merchantId,
            accountId: data.data.accountId,
            description: data.data.description,
            referenceCode: data.data.referenceCode,
            amount: data.data.amount,
            tax: '0',
            taxReturnBase: '0',
            currency: data.data.currency,
            signature: data.data.signature,
            responseUrl: Client.getEndpoint(data.data.responseUrl),
            confirmationUrl: Client.getEndpoint(data.data.confirmationUrl),
            buyerEmail: data.data.buyerEmail,
        };

        for (const [key, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        console.log(form);
        form.submit();
    };

    const redirectToWompi = async (data: any) => {
        // Verificar que el widget de Wompi esté disponible
        // @ts-expect-error - WidgetCheckout is a global variable from external script
        if (typeof WidgetCheckout === 'undefined') {
            console.error('El widget de Wompi no está cargado');
            paying.value = false;
            return;
        }

        try {
            // @ts-expect-error - WidgetCheckout is a global variable from external script
            const checkout = new WidgetCheckout({
                currency: data.data.currency,
                amountInCents: data.data['amount-in-cents'],
                reference: data.data.reference,
                publicKey: data.data['public-key'],
                signature: {
                    integrity: data.data['signature:integrity'],
                },
                redirectUrl: Client.getEndpoint(data.data['redirect-url']),
                customerData: {
                    email: data.data['customer-data:email'],
                    fullName: data.data['customer-data:full-name'],
                },
            });

            // Abrir el widget de pago
            checkout.open((result: any) => {
                const transaction = result.transaction;
                console.log('Wompi Transaction ID:', transaction.id);
                console.log('Wompi Transaction status:', transaction.status);

                // Redirigir a la página de confirmación
                window.location.href = Client.getEndpoint(data.data['redirect-url']);
            });
        } catch (error) {
            console.error('Error al abrir el widget de Wompi:', error);
            paying.value = false;
        }
    };

    return {
        items,
        paying,
        addItem,
        removeItem,
        clearCart,
        total,
        totalItems,
        loadCart,
        addFromCourse,
        has,
        open,
        checkout,
        close,
    };
}
