<script setup lang="ts">
import { Button } from '@/components/ui/button';
import * as Cart from '@/composables/useCart';
import { formatMoney } from '@/lib/utils';
import { router } from '@inertiajs/vue3';
import { ArrowRight, Package, ShoppingCart, Trash2, X } from 'lucide-vue-next';
import { computed } from 'vue';

const cart = Cart.useCart();
const isEmpty = computed(() => cart.items.value.length === 0);

const goToCourses = () => {
    cart.close();
    router.visit('/cursos');
};
</script>

<template>
    <div class="rbt-cart-side-menu">
        <div class="inner-wrapper">
            <div class="inner-top">
                <div class="content">
                    <div class="title-section">
                        <ShoppingCart :size="24" class="cart-icon" />
                        <div class="title-wrapper">
                            <h4 class="title mb--0">Carrito de Compras</h4>
                            <span class="subtitle" v-if="!isEmpty"
                                >{{ cart.totalItems() }} {{ cart.totalItems() === 1 ? 'material' : 'materiales' }}</span
                            >
                        </div>
                    </div>
                    <div class="rbt-btn-close" id="btn_sideNavClose">
                        <Button variant="ghost" size="icon" @click="cart.close()" class="close-button">
                            <X :size="20" />
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="isEmpty" class="empty-cart">
                <div class="empty-icon">
                    <Package :size="64" :stroke-width="1.5" />
                </div>
                <h5 class="empty-title">Tu carrito está vacío</h5>
                <p class="empty-text">Agrega materiales de estudio para comenzar</p>
                <Button @click="goToCourses" variant="default" class="browse-btn">
                    <span>Explorar materiales</span>
                    <ArrowRight :size="16" />
                </Button>
            </div>

            <!-- Cart Items -->
            <nav v-else class="side-nav w-100">
                <ul class="rbt-minicart-wrapper">
                    <li v-for="item in cart.items.value" :key="item.id" class="minicart-item">
                        <div class="item-image">
                            <div class="image-placeholder">
                                <i class="feather-book-open"></i>
                            </div>
                        </div>
                        <div class="product-content">
                            <h6 class="title">{{ item.title }}</h6>
                            <div class="item-meta">
                                <span class="quantity">Cantidad: {{ item.quantity }}</span>
                            </div>
                            <span class="price">{{ formatMoney(item.price) }}</span>
                        </div>
                        <div class="close-btn">
                            <Button @click="cart.removeItem(item.id)" variant="ghost" size="icon" class="remove-btn">
                                <Trash2 :size="18" />
                            </Button>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- Footer -->
            <div v-if="!isEmpty" class="rbt-minicart-footer">
                <div class="cart-summary">
                    <div class="summary-row">
                        <span class="label">Subtotal</span>
                        <span class="value">{{ formatMoney(cart.total()) }}</span>
                    </div>
                    <div class="summary-row total-row">
                        <span class="label"><strong>Total</strong></span>
                        <span class="value total-price"
                            ><strong>{{ formatMoney(cart.total()) }}</strong></span
                        >
                    </div>
                </div>

                <div class="rbt-minicart-bottom">
                    <div class="cart-legal-notice">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p><strong>Aviso importante:</strong> Al proceder con el pago, aceptas que estás comprando material de estudio de una plataforma privada, sin ninguna relación institucional o comercial con la convocatoria oficial de la Procuraduría.</p>
                    </div>
                    <Button :disabled="cart.paying.value" @click="cart.checkout()" class="checkout-btn w-100">
                        <ShoppingCart :size="18" v-if="!cart.paying.value" />
                        <div v-if="cart.paying.value" class="spinner"></div>
                        <span>{{ cart.paying.value ? 'Procesando...' : 'Proceder al Pago' }}</span>
                        <ArrowRight :size="18" v-if="!cart.paying.value" />
                    </Button>

                    <p class="secure-text">
                        <i class="feather-shield"></i>
                        Pago 100% seguro
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div @click="cart.close()" class="close_side_menu"></div>
</template>

<style scoped>
.rbt-cart-side-menu {
    position: fixed;
    right: -500px;
    top: 0;
    width: 500px;
    height: 100vh;
    background: #ffffff;
    box-shadow: -8px 0 40px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    transition: right 0.4s cubic-bezier(0.23, 1, 0.32, 1);
}

.rbt-cart-side-menu.side-menu-active {
    right: 0;
}

.inner-wrapper {
    display: flex;
    flex-direction: column;
    height: 100%;
}

/* Header */
.inner-top {
    padding: 20px 24px;
    border-bottom: 2px solid #f0f0f0;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.03) 0%, rgba(245, 228, 44, 0.02) 100%);
}

.content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.title-section {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}

.cart-icon {
    color: #133a54;
    flex-shrink: 0;
}

.title-wrapper {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.title {
    font-size: 18px;
    font-weight: 800;
    color: #151515;
    line-height: 1.2;
    white-space: nowrap;
    margin: 0;
}

.subtitle {
    font-size: 11px;
    color: #133a54;
    font-weight: 600;
    white-space: nowrap;
    padding: 2px 8px;
    background: rgba(19, 58, 84, 0.1);
    border-radius: 12px;
    display: inline-block;
    width: fit-content;
}

.close-button {
    width: 36px !important;
    height: 36px !important;
    border-radius: 10px !important;
    color: #666 !important;
    background: #f5f5f5 !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    border: 2px solid transparent !important;
}

.close-button:hover {
    color: #ffffff !important;
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%) !important;
    border-color: #dc2626 !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

/* Empty State */
.empty-cart {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 24px;
    text-align: center;
}

.empty-icon {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.05) 0%, rgba(245, 228, 44, 0.02) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
    color: rgba(19, 58, 84, 0.4);
}

.empty-title {
    font-size: 20px;
    font-weight: 700;
    color: #151515;
    margin-bottom: 8px;
}

.empty-text {
    font-size: 14px;
    color: #666;
    margin-bottom: 24px;
}

.browse-btn {
    background: linear-gradient(135deg, #133a54 0%, #f5e42c 100%) !important;
    color: #ffffff !important;
    border: none !important;
    padding: 12px 24px !important;
    font-weight: 600 !important;
    border-radius: 10px !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    transition: all 0.3s ease !important;
}

.browse-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.3);
}

/* Cart Items */
.side-nav {
    flex: 1;
    overflow-y: auto;
    padding: 0;
}

.side-nav::-webkit-scrollbar {
    width: 6px;
}

.side-nav::-webkit-scrollbar-track {
    background: #f5f5f5;
}

.side-nav::-webkit-scrollbar-thumb {
    background: #ddd;
    border-radius: 10px;
}

.side-nav::-webkit-scrollbar-thumb:hover {
    background: #ccc;
}

.rbt-minicart-wrapper {
    list-style: none;
    padding: 0;
    margin: 0;
}

.minicart-item {
    display: flex;
    gap: 14px;
    padding: 20px 24px;
    transition: all 0.3s ease;
    border-bottom: 1px solid #f0f0f0;
}

.minicart-item:hover {
    background: rgba(19, 58, 84, 0.02);
}

.item-image {
    flex-shrink: 0;
}

.image-placeholder {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(245, 228, 44, 0.05) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #133a54;
    font-size: 28px;
}

.product-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
}

.product-content .title {
    font-size: 15px;
    font-weight: 700;
    color: #151515;
    line-height: 1.4;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.item-meta {
    display: flex;
    align-items: center;
    gap: 12px;
}

.quantity {
    font-size: 13px;
    color: #666;
    font-weight: 500;
}

.price {
    font-size: 18px;
    font-weight: 800;
    color: #133a54;
}

.close-btn {
    flex-shrink: 0;
    display: flex;
    align-items: flex-start;
    padding-top: 4px;
}

.remove-btn {
    width: 36px !important;
    height: 36px !important;
    border-radius: 8px !important;
    color: #999 !important;
    background: #f5f5f5 !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    border: 2px solid transparent !important;
}

.remove-btn:hover {
    color: #ffffff !important;
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%) !important;
    border-color: #dc2626 !important;
    transform: translateY(-2px) scale(1.05);
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35);
}

.remove-btn:active {
    transform: translateY(0) scale(0.98);
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.25);
}

/* Footer */
.rbt-minicart-footer {
    border-top: 2px solid #f0f0f0;
    background: #fafafa;
    padding: 24px;
}

.cart-summary {
    margin-bottom: 24px;
    background: #ffffff;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #f0f0f0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
}

.summary-row .label {
    font-size: 15px;
    color: #666;
    font-weight: 500;
}

.summary-row .value {
    font-size: 15px;
    color: #333;
    font-weight: 600;
}

.total-row {
    border-top: 2px solid #e0e0e0;
    padding-top: 12px;
    margin-top: 8px;
}

.total-row .label {
    font-size: 17px;
    color: #151515;
}

.total-price {
    font-size: 22px;
    background: linear-gradient(135deg, #133a54 0%, #f5e42c 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.cart-legal-notice {
    display: flex;
    gap: 8px;
    align-items: flex-start;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-left: 3px solid #f59e0b;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 14px;
}

.cart-legal-notice p {
    margin: 0;
    font-size: 11px;
    line-height: 1.5;
    color: #92400e;
    font-weight: 500;
}

.cart-legal-notice p strong {
    font-weight: 800;
    color: #78350f;
}

.checkout-btn {
    background: linear-gradient(135deg, #133a54 0%, #f5e42c 100%) !important;
    color: #ffffff !important;
    border: none !important;
    padding: 16px 24px !important;
    font-weight: 700 !important;
    font-size: 15px !important;
    border-radius: 12px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 10px !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 8px 25px rgba(19, 58, 84, 0.3) !important;
    margin-bottom: 12px !important;
}

.checkout-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(19, 58, 84, 0.4) !important;
}

.checkout-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.secure-text {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 12px;
    color: #666;
    margin: 0;
    font-weight: 500;
}

.secure-text i {
    color: #28a745;
    font-size: 14px;
}

/* Overlay */
.close_side_menu {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9998;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    backdrop-filter: blur(2px);
}

body.cart-sidenav-menu-active .close_side_menu {
    opacity: 1;
    visibility: visible;
}

/* Responsive */
@media (max-width: 480px) {
    .rbt-cart-side-menu {
        width: 100%;
        right: -100%;
    }

    .inner-top {
        padding: 20px 16px 16px;
    }

    .minicart-item {
        padding: 12px 16px;
    }

    .rbt-minicart-footer {
        padding: 20px 16px;
    }

    .image-placeholder {
        width: 60px;
        height: 60px;
    }
}
</style>
