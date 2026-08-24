<script setup lang="ts">
import AppFooter from '@/components/shared/AppFooter.vue';
import * as Cart from '@/composables/useCart';
import { useMetaPixel } from '@/composables/useMetaPixel';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Category } from '@/types/project';
import { usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';

interface Props {
    categories?: Category[];
}

withDefaults(defineProps<Props>(), {
    categories: () => [],
});

const page = usePage();
const metaPixel = useMetaPixel();

onMounted(() => {
    // Inicializar Meta Pixel
    metaPixel.init();

    // Si el usuario acaba de registrarse y hay una URL de retorno guardada
    const returnUrl = localStorage.getItem('checkout_return_url');
    const user = page.props.auth?.user;
    const cart = Cart.useCart();

    if (user && returnUrl && cart.items.value.length > 0) {
        // Limpiar la URL de retorno del localStorage
        localStorage.removeItem('checkout_return_url');
        // Establecer flag para que la página del curso ejecute el checkout
        sessionStorage.setItem('should_checkout', 'true');
        // Redirigir a la página del curso
        window.location.href = returnUrl;
    }
});
</script>

<template>
    <AppLayout>
        <slot></slot>
    </AppLayout>
    <AppFooter :categories="categories" />
</template>

<style scoped></style>
