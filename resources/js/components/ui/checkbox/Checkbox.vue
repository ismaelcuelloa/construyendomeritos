<script setup lang="ts">
import type { HTMLAttributes, InputHTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { useVModel } from '@vueuse/core';
import { LabelForm } from '@/components/ui/label';

const props = defineProps<{
    defaultValue?: boolean
    modelValue?: boolean
    class?: HTMLAttributes['class']
    title?: string
    disabled?: boolean,
}>()

const defaultValue = props.defaultValue ?? false;

const emits = defineEmits<{
    (e: 'update:modelValue', payload: boolean): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue ?? props.modelValue ?? false,
})
</script>

<template>

    <div class="course-field">

        <div class="form-check form-switch">
            <LabelForm class="form-check-label d-flex align-items-center mb-0" :title="title">
                <input
                    type="checkbox"
                    role="switch"
                    :disabled="disabled"
                    v-model="modelValue"
                    data-slot="input"
                    :class="cn('form-check-input ms-3',props.class,)"
                >
            </LabelForm>

        </div>
        <slot name="description" />
    </div>

</template>
