<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppHeader from '@/components/shared/AppHeader.vue';

defineProps<{ title?: string }>();
</script>

<template>
    <Head>
        <title>{{ title ? title + ' - ' : '' }}Simulacros</title>
    </Head>
    <AppHeader />
    <main>
        <slot />
    </main>
</template>

<style scoped>
main {
    min-height: 60vh;
}
</style>
