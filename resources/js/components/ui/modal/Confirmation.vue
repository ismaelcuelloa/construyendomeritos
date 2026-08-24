<script setup lang="ts">
import Modal from '@/components/ui/modal/Modal.vue';
import { Button } from '@/components/ui/button';
import { ref, watch } from 'vue';

interface Props  {
    show: boolean,
    message: string,
    title?: string,
    static?: boolean,
    textYes?: string,
    textNo?: string,
    loading?: boolean,
}

interface Emits {
    'yes': [];
    'no': [];
    'update:show': [value: boolean]
}

const emits = defineEmits<Emits>();

const props = withDefaults(defineProps<Props>(), {
    static: true,
    textYes: 'Sí',
    textNo: 'No',
    loading: false,
});

const show = ref(props.show);
const loading = ref(props.loading);

const handleNo = () => {
    emits('no');
    emits('update:show', false);
}

const handleYes = () => {
    emits('yes');
    emits('update:show', false);
}

watch(() => props.show, (newValue) => {
    if (show.value !== newValue) {
        show.value = newValue;
    }
});

</script>

<template>
    <Modal :show="show"
           align="center"
           :static="static"
           :title="title"
           @close="emits('update:show', false)"
           :canClose="false"
           size="md"
    >
        <div class="confirmation-content">
            <div class="confirmation-icon">
                <i class="feather-alert-triangle"></i>
            </div>
            <p class="confirmation-message">{{message}}</p>
        </div>
        <template #footer>
            <div class="confirmation-buttons">
                <Button size="sm" :disabled="loading" variant="outline" class="btn-cancel-confirmation" @click="handleNo">
                    <i class="feather-x"></i> {{textNo}}
                </Button>
                <Button size="sm" :loading="loading" @click="handleYes" class="btn-confirm-confirmation">
                    <i class="feather-check"></i> {{textYes}}
                </Button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>
.confirmation-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    padding: 10px 0 20px;
}

.confirmation-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(220, 38, 38, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pulse 2s ease-in-out infinite;
}

.confirmation-icon i {
    font-size: 32px;
    color: #dc2626;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 10px rgba(220, 38, 38, 0);
    }
}

.confirmation-message {
    font-size: 15px;
    color: #555;
    line-height: 1.7;
    margin: 0;
    text-align: center;
    max-width: 450px;
    font-weight: 500;
}

.confirmation-buttons {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    width: 100%;
}

.btn-cancel-confirmation {
    background: #ffffff !important;
    color: #666 !important;
    border: 2px solid #ddd !important;
    padding: 10px 24px !important;
    font-weight: 700 !important;
    transition: all 0.3s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    border-radius: 8px !important;
}

.btn-cancel-confirmation:hover {
    background: #f5f5f5 !important;
    border-color: #999 !important;
    color: #333 !important;
}

.btn-confirm-confirmation {
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%) !important;
    color: #ffffff !important;
    border: 2px solid #dc2626 !important;
    padding: 10px 24px !important;
    font-weight: 700 !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25) !important;
    transition: all 0.3s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    border-radius: 8px !important;
}

.btn-confirm-confirmation:hover {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    box-shadow: 0 6px 16px rgba(220, 38, 38, 0.35) !important;
    transform: translateY(-2px);
}

.btn-confirm-confirmation i,
.btn-cancel-confirmation i {
    font-size: 16px;
}
</style>