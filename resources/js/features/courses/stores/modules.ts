import Toast from '@/composables/toast';
import { Client } from '@/lib/client';
import { Course, type Module } from '@/types/project';
import { ref } from 'vue';

export const modules = ref<Module[]>([]);
export const open = ref(false);

export const openCopy = ref(false);
export const type = ref('create');
export const module = ref<Module | null>(null);

export const moduleCopy = ref<Module | null>(null);

export const openSheetCreate = () => {
    type.value = 'create';
    module.value = null;
    openSheet(true);
};

export const openSheetUpdate = (_module: Module) => {
    type.value = 'update';
    console.debug('modules.openSheetUpdate called, module=', _module);
    module.value = _module;
    // Usar un pequeño timeout para evitar condiciones de carrera intermitentes
    setTimeout(() => {
        openSheet(true);
        // refuerzo: asegurar que quede true
        open.value = true;
    }, 50);
};

export const openSheetCopy = (_module: Module) => {
    moduleCopy.value = _module;
    sheetCopy(true);
};

export const openSheet = (value: boolean = true) => {
    console.debug('modules.openSheet called ->', value, 'module prop=', module.value);
    open.value = value;
};

export const sheetCopy = (value: boolean = true) => {
    openCopy.value = value;
};

export const onSave = (module: Module) => {
    modules.value.push(module);
};

export const onUpdate = (module: Module) => {
    const index = getIndex(module.id);
    if (index !== -1) {
        modules.value[index].title = module.title;
        modules.value[index].description = module.description;
        modules.value[index].updated_at = module.updated_at;
    }
};

export const onDelete = (module_id: string | number) => {
    const index = getIndex(module_id);
    if (index !== -1) {
        modules.value.splice(index, 1);
    }
};
export const getIndex = (module_id: string | number) => {
    return modules.value.findIndex((m) => m.id == module_id);
};

export const openDeleteModule = ref(false);
export const deleting = ref(false);

let deleteModuleId: any = null;
export const deleteModule = (id: string | number) => {
    deleteModuleId = id;
    openModalDeleteModule();
};

export const openModalDeleteModule = (open: boolean = true) => {
    if (deleting.value) return;
    openDeleteModule.value = open;
};

export const deleteModuleConfirm = async () => {
    deleting.value = true;
    try {
        await Client.delete(Client.ADMIN_MODULES + `/${deleteModuleId}`);
        onDelete(deleteModuleId);
        openDeleteModule.value = false;
        Toast.success('Modulo eliminado con exito');
        deleteModuleId = null;
    } catch {
        Toast.error('Error al eliminar el modulo');
    }

    deleting.value = false;
};

export const onSelectedCourseToCopy = async (course: Course) => {
    try {
        const params = {
            id: moduleCopy.value?.id,
            course_id: course.id,
        };

        await Client.post(`${Client.ADMIN_MODULES}/copy`, params);
        Toast.success('Se copio el modulo exitosamente');
    } catch {
        Toast.error('Error al intentar copiar el modulo');
    }
};
