<script setup lang="ts">
import Select from '@/components/ui/select/Select.vue';
import { ref, onMounted } from 'vue';
import type { SelectItem } from '@/components/ui/select';

const orderStatuses = ref<SelectItem[]>([
    { value: 0, text: 'Pendiente' },
    { value: 1, text: 'Procesando' },
    { value: 2, text: 'Pagado' },
    { value: 3, text: 'Fallido' },
    { value: 4, text: 'Cancelado' },
    { value: 5, text: 'Reembolsado' },
    { value: 6, text: 'Manual' },
    { value: 7, text: 'Demo' },
    { value: 8, text: 'Demo Expirado' },
]);

defineProps<{
    disabled?: boolean;
    placeholder?: string;
    allowClear?: boolean;
    showLabel?: boolean;
}>();
</script>

<template>
    <Select 
        :data="orderStatuses" 
        :title="placeholder || ''" 
        :disabled="disabled"
        :allowClear="allowClear"
        :showLabel="showLabel"
    >
        <template #description>
            <slot name="description" />
        </template>
    </Select>
</template>
