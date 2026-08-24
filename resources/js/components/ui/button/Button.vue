<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Primitive, type PrimitiveProps } from 'reka-ui'
import { type ButtonVariants, buttonVariants } from '.'

interface Props extends PrimitiveProps {
  variant?: ButtonVariants['variant']
  size?: ButtonVariants['size']
  class?: HTMLAttributes['class']
  loading?: boolean
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
})
</script>

<template>
  <Primitive
    data-slot="button"
    :as="as"
    :as-child="asChild"
    :class="cn(buttonVariants({ variant, size }), props.class)"
    :disabled="loading||disabled"
  >
    <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
    <slot />
  </Primitive>
</template>

<style scoped>
/* Botón Premium - Estilos Base */
[data-slot="button"] {
  position: relative;
  overflow: visible;
  border: none !important;
}

/* Gradiente para botones default */
[data-slot="button"]:not([class*="outline"]):not([class*="link"]):not([class*="ghost"]) {
  background: linear-gradient(135deg, #133a54 0%, #0a2135 100%) !important;
  color: #ffffff !important;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  border: none !important;
}

/* Efecto shimmer premium para botones default */
[data-slot="button"]:not([class*="outline"]):not([class*="link"]):not([class*="ghost"])::before {
  content: "";
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.4),
    transparent
  );
  transition: left 0.6s ease;
  z-index: 1;
  border-radius: inherit;
  pointer-events: none;
}

[data-slot="button"]:not([class*="outline"]):not([class*="link"]):not([class*="ghost"]):hover::before {
  left: 100%;
}

/* Animación del icono */
[data-slot="button"] i {
  transition: transform 0.3s cubic-bezier(0.23, 1, 0.32, 1);
  position: relative;
  z-index: 2;
}

[data-slot="button"]:hover i {
  transform: translateX(4px);
}

/* Focus visible para accesibilidad */
[data-slot="button"]:focus-visible {
  outline: 2px solid #133a54;
  outline-offset: 2px;
}

/* Estado disabled */
[data-slot="button"]:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

/* Slot content con z-index para que aparezca encima del shimmer */
[data-slot="button"] ::slotted(*) {
  position: relative;
  z-index: 2;
}
</style>
