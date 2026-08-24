<script setup lang="ts">
import type { HTMLAttributes, InputHTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { useVModel } from '@vueuse/core';
import { LabelForm } from '@/components/ui/label';
import { InputTypeHTMLAttribute } from '@vue/runtime-dom';




const props = defineProps<{
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes['class']
    placeholder?: InputHTMLAttributes['placeholder']
    type?: InputTypeHTMLAttribute
    mode?: InputHTMLAttributes['inputmode']
    title?: string
    disabled?: boolean,
    accept?: InputHTMLAttributes['accept'],
    multiple?: boolean,
    maxlength?: number,
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string | number): void
  (e: 'input', payload: Event): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
})
</script>

<template>
        <div class="course-field">
            <LabelForm :title="title"/>
            <input
                :accept="accept"
                :type="type??'text'"
                :inputmode="mode??'text'"
                :placeholder="placeholder"
                :disabled="disabled"
                :multiple="multiple"
                :maxlength="maxlength"
                v-if="type === 'file'"
                @input="(e) => emits('input', e)"
                data-slot="input"
                :class="cn(
                '',
                       props.class,)"
            >
            <input
                :accept="accept"
                :type="type??'text'"
                :inputmode="mode??'text'"
                :placeholder="placeholder"
                :disabled="disabled"
                :maxlength="maxlength"
                v-else
                v-model="modelValue"
                data-slot="input"
                :class="cn(
                '',
                       props.class,)"
            >

            <slot name="description" />

        </div>
</template>

<style scoped>

    .course-field {
        margin-bottom: 20px;
    }


</style>
