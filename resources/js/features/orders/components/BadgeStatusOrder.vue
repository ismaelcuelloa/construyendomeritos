<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { getOrderStatusLabel, OrderStatus } from '@/types/orders/OrderStatus';
import { computed } from 'vue';

const props = defineProps<{
    status_id: OrderStatus;
}>();

const typeText = computed(() => {
    return getOrderStatusLabel(props.status_id);
});

const typeStatus = computed(() => {
    if (props.status_id == OrderStatus.PENDING) {
        return 'warning';
    } else if (props.status_id == OrderStatus.PROCESSING) {
        return 'info';
    } else if (props.status_id == OrderStatus.PAID) {
        return 'success';
    } else if (props.status_id == OrderStatus.FAILED) {
        return 'danger';
    } else if (props.status_id == OrderStatus.CANCELLED) {
        return 'danger';
    } else if (props.status_id == OrderStatus.REFUNDED) {
        return 'default';
    } else if (props.status_id == OrderStatus.MANUAL) {
        return 'info';
    } else if (props.status_id == OrderStatus.DEMO) {
        return 'pink';
    } else if (props.status_id == OrderStatus.DEMO_EXPIRED) {
        return 'danger';
    } else {
        return 'default';
    }
});
</script>

<template>
    <Badge :type="typeStatus">{{ typeText }}</Badge>
</template>

<style scoped></style>
