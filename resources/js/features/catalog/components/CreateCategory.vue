<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input, InputError } from '@/components/ui/input';
import Modal from '@/components/ui/modal/Modal.vue';
import TextArea from '@/components/ui/text-area/TextArea.vue';
import { Client } from '@/lib/client';
import { ref, watch } from 'vue';

const emit = defineEmits<{
    (e: 'onSave', user: any): void;
    (e: 'update:open', value: boolean): void;
}>();

const props = defineProps<{
    open?: boolean;
}>();

const isOpen = ref(props.open || false);

watch(
    () => props.open,
    (newValue) => {
        isOpen.value = newValue ?? false;
        if (newValue) {
            reset();
        }
    },
);

const close = () => {
    emit('update:open', false);
};

const saving = ref(false);
const tittle = ref('');
const description = ref('');

const description_title = ref('');
const description_description = ref('');

const save = async () => {
    if (validate()) {
        saving.value = true;

        try {
            const params = {
                title: tittle.value,
                description: description.value,
            };
            const response = await Client.post(Client.ADMIN_CATEGORIES, params);
            emit('onSave', response.data.category);
            close();
            reset();
        } catch (e) {
            console.log(e);
        }

        saving.value = false;
    }
};

const validate = () => {
    let validate = true;
    resetDescriptionsFields();
    if (tittle.value.trim() == '') {
        description_title.value = 'El campo es requerido';
        validate = false;
    }

    return validate;
};

const reset = () => {
    console.log('reset');
    if (!saving.value) {
        resetFields();
        resetDescriptionsFields();
    }
};

const resetFields = () => {
    tittle.value = '';
    description.value = '';
};
const resetDescriptionsFields = () => {
    description_title.value = '';
    description_description.value = '';
};
</script>

<template>
    <Modal :show="isOpen" @update:show="(val) => (isOpen = val)" title="Crear Nueva Categoría" size="lg">
        <div class="create-category-modal-body">
            <Input :disabled="saving" title="Nombre de la Categoría" v-model="tittle" placeholder="Ej: Programación Web" class="mb-4">
                <template v-if="description_title.trim() != ''" #description>
                    <InputError :text="description_title" />
                </template>
            </Input>

            <TextArea :disabled="saving" title="Descripción" v-model="description" placeholder="Describe esta categoría detalladamente">
                <template #description>
                    <small class="info-text-modal d-block mt-2">
                        <i class="feather-info"></i>
                        Se permite solo texto sin formato, no emojis. Este campo se utiliza para búsquedas, así que por favor, sea descriptivo.
                    </small>
                </template>
            </TextArea>
        </div>

        <template #footer>
            <div class="footer-buttons-modal">
                <Button :disabled="saving" @click="close" variant="outline" size="sm" class="btn-cancel-modal">
                    <i class="feather-x"></i> Cancelar
                </Button>
                <Button :loading="saving" @click="save" size="sm" class="btn-save-modal"> <i class="feather-check"></i> Crear Categoría </Button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>
.create-category-modal-body {
    padding: 10px 0;
}

.info-text-modal {
    color: #666 !important;
    font-size: 13px !important;
    display: flex;
    align-items: flex-start;
    gap: 6px;
    padding: 10px 12px;
    background: rgba(19, 58, 84, 0.05);
    border-radius: 8px;
    border-left: 3px solid #133a54;
    line-height: 1.5;
}

.info-text-modal i {
    color: #133a54;
    font-size: 16px;
    margin-top: 2px;
    flex-shrink: 0;
}

.footer-buttons-modal {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    width: 100%;
}

.btn-cancel-modal {
    background: #ffffff !important;
    color: #666 !important;
    border: 2px solid #ddd !important;
    padding: 10px 20px !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    transition: all 0.3s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.btn-cancel-modal:hover {
    background: #f5f5f5 !important;
    border-color: #999 !important;
    color: #333 !important;
}

.btn-save-modal {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    border: 2px solid #133a54 !important;
    padding: 10px 24px !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.25) !important;
    transition: all 0.3s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.btn-save-modal:hover {
    background: linear-gradient(135deg, #1a5a80 0%, #133a54 100%) !important;
    box-shadow: 0 6px 16px rgba(19, 58, 84, 0.35) !important;
    transform: translateY(-2px);
}

.btn-save-modal i,
.btn-cancel-modal i {
    font-size: 16px;
}
</style>
