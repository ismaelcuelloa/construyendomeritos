<script setup lang="ts">
import AppCategory from '@/features/catalog/components/AppCategory.vue';
import { Category } from '@/types/project';

interface Props {
    categories?: Category[];
}

defineProps<Props>();
</script>

<template>
    <!-- Start Card Area -->
    <div class="row g-5">
        <div v-for="category in categories" :key="category.id" class="col-lg-4 col-md-6 col-12">
            <AppCategory :category="category" />
        </div>
    </div>
</template>

<style scoped></style>
