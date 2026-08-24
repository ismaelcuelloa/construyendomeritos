import { cva, type VariantProps } from 'class-variance-authority'

export { default as Button } from './Button.vue'

export const buttonVariants = cva(
  'inline-flex items-center justify-center font-semibold transition-all duration-300 cursor-pointer relative overflow-hidden',
  {
    variants: {
      variant: {
        default:
          'bg-gradient-to-r from-[#133a54] to-[#1a5a80] text-[#151515] shadow-lg hover:shadow-xl hover:-translate-y-1 active:translate-y-0 active:shadow-md rounded-lg font-bold letter-spacing-0.5',
        outline:
          'border-2 border-[#133a54] text-[#133a54] bg-transparent hover:bg-[#133a54] hover:text-[#151515] rounded-lg transition-all duration-300',
        secondary:
          'bg-[#1a5a80] text-[#151515] shadow-md hover:shadow-lg hover:-translate-y-1 rounded-lg font-semibold',
        ghost:
          'text-[#133a54] hover:bg-[#133a54] hover:bg-opacity-10 rounded-lg',
        link: 'text-[#133a54] underline-offset-4 hover:underline font-semibold',
        icon: 'h-10 w-10 rounded-full bg-gradient-to-r from-[#133a54] to-[#1a5a80] text-[#151515] shadow-md hover:shadow-lg hover:-translate-y-0.5',
      },
      size: {
        default: 'px-6 py-2.5 text-sm',
        sm: 'px-3 py-1.5 text-xs',
        md: 'px-5 py-2 text-sm',
        lg: 'px-8 py-3.5 text-base font-bold',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'sm',
    },
  },
)

export type ButtonVariants = VariantProps<typeof buttonVariants>
