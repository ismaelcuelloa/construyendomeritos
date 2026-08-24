<script setup lang="ts">
import { DialogRoot, type DialogRootEmits, type DialogRootProps, useForwardPropsEmits } from 'reka-ui'
import { SheetContent, SheetDescription, SheetFooter, SheetTitle } from '@/components/ui/sheet/index';
import { Button } from '@/components/ui/button';
import SheetOverlay from '@/components/ui/sheet/SheetOverlay.vue';
import { Separator } from '@/components/ui/separator';
import { ref, watch } from 'vue';

const props = defineProps<DialogRootProps & {
    title?: string
}>();

interface Emits extends DialogRootEmits {
    'onClose': [];
    'onOpen': [];
}

const emits = defineEmits<Emits>();

const isOpen = ref(props.open || false);

watch(() => props.open, (newValue) => {
    isOpen.value = newValue ?? false;
});


watch(isOpen, (value) => {
    emits('update:open', value);
    if (!value) {
        emits('onClose');
    } else {
        emits('onOpen');
    }
});

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <DialogRoot
    data-slot="sheet"
    v-bind="forwarded"
  >
      <SheetOverlay class="">

      </SheetOverlay>
      <SheetContent side="bottom" class="sheet position-fixed w-100 p-5 fadeIn shadow-sm">
          <SheetTitle>{{title}}</SheetTitle>
          <Separator/>
          <div class="sheet-content">
              <slot />
          </div>
          <slot name="footer" />
      </SheetContent>
  </DialogRoot>
</template>

<style>

.active-dark-mode .sheet{
    background: var(--color-darker) !important;
}

 .sheet{
     z-index: 1000;
     max-height: 100vh;
     border-radius: 30px 30px 0 0;
     background: var(--color-white) !important;
     display: flex;
     flex-direction: column;
 }

 .sheet-content{
     overflow-y: auto;
     overflow-x: hidden;
     max-height: calc(100vh - 250px);
     flex: 1;
     padding-bottom: 20px;
 }
</style>
