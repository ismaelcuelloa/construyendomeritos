<script setup lang="ts">

import Select from '@/components/ui/select/Select.vue';
import { Client } from '@/lib/client';
import { onMounted, computed } from 'vue';
import { ref } from 'vue';
import type { SelectItem } from '@/components/ui/select';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    restrictToUser?: boolean; // Si es true, solo muestra el rol 'user'
}>();

const roles = ref<SelectItem[]>([]);
const page = usePage();

const authUser = computed(() => page.props.auth?.user);
const isSuperUser = computed(() => {
    return authUser.value?.roles?.some((role: any) => role.name === 'super_user');
});

const getRoles = async () => {
    const response = await Client.get(Client.ADMIN_ROLES+'/list');
    let allRoles = parseItems(response.data.roles);
    
    // Si no es super_user, solo mostrar el rol 'user'
    if (!isSuperUser.value || props.restrictToUser) {
        allRoles = allRoles.filter((role: SelectItem) => role.value === 'user');
    }
    
    roles.value = allRoles;
};

const parseItems = (items: any[]) => {
    const parsedItems: SelectItem[] = [];
    items.forEach((item: any) => {
        parsedItems.push({
            value: item.name,
            text: item.description
        });
    });
    return parsedItems;
}

onMounted(() => {
    getRoles();
});

</script>

<template>
    <Select :data="roles" title="Roles" >
        <template #description>
            <slot name="description" />
        </template>
    </Select>
</template>

<style scoped>

</style>
