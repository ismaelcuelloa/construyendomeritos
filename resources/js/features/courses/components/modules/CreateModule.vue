<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input, InputError } from '@/components/ui/input';
import { Modal } from '@/components/ui/modal';
import TextArea from '@/components/ui/text-area/TextArea.vue';
import { Client } from '@/lib/client';
import { Module } from '@/types/project';
import { computed, nextTick, ref, watch } from 'vue';

const Types = {
    create: 'create',
    edit: 'update',
};

const emit = defineEmits<{
    (e: 'onSave', module: Module): void;
    (e: 'onUpdate', module: Module): void;
    (e: 'update:open', value: boolean): void;
}>();

const props = defineProps<{
    course_id: string | number;
    open?: boolean;
    module?: Module | null;
    type?: 'create' | 'update';
}>();

const type = ref(props.type ?? Types.create);
const isOpen = ref(props.open || false);

watch(
    () => props.open,
    async (newValue) => {
        console.debug('CreateModule: props.open changed ->', newValue);
        isOpen.value = newValue ?? false;
        if (newValue) {
            await nextTick();
            console.debug('CreateModule: calling onOpen after nextTick, props.module=', props.module);
            onOpen();
        }
    },
);

// Si la prop `module` cambia mientras el modal está abierto, recargar los campos
watch(
    () => props.module,
    (newModule) => {
        console.debug('CreateModule: props.module changed ->', newModule);
        if (isOpen.value && newModule) {
            onOpen();
        }
    },
    { deep: true },
);

// Sincronizar cambios de tipo desde la prop
watch(
    () => props.type,
    (newType) => {
        type.value = newType ?? Types.create;
    },
);

watch(
    () => isOpen.value,
    (newValue) => {
        emit('update:open', newValue);
    },
);

const close = () => {
    emit('update:open', false);
};

const saving = ref(false);
const tittle = ref('');
const description = ref('');
const pdfFiles = ref<File[]>([]);

const handleFileInput = (e: Event) => {
    const target = e.target as HTMLInputElement;

    if (target.files && target.files.length > 0) {
        const files = Array.from(target.files);

        // Validar cada archivo
        for (const file of files) {
            if (!file.type.match('application/pdf')) {
                description_pdf.value = 'Solo se permiten archivos PDF';
                target.value = '';
                return;
            }
            // Check file size (10MB limit)
            if (file.size > 10 * 1024 * 1024) {
                description_pdf.value = 'Cada archivo no debe superar los 10MB';
                target.value = '';
                return;
            }
        }

        pdfFiles.value = files;
        description_pdf.value = ''; // Clear any previous errors
    } else {
        pdfFiles.value = [];
    }
};

const description_title = ref('');
const description_description = ref('');
const description_pdf = ref('');

const save = async () => {
    if (validate()) {
        saving.value = true;

        try {
            const formData = new FormData();
            formData.append('course_id', props.course_id.toString());
            formData.append('title', tittle.value);
            formData.append('description', description.value);

            // Agregar múltiples archivos PDF
            pdfFiles.value.forEach((file, index) => {
                formData.append(`pdf_files[${index}]`, file);
            });

            if (type.value === Types.edit && props.module?.id) {
                formData.append('_method', 'PUT');
            }

            const response = await sendRequest(formData);

            if (type.value === Types.create) {
                emit('onSave', response.data.module);
            } else {
                emit('onUpdate', response.data.module);
            }

            close();
            reset();
        } catch (e) {
            console.error('Error al guardar módulo:', e);
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

    // Los archivos PDF son opcionales ahora
    if (pdfFiles.value.length > 0) {
        for (const file of pdfFiles.value) {
            if (!file.type.match('application/pdf')) {
                description_pdf.value = 'Solo se permiten archivos PDF';
                validate = false;
                break;
            }
        }
    }

    return validate;
};

const reset = () => {
    if (!saving.value) {
        resetFields();
        resetDescriptionsFields();
    }
};

const sendRequest = async (formData: FormData) => {
    const config = {
        headers: {
            'Content-Type': 'multipart/form-data',
        },
    };

    if (type.value === Types.edit) {
        return await Client.post(Client.ADMIN_MODULES + '/' + props.module?.id, formData, config);
    } else {
        return await Client.post(Client.ADMIN_MODULES, formData, config);
    }
};

const resetFields = () => {
    tittle.value = '';
    description.value = '';
    pdfFiles.value = [];
    // Reset file input element
    const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement;
    if (fileInput) fileInput.value = '';
};

const resetDescriptionsFields = () => {
    description_title.value = '';
    description_description.value = '';
    description_pdf.value = '';
};

const onOpen = () => {
    reset();
    if (type.value === Types.edit) {
        tittle.value = props.module?.title ?? '';
        description.value = props.module?.description ?? '';
        // Note: File upload will be handled separately for edit mode
    }
};

const sheetTitle = computed(() => {
    return type.value == Types.create ? 'Nuevo Módulo' : 'Editar Módulo';
});
</script>

<template>
    <Modal :show="isOpen" @update:show="(val) => (isOpen = val)" :title="sheetTitle" size="xl">
        <div class="create-module-modal-body">
            <Input :disabled="saving" title="Titulo" v-model="tittle" class="mb-4">
                <template v-if="description_title.trim() != ''" #description>
                    <InputError :text="description_title" />
                </template>
            </Input>

            <TextArea :disabled="saving" title="Descripcion" v-model="description" class="mb-4">
                <template #description>
                    <small class="info-text-modal d-block mt-2">
                        <i class="feather-info"></i>
                        Se permite solo texto sin formato, no emojis. Este campo se utiliza para búsquedas, así que por favor, sea descriptivo.
                    </small>
                </template>
            </TextArea>

            <!-- Archivos PDF existentes -->
            <div v-if="type === Types.edit && props.module && props.module.files && props.module.files.length > 0" class="mb-4">
                <label class="label-form">Archivos PDF existentes</label>
                <ul class="list-group mb-2">
                    <li v-for="file in props.module.files" :key="file.id" class="d-flex align-items-center mb-1">
                        <a :href="file.file?.url || '#'" target="_blank" rel="noopener" class="me-2">
                            <i class="feather-file-text"></i>
                            {{ file.file?.name || file.title || 'Archivo PDF' }}
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Subida de nuevos archivos PDF -->
            <Input
                type="file"
                accept=".pdf,application/pdf"
                multiple
                :disabled="saving"
                title="Archivos PDF del Módulo"
                @input="handleFileInput"
                class="mb-4"
            >
                <template v-if="description_pdf.trim() != ''" #description>
                    <InputError :text="description_pdf" />
                </template>
                <template v-else #description>
                    <small class="info-text-modal d-block mt-2">
                        <i class="feather-upload"></i>
                        Seleccione uno o más archivos PDF del módulo (máximo 10MB cada uno).
                        <br />
                        <span v-if="pdfFiles.length > 0" class="text-success">
                            <i class="feather-check"></i>
                            {{ pdfFiles.length }} archivo{{ pdfFiles.length > 1 ? 's' : '' }} seleccionado{{ pdfFiles.length > 1 ? 's' : '' }}
                        </span>
                    </small>
                </template>
            </Input>
        </div>

        <template #footer>
            <div class="footer-buttons-modal">
                <Button :disabled="saving" @click="close" variant="outline" size="sm" class="btn-cancel-modal">
                    <i class="feather-x"></i> Cancelar
                </Button>
                <Button :loading="saving" @click="save" size="sm" class="btn-save-modal"> <i class="feather-save"></i> Guardar </Button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>
.create-module-modal-body {
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
