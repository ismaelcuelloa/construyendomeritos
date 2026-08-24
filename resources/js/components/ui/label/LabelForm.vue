<script setup lang="ts">

defineProps<{
    title?:string
}>()
</script>

<template>
    <label v-show="title?.trim() != ''" class="label">
        {{ title }}
        <slot></slot>
    </label>
</template>

<style scoped>
    .label{
        display: block;
        font-size: 14px;
        margin-bottom: 9px;
        font-weight: 600;
        text-transform: capitalize;
        color: var(--color-heading);
    }

    .active-dark-mode .label {
        color: var(--color-white-off);
    }
</style>
