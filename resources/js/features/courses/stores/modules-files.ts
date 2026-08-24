import Toast from '@/composables/toast';
import { Client } from '@/lib/client';
import { Module, type ModuleFile } from '@/types/project';
import { ref } from 'vue';
import * as Modules from './modules';

export const open = ref(false);
export const type = ref('create');
export const moduleFile = ref<ModuleFile | null>(null);
export const module_id = ref<string | number>('');

export const openSheetCreate = (_module_id: string | number) => {
    type.value = 'create';
    module_id.value = _module_id;
    openSheet(true);
};

export const openSheetUpdate = (_moduleFile: ModuleFile) => {
    type.value = 'update';
    moduleFile.value = _moduleFile;
    module_id.value = _moduleFile.module_id;
    // small timeout to ensure reactive propagation and avoid missed opens
    setTimeout(() => {
        openSheet(true);
        open.value = true;
    }, 50);
};

export const openSheet = (value: boolean = true) => {
    open.value = value;
    if (!value) {
        // limpiar datos al cerrar para evitar estados residuales
        moduleFile.value = null;
        module_id.value = '';
    }
};

export const onSave = (moduleFiles: ModuleFile) => {
    const index = Modules.getIndex(moduleFiles.module_id);
    if (index > -1) {
        if (!Modules.modules.value[index].files) {
            Modules.modules.value[index].files = [];
        }
        Modules.modules.value[index].files.push(moduleFiles);
        Modules.modules.value[index] = { ...Modules.modules.value[index] };
    }
};

export const onUpdate = (moduleFiles: ModuleFile) => {
    const index = Modules.getIndex(moduleFiles.module_id);
    if (index > -1) {
        const indexFile = getIndex(Modules.modules.value[index], moduleFiles.id);
        if (indexFile !== undefined && indexFile > -1 && Modules.modules.value[index].files) {
            Modules.modules.value[index].files[indexFile] = moduleFiles;
            Modules.modules.value[index] = { ...Modules.modules.value[index] };
        }
    }
};

export const onDelete = (moduleFile: ModuleFile) => {
    const index = Modules.getIndex(moduleFile.module_id);
    if (index > -1) {
        const indexFile = getIndex(Modules.modules.value[index], moduleFile.id);
        if (indexFile !== undefined && indexFile > -1 && Modules.modules.value[index].files) {
            Modules.modules.value[index].files.splice(indexFile, 1);
            Modules.modules.value[index] = { ...Modules.modules.value[index] };
        }
    }
};

export const getIndex = (module: Module, module_file_id: string | number) => {
    return module.files?.findIndex((m) => m.id == module_file_id);
};

export const openDeleteModuleFile = ref(false);
export const deleting = ref(false);

let moduleFileDelete: ModuleFile | null = null;
export const deleteModuleFile = (moduleFile: ModuleFile) => {
    moduleFileDelete = moduleFile;
    openModalDelete();
};

export const openModalDelete = (open: boolean = true) => {
    if (deleting.value) return;
    openDeleteModuleFile.value = open;
};

export const deleteConfirm = async () => {
    if (!moduleFileDelete) return;
    deleting.value = true;
    try {
        await Client.delete(Client.ADMIN_MODULES_FILES + `/${moduleFileDelete.id}`);
        onDelete(moduleFileDelete);
        openDeleteModuleFile.value = false;
        Toast.success('Archivo de módulo eliminado con exito');
        moduleFileDelete = null;
    } catch {
        Toast.error('Error al eliminar el archivo de módulo');
    }

    deleting.value = false;
};
