<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

export interface BreadcrumbItemType {
    title: string;
    href?: string;
}

defineProps<{
    breadcrumbs: BreadcrumbItemType[];
}>();
</script>

<template>
    <!-- Start Breadcrumb Area  -->
    <ul class="page-list">
        <template v-for="(item, index) in breadcrumbs" :key="index">
            <template v-if="index === breadcrumbs.length - 1">
                <li class="rbt-breadcrumb-item active">{{ item.title }}</li>
            </template>
            <template v-else>
                <li class="rbt-breadcrumb-item">
                    <Link :href="item.href ?? '#'">{{ item.title }}</Link>
                </li>
            </template>
            <li v-if="index !== breadcrumbs.length - 1">
                <div class="icon-right"><i class="feather-chevron-right"></i></div>
            </li>
        </template>
    </ul>
    <!-- End Breadcrumb Area  -->
</template>
