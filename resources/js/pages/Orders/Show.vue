<script setup lang="ts">
import BadgeStatusOrder from '@/features/orders/components/BadgeStatusOrder.vue';
import { Separator } from '@/components/ui/separator';
import { useMetaPixel } from '@/composables/useMetaPixel';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatMoney } from '@/lib/utils';
import type { Order } from '@/types/project';
import { onMounted } from 'vue';

const props = defineProps<{
    order: Order;
}>();

const metaPixel = useMetaPixel();

onMounted(() => {
    console.log('Order page loaded - Status ID:', props.order.status_id);
    console.log('Order details:', props.order);

    // Disparar evento de compra solo si el pago fue exitoso
    // PAID = 2 según OrderStatus enum
    if (props.order.status_id === 2) {
        console.log('Payment successful - Tracking purchase with Meta Pixel');

        const contentIds = props.order.items?.map((item) => item.id?.toString() || '') || [];
        const contents =
            props.order.items?.map((item) => ({
                id: item.id?.toString() || '',
                quantity: item.quantity || 1,
            })) || [];

        const purchaseData = {
            value: parseFloat(props.order.amount?.toString() || '0'),
            currency: props.order.currency || 'COP',
            content_ids: contentIds,
            content_type: 'product' as const,
            contents: contents,
            num_items: props.order.items?.length || 0,
        };

        console.log('Purchase data:', purchaseData);
        metaPixel.trackPurchase(purchaseData);
    } else {
        console.log('Payment not successful - Status:', props.order.status_id);
    }
});
</script>

<template>
    <AppLayout title="Curso">
        <div class="row mx-0">
            <div class="col-sm-1 col-md-2 col-lg-3 col-12"></div>

            <div class="col-sm-10 col-md-8 col-lg-6 col-12">
                <div class="checkout-cart-total mt--60">
                    <h4 class="mb-3">Órden # {{ order.number }} <BadgeStatusOrder class="h-100" :status_id="order.status_id" /></h4>
                    <h5 class="mb-1">{{ order.user?.full_name }}</h5>
                    <h5 class="mb-1">{{ order.user?.email }}</h5>

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
            </div>

            <div class="col-sm-1 col-md-2 col-lg-3 col-12"></div>
        </div>
    </AppLayout>
</template>

<style scoped></style>
