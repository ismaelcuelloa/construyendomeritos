<script setup lang="ts">
import AppHeader from '@/components/shared/AppHeader.vue';
import WhatsappButton from '@/components/shared/WhatsappButton.vue';
import type { BreadcrumbItemType } from '@/types';
import '@bhplugin/vue3-datatable/dist/style.css';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppHeader />
    <main class="rbt-main-wrapper">
        <slot></slot>
    </main>
    <WhatsappButton />
</template>

<style></style>
