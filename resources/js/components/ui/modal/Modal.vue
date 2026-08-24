<script setup lang="ts">
import { ref, type HTMLAttributes } from 'vue';
import { type ModalVariants, modalVariants } from '.'
import { cn } from '@/lib/utils';


interface Props  {
    show: boolean,
    variant?: ModalVariants['variant'],
    size?: ModalVariants['size'],
    align?: ModalVariants['align'],
    class?: HTMLAttributes['class'],
    title?: string,
    static?: boolean,
    canClose?: boolean,
}

const props = withDefaults(defineProps<Props>(), {
    static:  false,
    canClose: true,
});

const emit = defineEmits<{
    'update:show': [value: boolean]
    'close': []
}>();

const canClose = ref(props.canClose);

const closeModal = () => {
    emit('update:show', false);
    emit('close');
};

const closeBackdrop = () => {
    if(!props.static){
        closeModal();
    }
}


</script>

<template>
    <Transition name="modal">

            <div
                 v-if="props.show"
                 class="rbt-default-modal modal fade show"
                 tabindex="-1"
                 aria-labelledby="modalLabel"
                 aria-modal="true"
                 role="dialog"
                 style="display: block;">

                <!-- Backdrop -->
                <div  class="modal-backdrop fade show" @click="closeBackdrop"></div>

                <!-- Modal Dialog -->
                <div :class="cn('modal-dialog',modalVariants({ variant, size, align }), props.class) ">
                    <div class="modal-content">
                        <!-- Header -->
                        <div class="modal-header">
                            <h5 v-if="props.title" class="modal-title" id="modalLabel">{{ props.title }}</h5>
                            <button v-show="canClose" type="button"
                                    class="rbt-round-btn"
                                    @click="closeModal"
                                    aria-label="Close">
                                <i class="feather-x"></i>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                            <div class="inner rbt-default-form">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <slot></slot>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer pt--30">
                            <slot name="footer">

                            </slot>
                        </div>

                        <div class="top-circle-shape"></div>
                    </div>
                </div>
            </div>

    </Transition>
</template>

<style scoped>

.modal-backdrop {
    z-index: 1040;
    backdrop-filter: blur(2px);
    background: #000000b5;
    opacity: 1;
}

.modal-dialog {
    z-index: 1050;
}

/* Animaciones */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}


</style>
