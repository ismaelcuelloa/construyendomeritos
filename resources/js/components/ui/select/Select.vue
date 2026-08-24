<script setup lang="ts">
import { onMounted, watch } from 'vue';
import type { SelectItem } from '.';
import { LabelForm } from '@/components/ui/label';
import { useVModel } from '@vueuse/core';

interface Props {
    defaultValue?: string | number
    modelValue?: string | number
    data: SelectItem[];
    title?: string;
    allowClear?: boolean;
    disabled?: boolean;
    showLabel?: boolean;
}


const emits = defineEmits<{
    (e: 'update:modelValue', payload: string | number): void
}>()

const props = defineProps<Props>();

const modelValue = useVModel(props, 'modelValue', emits, {
    passive: true,
    defaultValue: props.defaultValue,
});

const initializeSelect = (refresh = false) => {
    if(refresh){
        $('.selectpicker').selectpicker('refresh');
    }else{
        $('.selectpicker').selectpicker();
    }
};

onMounted(() => {
    initializeSelect();
});

watch(() => props.data, () => {
    console.log(props.data);
    setTimeout(()=>{
        initializeSelect(true);
    },200);
});


</script>

<template>
    <div class="rbt-modern-select bg-transparent height-45">
        <LabelForm v-if="showLabel !== false" :title="title"/>
        <select 
            v-model="modelValue" 
            class="selectpicker w-100" 
            data-style="btn dropdown-toggle btn-light" 
            :title="title"
            :data-none-selected-text="allowClear ? 'Todos' : title"
            :disabled="disabled"
        >
            <option v-if="allowClear" :value="null">Todos</option>
            <option v-for="(item, index) in data" :key="index" :value="item.value"
                    :selected="modelValue === item.value">{{ item.text
                }}
            </option>
        </select>
        <slot name="description" />
    </div>
</template>

<style scoped>

</style>
