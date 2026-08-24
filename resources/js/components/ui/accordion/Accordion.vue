<script setup lang="ts">
const props = defineProps<{
    id?: string;
}>();
</script>

<template>

    <div class="rbt-accordion-style rbt-accordion-01  accordion">
        <div class="accordion" :id="id">
            <slot></slot>
        </div>
    </div>

</template>

<style scoped>

</style>
