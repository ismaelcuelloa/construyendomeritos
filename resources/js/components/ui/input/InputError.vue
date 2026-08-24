<script setup lang="ts">

const props = defineProps<{
    text ?: string
}>()

</script>

<template>
    <small v-show="props.text !== undefined && props.text != ''" class=" color-danger description"><i class="feather-info me-2"></i>{{props.text}}</small>
</template>

<style scoped>
    .description {
        margin-top: 5px;
        font-size: 0.875rem;
        color: var(--color-white-off);
    }
</style>
