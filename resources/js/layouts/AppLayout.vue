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
    <div class="global-disclaimer-bar">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span><strong>Aviso:</strong> Sitio web privado e independiente de la Procuraduría General de la Nación. No realizamos trámites ni inscripciones; ofrecemos únicamente material de estudio para la convocatoria.</span>
    </div>
    <AppHeader />
    <main class="rbt-main-wrapper">
        <slot></slot>
    </main>
    <WhatsappButton />
</template>

<style>
.global-disclaimer-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 16px;
    background: #11364f;
    color: #f5e42c;
    font-size: 12px;
    font-weight: 500;
    text-align: center;
    line-height: 1.5;
    position: relative;
    z-index: 1000;
}

.global-disclaimer-bar strong {
    font-weight: 700;
}

@media (max-width: 768px) {
    .global-disclaimer-bar {
        font-size: 11px;
        padding: 6px 12px;
        line-height: 1.4;
    }
}
</style>
