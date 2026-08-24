<script setup lang="ts">

import Select from '@/components/ui/select/Select.vue';
import { Client } from '@/lib/client';
import { onMounted } from 'vue';
import { ref } from 'vue';
import type { SelectItem } from '@/components/ui/select';

const categories = ref<SelectItem[]>([]);

const getCategories = async () => {
    const response = await Client.post(Client.ADMIN_CATEGORIES+'/list?per_page=1500');
    categories.value = parseItems(response.data.data) ;
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
}

onMounted(() => {
    getCategories();
});

</script>

<template>
    <Select :data="categories" title="Categorías" >
        <template #description>
            <slot name="description" />
        </template>
    </Select>
</template>

<style scoped>

</style>
