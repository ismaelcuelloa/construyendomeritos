<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { ref, watch } from 'vue'
import { Input } from '@/components/ui/input';

const props = defineProps<{
    defaultValue?: string | number
    modelValue?: string | number
    class?: HTMLAttributes['class']
    placeholder?: string
}>()

const emits = defineEmits<{
    'update:modelValue': [payload: string | number]
    'search': [payload: string]
}>();

const searchValue = ref<string>(String(props.modelValue || props.defaultValue || ''));
let debounceTimeout: ReturnType<typeof setTimeout>;

// Observar cambios en el searchValue
watch(searchValue, (newValue) => {
    emits('update:modelValue', newValue);
    
    if (debounceTimeout) {
        clearTimeout(debounceTimeout);
    }
    
    debounceTimeout = setTimeout(() => {
        console.log('Emitting search:', newValue);
        emits('search', newValue);
    }, 500);
});

</script>

<template>
    <Input 
        class="w-100" 
        type="search" 
        v-model="searchValue"
        :placeholder="placeholder || 'Buscar...'"
    />
</template>

<style scoped>

</style>
