<script setup lang="ts">

import Select from '@/components/ui/select/Select.vue';
import { Client } from '@/lib/client';
import { watch, onMounted } from 'vue';
import { ref } from 'vue';
import type { SelectItem } from '@/components/ui/select';

interface Props {
    categoryId?: string | number;
}

const props = defineProps<Props>();

const subcategories = ref<SelectItem[]>([]);

const getSubcategories = async () => {
    if (!props.categoryId) {
        subcategories.value = [];
        return;
    }
    const response = await Client.post(`${Client.ADMIN_CATEGORIES}/${props.categoryId}/subcategorias/list?per_page=1500`);
    subcategories.value = parseItems(response.data.data);
};

const parseItems = (items: any[]) => {
    const parsedItems: SelectItem[] = [];
    items.forEach((item: any) => {
        parsedItems.push({
            value: item.id,
            text: item.title
        });
    });
    return parsedItems;
};

onMounted(() => {
    getSubcategories();
});

watch(() => props.categoryId, () => {
    getSubcategories();
});

</script>

<template>
    <Select :data="subcategories" title="Subcategoría">
        <template #description>
            <slot name="description" />
        </template>
    </Select>
</template>

<style scoped>

</style>
