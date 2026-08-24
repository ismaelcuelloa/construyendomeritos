<script setup lang="ts">
import { cva } from 'class-variance-authority';

const badge = cva('', {
    variants: {
        variant: {
            default: 'rbt-badge',
            rounded : 'rbt-default-badge position-static',
            compact : 'rbt-badge-5'
        },
        size: {
            default: '',
            sm: 'text-xs px-1.5 py-0.5',
            lg: 'text-base px-3 py-1.5'
        },
        type : {
            default : '',
            success : 'bg-color-success-opacity color-success',
            warning : 'bg-color-warning-opacity color-warning',
            danger : 'bg-color-danger-opacity color-danger',
            info : 'bg-color-secondary-opacity color-',
            pink : 'badge-pink'
        }
    },
    defaultVariants: {
        variant: 'default',
        size: 'default',
        type : 'default'
    }
});

interface Props {
    variant?: 'default' | 'rounded' | 'compact';
    size?: 'default' | 'sm' | 'lg';
    type?: 'default' | 'success' | 'warning' | 'danger' | 'info' | 'pink';
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
    size: 'default',
    type : 'default'
});
</script>

<template>
    <span :class="badge({ variant: props.variant, size: props.size, type : props.type })">
        <slot />
    </span>
</template>

<style scoped>
.badge-pink {
    background-color: rgba(236, 72, 153, 0.1);
    color: #EC4899;
}
</style>
