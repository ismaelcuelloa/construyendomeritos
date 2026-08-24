<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input, InputError } from '@/components/ui/input';
import { Modal } from '@/components/ui/modal';
import Toast from '@/composables/toast';
import { Client } from '@/lib/client';
import * as Modules from '@/features/courses/stores/modules';
import { ModuleFile } from '@/types/project';
import axios from 'axios';
import { computed, nextTick, ref, watch } from 'vue';

const Types = {
    create: 'create',
    edit: 'update',
};

const emit = defineEmits<{
    (e: 'onSave', module: ModuleFile): void;
    (e: 'onUpdate', module: ModuleFile): void;
    (e: 'update:open', value: boolean): void;
}>();

const props = defineProps<{
    module_id: string | number;
    open?: boolean;
    moduleFile?: ModuleFile | null;
    type?: 'create' | 'update';
}>();

const type = ref(props.type ?? Types.create);

// Mantener sincronizado el tipo si cambia desde el padre
watch(
    () => props.type,
    (v) => {
        type.value = v ?? Types.create;
    },
);
const isOpen = ref(props.open || false);

watch(
    () => props.open,
    (newValue) => {
        isOpen.value = newValue ?? false;
        if (newValue) {
            // Ensure fields load when opened
            nextTick().then(() => onOpen());
        }
    },
);

// Keep parent in sync when internal isOpen changes
watch(
    () => isOpen.value,
    (val) => {
        emit('update:open', val);
    },
);

const close = () => {
    emit('update:open', false);
};

const saving = ref(false);
const file = ref<File | null>(null);
const handleFileInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        if (!target.files[0].type.match('application/pdf')) {
            description_file.value = 'Solo se permiten archivos PDF';
            target.value = '';
            return;
        }
        file.value = target.files[0];
    } else {
        file.value = null;
    }
};

const description_file = ref('');

const save = async () => {
    if (validate()) {
        saving.value = true;

        try {
            console.debug('CreateModuleFile.save: preparing to send', { module_id: props.module_id, file: file.value?.name });
            // Inspect FormData contents for debugging
            const fd = new FormData();
            fd.append('module_id', props.module_id?.toString() ?? '');
            // Backend requires a title — derive from file name if not provided
            const derivedTitle =
                props.moduleFile && props.moduleFile.title ? props.moduleFile.title : file.value ? file.value.name.replace(/\.[^/.]+$/, '') : '';
            if (derivedTitle) fd.append('title', derivedTitle);
            if (file.value) fd.append('file', file.value);
            for (const pair of fd.entries()) {
                console.debug('FormData entry:', pair[0], pair[1]);
            }

            // Use axios directly so it sets the multipart boundary header automatically
            const url =
                type.value === Types.edit
                    ? Client.getEndpoint(Client.ADMIN_MODULES_FILES + '/' + (props.moduleFile?.id ?? ''))
                    : Client.getEndpoint(Client.ADMIN_MODULES_FILES);

            const response = await axios.post(url, fd);
            console.debug('CreateModuleFile.save response:', response?.data);

            // Normalize server response to find the created/updated module file
            const moduleFileResponse =
                response?.data?.module_file || response?.data?.moduleFile || response?.data?.data?.module_file || response?.data;
            console.debug('CreateModuleFile.normalized moduleFile:', moduleFileResponse);

            if (type.value === Types.create) {
                if (moduleFileResponse) {
                    emit('onSave', moduleFileResponse);
                    Toast.success('Archivo subido con éxito');
                    close();
                    reset();
                } else {
                    console.error('CreateModuleFile.save: no se recibió module_file en la respuesta', response?.data);
                }
            } else {
                if (moduleFileResponse) {
                    emit('onUpdate', moduleFileResponse);
                    Toast.success('Archivo actualizado con éxito');
                    close();
                    reset();
                } else {
                    console.error('CreateModuleFile.save: no se recibió module_file en la respuesta', response?.data);
                }
            }
        } catch (e) {
            console.error('CreateModuleFile.save error:', e);
            const resp = (e as any)?.response;
            if (resp && resp.status === 422) {
                // Mostrar errores de validación si vienen del backend
                const errors = resp.data?.errors || resp.data;
                console.debug('CreateModuleFile.validation errors:', errors);
                console.error('CreateModuleFile.validation full response:', resp.data);
                try {
                    console.error('CreateModuleFile.validation pretty:', JSON.stringify(resp.data, null, 2));
                } catch (err) {
                    console.error('Could not stringify resp.data', err);
                }
                // also expose entire response for user debugging (compact)
                description_file.value = resp.data?.message ?? JSON.stringify(resp.data);
                if (errors?.file && Array.isArray(errors.file)) {
                    description_file.value = errors.file[0];
                } else if (typeof errors === 'string') {
                    description_file.value = errors;
                } else if (resp.data?.message) {
                    description_file.value = resp.data.message;
                }
                Toast.error('Error de validación al subir el archivo');
            } else {
                Toast.error('Error al subir el archivo');
            }
        }

        saving.value = false;
    }
};

const validate = () => {
    let validate = true;
    resetDescriptionsFields();

    if (!file.value && type.value === Types.create) {
        description_file.value = 'El archivo es requerido';
        validate = false;
    } else if (file.value && !file.value.type.match('application/pdf')) {
        description_file.value = 'Solo se permiten archivos PDF';
        validate = false;
    }

    return validate;
};

const reset = () => {
    if (!saving.value) {
        resetFields();
        resetDescriptionsFields();
    }
};

/* sendRequest removed — save() uses axios.post directly to upload FormData */

const resetFields = () => {
    file.value = null;
};
const resetDescriptionsFields = () => {
    description_file.value = '';
};

const onOpen = () => {
    reset();
    // No title/description for module files; existing file info is shown via props.moduleFile
};

const currentModule = computed(() => {
    const id = props.module_id as string | number;
    if (!id) return null;
    return Modules.modules.value.find((m) => m.id == id) ?? null;
});

const sheetTitle = computed(() => {
    return type.value == Types.create ? 'Nuevo Archivo de Módulo' : 'Editar Archivo de Módulo';
});
</script>

<template>
    <Modal :show="isOpen" @update:show="(val) => (isOpen = val)" :title="sheetTitle" size="xl">
        <div class="create-module-modal-body">
            <!-- Removed Title and Description fields: only PDF replacement is needed -->

            <div v-if="currentModule" class="mb-2">
                <label class="label-form">Módulo</label>
                <div class="mb-2">
                    <strong>{{ (currentModule as any).title || '—' }}</strong>
                </div>
            </div>

            <div v-if="moduleFile && moduleFile.file && type === Types.edit" class="mb-4">
                <label class="label-form">Archivo PDF existente</label>
                <div>
                    <a :href="moduleFile.file.url" target="_blank" rel="noopener"> <i class="feather-file-text"></i> {{ moduleFile.file.name }} </a>
                </div>
            </div>

            <Input type="file" accept=".pdf,application/pdf" :disabled="saving" title="Archivo PDF" @change="handleFileInput" class="mb-4">
                <template v-if="description_file.trim() != ''" #description>
                    <InputError :text="description_file" />
                </template>
                <template v-else #description>
                    <small class="info-text-modal d-block mt-2">
                        <i class="feather-upload"></i>
                        Seleccione un archivo PDF (máximo 10MB).
                        <br />
                        <span v-if="file" class="text-success">
                            <i class="feather-check"></i>
                            {{ file.name }} seleccionado
                        </span>
                        <span v-else class="text-muted"> No se eligió ningún archivo </span>
                    </small>
                </template>
            </Input>
        </div>

        <template #footer>
            <div class="footer-buttons-modal">
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

.btn-save-modal i {
    font-size: 16px;
}
</style>
