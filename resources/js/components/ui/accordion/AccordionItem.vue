<script setup lang="ts">

 import { computed } from 'vue';
 import { cn } from '@/lib/utils';

 const props = defineProps<{
     id: string;
     parentId?: string;
     open?: boolean;
     title?: string;
     classHeader?: string;
 }>();

 const classBody = computed(() => {
     if(props.open){
         return 'accordion-collapse collapse show'
     }else {
         return 'accordion-collapse collapse'
     }
 });

 const classHeader = computed(() => {
     if(props.open){
         return 'accordion-button'
     }else {
         return 'accordion-button collapsed'
     }
 });

</script>

<template>

    <div class="accordion-item card">

            <div :class="cn('accordion-header card-header d-flex align-items-center justify-content-between',props.classHeader)"  :id="'heading'+id" >
                <h5 class="mb-0 w-100 pointer-event title" data-bs-toggle="collapse" :data-bs-target="'#collapse'+id" :aria-expanded="open" :aria-controls="'collapse'+id" >
                    {{title}}
                </h5>
                <div class="d-flex align-items-center justify-content-end w-100 px-2">
                    <slot name="header"></slot>
                </div>
                <button style="width: fit-content" :class="classHeader" type="button" data-bs-toggle="collapse" :data-bs-target="'#collapse'+id" :aria-expanded="open" :aria-controls="'collapse'+id">
                </button>
            </div>

            <div :id="'collapse'+id" :class="classBody" :aria-labelledby="'heading'+id" :data-bs-parent="'#'+parentId" style="">
                <div class="accordion-body card-body">
                    <slot></slot>
                </div>
            </div>

    </div>

</template>

<style scoped>

 h5.title {
     cursor: pointer;
 }

</style>
