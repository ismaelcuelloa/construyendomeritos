<script setup lang="ts">
import BadgeStatusOrder from '@/features/orders/components/BadgeStatusOrder.vue';
import SelectModern from '@/components/ui/SelectModern.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import Toast from '@/composables/toast';
import AppAdminLayout from '@/layouts/AppAdminLayout.vue';
import { Client } from '@/lib/client';
import { formatMoney } from '@/lib/utils';
import type { Order } from '@/types/project';
import { ref } from 'vue';

const props = defineProps<{
    order: Order;
}>();

const currentStatus = ref(props.order.status_id);
const updating = ref(false);

const orderStatuses = [
    { value: 0, text: '🟡 Pendiente' },
    { value: 1, text: '🔵 Procesando' },
    { value: 2, text: '🟢 Pagado' },
    { value: 3, text: '🔴 Fallido' },
    { value: 4, text: '⚫ Cancelado' },
    { value: 5, text: '🟣 Reembolsado' },
    { value: 6, text: '🟠 Manual' },
];

const updateStatus = async () => {
    if (updating.value) return;

    updating.value = true;
    try {
        await Client.patch(`${Client.ADMIN_ORDERS}/${props.order.id}/status`, {
            status_id: currentStatus.value,
        });

        Toast.success('Estado actualizado exitosamente');

        // Recargar la página después de 1 segundo
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    } catch (e: any) {
        Toast.error('Error al actualizar el estado');
        console.error(e);
    } finally {
        updating.value = false;
    }
};
</script>

<template>
    <AppAdminLayout title="Detalle de Orden">
        <div class="checkout-cart-total">
            <div class="order-header">
                <div>
                    <h4 class="mb-3">Órden # {{ order.number }} <BadgeStatusOrder class="h-100" :status_id="order.status_id" /></h4>
                    <h5 class="mb-1">{{ order.user?.full_name }}</h5>
                    <h5 class="mb-1">{{ order.user?.email }}</h5>
                </div>

                <div class="status-update-section">
                    <SelectModern v-model="currentStatus" :options="orderStatuses" label="Estado de la Orden" :disabled="updating" />
                    <Button @click="updateStatus" :disabled="updating || currentStatus === order.status_id" class="btn-update-status">
                        <span v-if="updating" class="btn-content">
                            <span class="spinner"></span>
                            Actualizando...
                        </span>
                        <span v-else class="btn-content"> Actualizar Estado </span>
                    </Button>
                </div>
            </div>

            <Separator class="mt--40 mb--40" />

            <h4 class="">Productos <span>Total</span></h4>

            <ul>
                <li v-for="item in order.items ?? []" :key="item.id">
                    {{ item.description }} X {{ item.quantity }}
                    <span>{{ formatMoney(item.unit_price) }}</span>
                </li>
            </ul>

            <h4 class="mt--30">
                Total <span>{{ formatMoney(order.amount) }}</span>
            </h4>
        </div>
    </AppAdminLayout>
</template>

<style scoped>
.ql-container.ql-snow {
    min-height: 200px;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 2rem;
    flex-wrap: wrap;
}

.status-update-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    min-width: 320px;
}

.btn-update-status {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: white !important;
    font-weight: 700 !important;
    padding: 0.875rem 1.75rem !important;
    border-radius: 12px !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    white-space: nowrap;
    border: none !important;
    font-size: 15px !important;
    letter-spacing: 0.3px !important;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.25) !important;
    position: relative !important;
    overflow: hidden !important;
}

.btn-update-status::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
}

.btn-update-status:hover:not(:disabled)::before {
    left: 100%;
}

.btn-update-status:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.35) !important;
    background: linear-gradient(135deg, #0d2a3e 0%, #133a54 100%) !important;
}

.btn-update-status:active:not(:disabled) {
    transform: translateY(0);
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.25) !important;
}

.btn-update-status:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none !important;
}

.btn-content {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Mejora de la sección de productos */
.checkout-cart-total {
    background: #ffffff;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}

.checkout-cart-total h4 {
    font-weight: 700;
    color: var(--color-heading);
    margin-bottom: 1.5rem;
}

.checkout-cart-total ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.checkout-cart-total ul li {
    padding: 1rem 0;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 15px;
    color: var(--color-body);
}

.checkout-cart-total ul li:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
    }

    .status-update-section {
        width: 100%;
        min-width: unset;
    }

    .btn-update-status {
        width: 100%;
        padding: 1rem 1.5rem !important;
    }

    .checkout-cart-total {
        padding: 1.5rem;
    }
}
</style>
