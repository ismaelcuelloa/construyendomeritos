<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';
import { cva } from 'class-variance-authority';



interface Props {
    href : string,
    class?: HTMLAttributes['class'],
    as ?: 'a' | 'link'
    variant?: 'default' | 'linked' ;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'linked',
});

const badge = cva(props.class, {
    variants: {
        variant: {
            default: '',
            linked : 'rbt-btn-link left-icon d-flex align-items-center',
        },
    },
    defaultVariants: {
        variant: 'linked',
    }
});

const as = props.as ?? 'link';

</script>

<template>
    <Link v-if="as === 'link'" :href="props.href" :class="badge({ variant: props.variant})">
        <slot></slot>
    </Link>
    <a v-else :href="props.href" :class="badge({ variant: props.variant})">
        <slot></slot>
    </a>
</template>

<style scoped>

</style>
