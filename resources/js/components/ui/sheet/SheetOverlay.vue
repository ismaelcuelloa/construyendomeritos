<script setup lang="ts">
import { cn } from '@/lib/utils'
import { DialogOverlay, type DialogOverlayProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

const props = defineProps<DialogOverlayProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated
})
</script>

<template>
  <DialogOverlay
    data-slot="sheet-overlay"
    :class="cn('dialog-overlay', props.class)"
    v-bind="delegatedProps"
  >
    <slot />
  </DialogOverlay>
</template>

<style>
   .dialog-overlay{
       position: fixed;
       top: 0;
       width: 100%;
       height: 100%;
       z-index: 999;
       backdrop-filter: brightness(0.5) blur(1px);
   }
</style>
