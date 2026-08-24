import { cva, type VariantProps } from 'class-variance-authority'

export { default as Modal } from './Modal.vue'

export const modalVariants = cva(
  '',
  {
    variants: {
      variant: {
        default:
          '',
      },
      size: {
        default: '',
        sm: 'modal-sm',
        lg: 'modal-lg',
        xl: 'modal-xl',
        full : 'modal-fullscreen'
      },
      align : {
          default: '',
          center : 'modal-dialog-centered'
      }
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
      align : 'default'
    },
  },
)

export type ModalVariants = VariantProps<typeof modalVariants>
