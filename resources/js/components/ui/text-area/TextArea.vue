<script setup lang="ts">

import { LabelForm } from '@/components/ui/label';
import type { HTMLAttributes, InputHTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { useVModel } from '@vueuse/core';

const props = defineProps<{
    defaultValue?: string | number
    modelValue?: string | number
    class?: HTMLAttributes['class']
    placeholder?: InputHTMLAttributes['placeholder']
    title?: string
    disabled?: boolean,
    rows?: number
}>()

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string | number): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
})

</script>

<template>

    <div class="course-field">
        <LabelForm :title="title"/>
        <textarea
            v-model="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :rows="rows??10"
            :class="cn(
                '',
                       props.class,)"
        ></textarea>
        <slot name="description" />
    </div>

</template>

<style scoped>
    .course-field {
        margin-bottom: 20px;
    }
</style>
