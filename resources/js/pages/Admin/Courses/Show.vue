<script setup lang="ts">
import { Accordion, AccordionItem } from '@/components/ui/accordion';
import Link from '@/components/ui/link/Link.vue';
import AppAdminLayout from '@/layouts/AppAdminLayout.vue';
import * as Modules from '@/features/courses/stores/modules';
import * as ModuleFile from '@/features/courses/stores/modules-files';

import CreateModule from '@/features/courses/components/modules/CreateModule.vue';
import CreateModuleFile from '@/features/courses/components/modules/files/CreateModuleFile.vue';
import ExamManager from '@/features/courses/components/modules/ExamManager.vue';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

import Modal from '@/components/ui/modal/Modal.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input, InputError } from '@/components/ui/input';
import { LabelForm } from '@/components/ui/label';
import Confirmation from '@/components/ui/modal/Confirmation.vue';
import SelectCategories from '@/features/catalog/components/selects/SelectCategories.vue';
import SelectSubcategories from '@/features/catalog/components/selects/SelectSubcategories.vue';
import TextArea from '@/components/ui/text-area/TextArea.vue';
import Toast from '@/composables/toast';
import { isSuperUser } from '@/composables/useUser';
import { Client } from '@/lib/client';
import type { Course } from '@/types/project';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps<{
    course: Course;
}>();

const saving = ref(false);
const title = ref(props.course.title);
const description = ref(props.course.description);
const price = ref<number | string>(props.course.price);
console.log(props.course.published);
const published = ref(props.course.published);
const category = ref(props.course.category_id);
const subcategory = ref(props.course.subcategory_id);
const showSubcategorySelector = ref(props.course.category?.enable_subcategories ?? false);

const description_title = ref('');
const description_description = ref('');
const description_price = ref('');
const description_category = ref('');

const editor = ref<any>(null);
const image_default = '/assets/images/others/thumbnail-placeholder.svg';
const image = ref(props.course.metadata?.banner ? '/' + props.course.metadata.banner : image_default);
const file = ref<File | null>(null);
const filterOptionsFlat = computed(() => {
    const raw = props.course.category?.custom_filter_options ?? [];
    return raw.map((group: any) => ({
        label: group.label,
        value: group.label,
    }));
});

const resolveInitialFilterValues = (): string[] => {
    const current = props.course.metadata?.custom_filter_value ?? '';
    if (!current) return [];
    const raw = props.course.category?.custom_filter_options ?? [];
    const selected: string[] = [];
    const parts = current.split('||');
    for (const part of parts) {
        const trimmed = part.trim();
        if (!trimmed) continue;
        for (const group of raw) {
            const values = group.values ?? (group.value !== undefined ? [group.value] : []);
            if (values.includes(trimmed) || group.label === trimmed) {
                if (!selected.includes(group.label)) {
                    selected.push(group.label);
                }
                break;
            }
        }
        if (!selected.includes(trimmed)) {
            selected.push(trimmed);
        }
    }
    return selected;
};

const customFilterValues = ref<string[]>(resolveInitialFilterValues());

const toggleFilterValue = (label: string) => {
    const idx = customFilterValues.value.indexOf(label);
    if (idx === -1) {
        customFilterValues.value.push(label);
    } else {
        customFilterValues.value.splice(idx, 1);
    }
};

const price_formatted = computed(() => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(price.value));
});

const handleFileInput = (e: Event) => {
    const target = e.target as HTMLInputElement;

    if (target.files && target.files[0]) {
        if (!target.files[0].type.match('image.*')) {
            //description_file.value = 'Solo se permiten archivos de Imagen';
            target.value = '';
            return;
        }
        file.value = target.files[0];
    } else {
        file.value = null;
    }
};

const save = async () => {
    if (validate()) {
        saving.value = true;

        try {
            const params = {
                title: title.value,
                description: description.value,
                price: price.value,
                published: published.value,
                category_id: category.value,
                subcategory_id: subcategory.value || null,
            };

            await Client.put(`${Client.ADMIN_COURSES}/${props.course.id}`, params);
            Toast.success('Curso actualizado con exito');
        } catch (e: any) {
            Toast.error(e?.response?.data?.message || 'Error al actualizar el curso');
            console.log(e);
        }

        saving.value = false;
    }
};

const saveMoreInfo = async () => {
    saving.value = true;
    try {
        const formData = new FormData();

        formData.append('description', editor.value?.getHTML());
        formData.append('custom_filter_value', customFilterValues.value.join('||'));

        if (file.value) {
            formData.append('file', file.value);
        }

        await Client.post(`${Client.ADMIN_COURSES}/${props.course.id}/metadata`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        Toast.success('Curso actualizado con exito');
    } catch (e: any) {
        Toast.error(e?.response?.data?.message || 'Error al actualizar el curso');
        console.log(e);
    }

    saving.value = false;
};

const validate = () => {
    let validate = true;
    resetDescriptionsFields();
    if (title.value.trim() == '') {
        description_title.value = 'El campo es requerido';
        validate = false;
    }

    if (!price.value || price.value === '' || price.value === 0) {
        description_price.value = 'El campo es requerido';
        validate = false;
    }

    return validate;
};

const resetDescriptionsFields = () => {
    description_title.value = '';
    description_description.value = '';
    description_price.value = '';
    description_category.value = '';
};

const goToCourse = (slug: string) => {
    return `/cursos/${slug}`;
};

const initEditor = () => {
    editor.value?.setHTML(props.course.metadata?.description ?? '');
};

let sortableInstance: any = null;

const initSortable = () => {
    nextTick(() => {
        const el = document.querySelector('.modules-sortable');
        if (!el) return;

        if (sortableInstance) {
            sortableInstance.destroy();
        }

        sortableInstance = (window as any).Sortable?.create(el, {
            handle: '.drag-handle',
            animation: 200,
            ghostClass: 'sortable-ghost',
            onEnd: () => {
                const items = el.querySelectorAll('[data-module-id]');
                const orders: { id: number; order_no: number }[] = [];
                items.forEach((item: any, index: number) => {
                    orders.push({
                        id: parseInt(item.dataset.moduleId),
                        order_no: index,
                    });
                });
                saveModuleOrder(orders);
            },
        });
    });
};

const saveModuleOrder = async (orders: { id: number; order_no: number }[]) => {
    try {
        await Client.post(Client.ADMIN_MODULES + '/reorder', { orders });
    } catch {
        Toast.error('Error al guardar el orden');
    }
};

onMounted(() => {});

watch(
    props.course,
    (value: any) => {
        Modules.modules.value = value.modules;
        initSortable();
    },
    { deep: true, immediate: true },
);

watch(Modules.modules, () => {
    initSortable();
}, { deep: true });

watch(file, (value: any) => {
    image.value = URL.createObjectURL(value);
});

watch(category, async (newCategoryId) => {
    subcategory.value = null;
    if (newCategoryId) {
        try {
            const response = await Client.get(`${Client.ADMIN_CATEGORIES}/${newCategoryId}`);
            showSubcategorySelector.value = response.data?.category?.enable_subcategories ?? false;
        } catch {
            showSubcategorySelector.value = false;
        }
    } else {
        showSubcategorySelector.value = false;
    }
});

const showCopyModuleModal = ref(false);
const targetCategoryId = ref<number | null>(null);
const targetCourseId = ref<number | null>(null);
const availableCourses = ref<any[]>([]);
const copyingModule = ref(false);
const loadingTargetCourses = ref(false);

const flattenCourses = (categories: any[], excludeId: number) => {
    const result: any[] = [];
    for (const cat of categories) {
        for (const course of (cat.courses || [])) {
            if (course.id !== excludeId) result.push({ ...course, _category: cat.title, _categoryId: cat.id });
        }
        for (const sub of (cat.subcategories || [])) {
            for (const course of (sub.courses || [])) {
                if (course.id !== excludeId) result.push({ ...course, _category: cat.title, _categoryId: cat.id, _subcategory: sub.title });
            }
        }
    }
    return result;
};

const handleCopyModule = async (module: any) => {
    Modules.openSheetCopy(module);
    showCopyModuleModal.value = true;
    targetCategoryId.value = null;
    targetCourseId.value = null;
    loadingTargetCourses.value = true;
    try {
        const response = await Client.post(Client.ADMIN_CATEGORIES + '/courses-tree', { exclude_user_id: '' });
        availableCourses.value = flattenCourses(response.data, props.course.id);
    } catch {
        availableCourses.value = [];
    }
    loadingTargetCourses.value = false;
};

const executeModuleCopy = async () => {
    if (!targetCourseId.value || !Modules.moduleCopy.value) return;
    copyingModule.value = true;
    try {
        await Client.post(`${Client.ADMIN_MODULES}/copy`, {
            id: Modules.moduleCopy.value.id,
            course_id: targetCourseId.value,
        });
        Toast.success('Módulo copiado exitosamente');
        showCopyModuleModal.value = false;
        targetCourseId.value = null;
    } catch (e: any) {
        console.error('Error al copiar módulo:', e?.response?.data || e);
        Toast.error(e?.response?.data?.message || 'Error al intentar copiar el módulo');
    }
    copyingModule.value = false;
};

const formatPrice = (price: number) => {
    return '$' + new Intl.NumberFormat('es-CO').format(price) + ' COP';
};

const categoryOptions = computed(() => {
    const seen = new Set<number>();
    const result: { id: number; title: string }[] = [];
    for (const course of availableCourses.value) {
        if (!seen.has(course._categoryId)) {
            seen.add(course._categoryId);
            result.push({ id: course._categoryId, title: course._category });
        }
    }
    return result;
});

const filteredCourses = computed(() => {
    if (!targetCategoryId.value) return [];
    const courses = availableCourses.value.filter(c => c._categoryId === targetCategoryId.value);
    const direct = courses.filter(c => !c._subcategory);
    const subs: Record<string, any[]> = {};
    for (const c of courses) {
        if (c._subcategory) {
            if (!subs[c._subcategory]) subs[c._subcategory] = [];
            subs[c._subcategory].push(c);
        }
    }
    const hasSubs = Object.keys(subs).length > 0;
    return { direct, subs, hasSubs };
});

watch(targetCategoryId, () => {
    targetCourseId.value = null;
});
</script>

<template>
    <AppAdminLayout title="Curso">
        <Accordion class="course-accordion-premium">
            <AccordionItem id="1" :open="true" title="Información del Curso" class="accordion-item-premium">
                <template #header>
                    <Link as="a" class="preview-link" target="_blank" :href="goToCourse(course.slug)">
                        <i class="feather-eye"></i> Vista previa
                    </Link>
                </template>
                <div class="row">
                    <div class="col-xs-12 col-md-1 col-lg-2 col-xl-2 col-12"></div>
                    <div class="col-xs-12 col-md-10 col-lg-8 col-xl-8 col-12">
                        <div class="form-section">
                            <SelectCategories v-model="category" class="mb-4">
                                <template v-if="description_category.trim() != ''" #description>
                                    <InputError :text="description_category" />
                                </template>
                            </SelectCategories>

                            <SelectSubcategories v-if="showSubcategorySelector" :category-id="category" v-model="subcategory" class="mb-4" />

                            <Input :disabled="saving" title="Titulo" v-model="title">
                                <template v-if="description_title.trim() != ''" #description>
                                    <InputError :text="description_title" />
                                </template>
                            </Input>

                            <TextArea :disabled="saving" title="Descripcion" v-model="description">
                                <template #description>
                                    <small class="info-text d-block mt-2">
                                        <i class="feather-info"></i>
                                        Se permite solo texto sin formato, no emojis. Este campo se utiliza para búsquedas, así que por favor, sea
                                        descriptivo.
                                    </small>
                                </template>
                            </TextArea>

                            <Input :disabled="saving" type="number" title="Precio" v-model="price">
                                <template v-if="description_price.trim() != '' && !price" #description>
                                    <InputError :text="description_price" />
                                </template>
                                <template v-else #description>
                                    <small class="price-formatted d-block mt-2">
                                        {{ price_formatted }}
                                    </small>
                                </template>
                            </Input>

                            <div class="checkbox-wrapper mt-4">
                                <Checkbox v-model="published" :disabled="saving" title="Publicado: " />
                            </div>

                            <div class="mt-5">
                                <Button :loading="saving" @click="save" size="sm" class="btn-save-premium">
                                    <i class="feather-save"></i> Guardar
                                </Button>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-1 col-lg-2 col-xl-2 col-12"></div>
                </div>
            </AccordionItem>

            <AccordionItem id="2" :open="true" title="Módulos del Material de Estudio" class="accordion-item-premium">
                <template v-if="isSuperUser()" #header>
                    <Button @click="Modules.openSheetCreate()" class="btn-module-premium">
                        <i class="feather-plus-square"></i>
                        <span>Nuevo Módulo</span>
                    </Button>
                </template>

                <div class="modules-container mt-4">
                    <Accordion>
                        <div class="modules-sortable">
                        <AccordionItem
                            v-for="module in Modules.modules.value"
                            :key="module.id"
                            :id="`module${module.id}`"
                            :title="module.title"
                            :data-module-id="module.id"
                            class-header="rbt-course module-item-premium"
                        >
                            <template v-if="isSuperUser()" #header>
                                <div class="module-actions">
                                    <span class="drag-handle" title="Arrastrar para reordenar">
                                        <i class="feather-move"></i>
                                    </span>
                                    <Button @click="handleCopyModule(module)" variant="icon" size="lg" class="btn-action-module" title="Copiar">
                                        <i class="feather-copy"></i>
                                    </Button>
                                    <Button
                                        @click="Modules.openSheetUpdate(module)"
                                        variant="icon"
                                        size="lg"
                                        class="btn-action-module"
                                        title="Editar"
                                    >
                                        <i class="feather-edit"></i>
                                    </Button>
                                    <Button
                                        @click="Modules.deleteModule(module.id)"
                                        variant="icon"
                                        size="lg"
                                        class="btn-action-module btn-danger-action"
                                        title="Eliminar"
                                    >
                                        <i class="feather-trash"></i>
                                    </Button>
                                </div>
                            </template>

                            <div v-for="file in module.files" :key="file.id" class="file-item-premium">
                                <a v-if="file.file?.url" :href="file.file.url" target="_blank" rel="noopener" class="file-info">
                                    <i class="feather-file"></i>
                                    <h6 class="file-title">{{ file.title }}</h6>
                                </a>
                                <div v-else class="file-info">
                                    <i class="feather-file"></i>
                                    <h6 class="file-title">{{ file.title }}</h6>
                                </div>

                                <div v-if="isSuperUser()" class="file-actions">
                                    <Button
                                        @click="ModuleFile.openSheetUpdate(file)"
                                        variant="icon"
                                        size="md"
                                        class="btn-action-file"
                                        title="Reemplazar"
                                    >
                                        <i class="feather-refresh-cw"></i>
                                    </Button>
                                    <Button
                                        @click="ModuleFile.deleteModuleFile(file)"
                                        variant="icon"
                                        size="md"
                                        class="btn-action-file btn-danger-action"
                                        title="Eliminar"
                                    >
                                        <i class="feather-trash"></i>
                                    </Button>
                                </div>
                            </div>

                            <div v-if="isSuperUser()" class="mt-4 module-bottom-actions">
                                <Button @click="ModuleFile.openSheetCreate(module.id)" class="btn-file-premium">
                                    <i class="feather-plus-square"></i>
                                    <span>Nuevo Archivo</span>
                                </Button>
                                <ExamManager :module-id="module.id" />
                            </div>
                        </AccordionItem>
                        </div>
                    </Accordion>
                </div>
            </AccordionItem>

            <AccordionItem id="3" :open="true" title="Informacion Adicional" class="accordion-item-premium">
                <div class="row">
                    <div class="col-xs-12 col-md-1 col-12"></div>
                    <div class="col-xs-12 col-md-10 col-12">
                        <div class="form-section">
                            <div class="course-field">
                                <LabelForm title="Imagen de miniatura" />
                                <div class="thumbnail-upload-premium">
                                    <div class="upload-area">
                                        <div class="brows-file-wrapper" data-black-overlay="9">
                                            <input
                                                v-if="isSuperUser()"
                                                id="createinputfile"
                                                type="file"
                                                accept="image/*"
                                                class="inputfile"
                                                @input="handleFileInput"
                                            />
                                            <img :src="image" alt="file image" class="thumbnail-preview" />
                                            <label v-if="isSuperUser()" class="upload-label" for="createinputfile" title="No File Choosen">
                                                <i class="feather-upload"></i>
                                                <span class="text-center">Elegir una Imagen</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <small class="info-text">
                                    <i class="feather-info me-2"></i>
                                    <b>Dimensiones:</b> 3/2 (720x480 píxeles), <b>Archivos Soportados:</b> JPG, JPEG, PNG
                                </small>
                            </div>

                            <div
                                v-if="course.category?.enable_custom_filter && filterOptionsFlat.length > 0"
                                class="course-field mt-4"
                            >
                                <LabelForm title="Valor del Filtro Personalizado" />
                                <div class="filter-checkboxes-premium">
                                    <div
                                        v-for="option in filterOptionsFlat"
                                        :key="option.value"
                                        class="filter-checkbox-item"
                                    >
                                        <Checkbox
                                            :model-value="customFilterValues.includes(option.value)"
                                            @update:model-value="toggleFilterValue(option.value)"
                                            :disabled="!isSuperUser()"
                                            :title="option.label"
                                        />
                                    </div>
                                </div>
                                <small class="info-text mt-2">
                                    <i class="feather-info me-2"></i>
                                    Seleccione una o varias opciones. Este valor se utilizará para filtrar este curso en la página pública.
                                </small>
                            </div>

                            <div class="course-field mt-5">
                                <LabelForm title="Descripción detallada" />
                                <div class="editor-wrapper-premium">
                                    <QuillEditor
                                        :readOnly="!isSuperUser()"
                                        ref="editor"
                                        @ready="initEditor"
                                        toolbar="full"
                                        theme="snow"
                                        contentType="html"
                                    />
                                </div>
                            </div>

                            <div v-if="isSuperUser()" class="mt-5">
                                <Button :loading="saving" @click="saveMoreInfo" size="sm" class="btn-save-premium">
                                    <i class="feather-save"></i> Guardar
                                </Button>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-1 col-12"></div>
                </div>
            </AccordionItem>
        </Accordion>
    </AppAdminLayout>
    <CreateModule
        :course_id="course.id"
        :module="Modules.module.value"
        :key="Modules.module.value?.id || 'new'"
        @update:open="Modules.openSheet"
        :open="Modules.open.value"
        @onSave="Modules.onSave"
        @onUpdate="Modules.onUpdate"
        :type="Modules.type.value as any"
    />

    <CreateModuleFile
        :module_id="ModuleFile.module_id.value"
        :module-file="ModuleFile.moduleFile.value"
        :key="ModuleFile.moduleFile.value?.id || ModuleFile.module_id.value || 'new-file'"
        @update:open="ModuleFile.openSheet"
        :open="ModuleFile.open.value"
        @onSave="ModuleFile.onSave"
        @onUpdate="ModuleFile.onUpdate"
        :type="ModuleFile.type.value as any"
    />

    <Confirmation
        :show="Modules.openDeleteModule.value"
        @update:show="Modules.openModalDeleteModule"
        title="Eliminar módulo"
        message="¿Está seguro de eliminar este módulo?"
        @yes="Modules.deleteModuleConfirm"
        :loading="Modules.deleting.value"
    />

    <Confirmation
        :show="ModuleFile.openDeleteModuleFile.value"
        @update:show="ModuleFile.openModalDelete"
        title="Eliminar archivo de módulo"
        message="¿Está seguro de eliminar este archivo de módulo?"
        @yes="ModuleFile.deleteConfirm"
        :loading="ModuleFile.deleting.value"
    />

    <Modal
        :show="showCopyModuleModal"
        @update:show="(val) => (showCopyModuleModal = val)"
        title="Copiar Módulo"
        size="md"
    >
        <div class="copy-modal-body">
            <p class="copy-modal-info">
                Vas a copiar <strong>{{ Modules.moduleCopy.value?.title }}</strong> a otro curso.
            </p>

            <div v-if="loadingTargetCourses" class="loading-hint">
                <span class="spinner-small"></span> Cargando cursos...
            </div>
            <template v-else>
                <div class="copy-module-field">
                    <label class="copy-module-label">Categoría</label>
                    <select v-model="targetCategoryId" class="copy-module-select">
                        <option :value="null" disabled>-- Selecciona una categoría --</option>
                        <option v-for="cat in categoryOptions" :key="cat.id" :value="cat.id">
                            {{ cat.title }}
                        </option>
                    </select>
                </div>

                <div class="copy-module-field">
                    <label class="copy-module-label">Curso destino</label>
                    <select v-model="targetCourseId" class="copy-module-select" :disabled="!targetCategoryId">
                        <option :value="null" disabled>-- Selecciona un curso --</option>
                        <template v-if="filteredCourses.hasSubs">
                            <option v-for="course in filteredCourses.direct" :key="course.id" :value="course.id">
                                {{ course.title }} — {{ formatPrice(course.price) }}
                            </option>
                            <optgroup v-for="(courses, sub) in filteredCourses.subs" :key="sub" :label="sub">
                                <option v-for="course in courses" :key="course.id" :value="course.id">
                                    {{ course.title }} — {{ formatPrice(course.price) }}
                                </option>
                            </optgroup>
                        </template>
                        <template v-else>
                            <option v-for="course in filteredCourses.direct" :key="course.id" :value="course.id">
                                {{ course.title }} — {{ formatPrice(course.price) }}
                            </option>
                        </template>
                    </select>
                </div>
            </template>

            <div class="copy-modal-actions">
                <button class="btn btn-cancel" @click="showCopyModuleModal = false" :disabled="copyingModule">
                    Cancelar
                </button>
                <button class="btn btn-confirm" @click="executeModuleCopy" :disabled="!targetCourseId || copyingModule">
                    <span v-if="copyingModule" class="spinner-small"></span>
                    {{ copyingModule ? 'Copiando...' : 'Copiar Módulo' }}
                </button>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
/* Accordion Premium */
.course-accordion-premium :deep(.accordion-item-premium) {
    border: 2px solid rgba(19, 58, 84, 0.15) !important;
    border-radius: 16px !important;
    margin-bottom: 20px !important;
    background: #ffffff !important;
    box-shadow: 0 4px 16px rgba(19, 58, 84, 0.08) !important;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

.course-accordion-premium :deep(.accordion-item-premium:hover) {
    border-color: rgba(19, 58, 84, 0.25) !important;
    box-shadow: 0 6px 24px rgba(19, 58, 84, 0.12) !important;
    transform: translateY(-2px);
}

.course-accordion-premium :deep(.accordion-header) {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.08) 0%, rgba(26, 90, 128, 0.04) 100%) !important;
    border-bottom: 2px solid rgba(19, 58, 84, 0.15) !important;
    padding: 24px 28px !important;
    font-size: 20px !important;
    font-weight: 800 !important;
    color: #1a1a1a !important;
    letter-spacing: -0.5px !important;
}

.course-accordion-premium :deep(.accordion-body) {
    padding: 28px !important;
    background: #ffffff !important;
}

/* Vista Previa Link */
.preview-link {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    padding: 10px 20px !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    border: 2px solid transparent !important;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.2) !important;
}

.preview-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.3) !important;
    background: linear-gradient(135deg, #1a5a80 0%, #133a54 100%) !important;
}

.preview-link i {
    font-size: 18px;
}

/* Form Section */
.form-section {
    background: rgba(19, 58, 84, 0.02);
    padding: 24px;
    border-radius: 12px;
    border: 1px solid rgba(19, 58, 84, 0.08);
}

/* Info Text */
.info-text {
    color: #666 !important;
    font-size: 13px !important;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: rgba(19, 58, 84, 0.05);
    border-radius: 8px;
    border-left: 3px solid #133a54;
}

.info-text i {
    color: #133a54;
    font-size: 16px;
}

/* Price Formatted */
.price-formatted {
    color: #133a54 !important;
    font-weight: 700 !important;
    font-size: 18px !important;
    padding: 8px 12px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(26, 90, 128, 0.05) 100%);
    border-radius: 8px;
    display: inline-block;
    border: 2px solid rgba(19, 58, 84, 0.2);
}

/* Checkbox Wrapper */
.checkbox-wrapper {
    padding: 16px;
    background: rgba(19, 58, 84, 0.03);
    border-radius: 10px;
    border: 2px solid rgba(19, 58, 84, 0.1);
}

/* Botón Guardar Premium */
.btn-save-premium {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    padding: 12px 28px !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
    border: 2px solid transparent !important;
    box-shadow: 0 4px 16px rgba(19, 58, 84, 0.25) !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.btn-save-premium:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(19, 58, 84, 0.35) !important;
    background: linear-gradient(135deg, #1a5a80 0%, #133a54 100%) !important;
}

.btn-save-premium i {
    font-size: 16px;
}

/* Botón Módulo Premium */
.btn-module-premium {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.9) 0%, rgba(52, 211, 153, 0.9) 100%) !important;
    color: #ffffff !important;
    padding: 10px 20px !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    border: 2px solid transparent !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2) !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.btn-module-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.3) !important;
    background: linear-gradient(135deg, rgba(52, 211, 153, 0.9) 0%, rgba(16, 185, 129, 0.9) 100%) !important;
}

/* Botón Archivo Premium */
.btn-file-premium {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.9) 0%, rgba(96, 165, 250, 0.9) 100%) !important;
    color: #ffffff !important;
    padding: 8px 16px !important;
    border-radius: 8px !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    border: 2px solid transparent !important;
    box-shadow: 0 3px 10px rgba(59, 130, 246, 0.2) !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
}

.btn-file-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(59, 130, 246, 0.3) !important;
    background: linear-gradient(135deg, rgba(96, 165, 250, 0.9) 0%, rgba(59, 130, 246, 0.9) 100%) !important;
}

.module-bottom-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

/* Módulos Container */
.modules-container :deep(.accordion-item) {
    border: 1px solid rgba(19, 58, 84, 0.12) !important;
    border-radius: 12px !important;
    margin-bottom: 12px !important;
    background: #ffffff !important;
}

.module-item-premium {
    transition: all 0.3s ease;
}

.module-item-premium:hover {
    border-color: rgba(19, 58, 84, 0.2) !important;
    box-shadow: 0 2px 12px rgba(19, 58, 84, 0.1) !important;
}

/* Acciones de Módulo */
.module-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.drag-handle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: grab;
    color: #a0aec0;
    transition: all 0.2s;
    user-select: none;
}

.drag-handle:hover {
    background: rgba(19, 58, 84, 0.1);
    color: #133a54;
}

.drag-handle:active {
    cursor: grabbing;
}

.drag-handle i {
    font-size: 16px;
}

.btn-action-module {
    background: rgba(19, 58, 84, 0.1) !important;
    color: #133a54 !important;
    border-radius: 8px !important;
    padding: 8px !important;
    transition: all 0.3s ease !important;
    border: 2px solid transparent !important;
}

.btn-action-module:hover {
    background: rgba(19, 58, 84, 0.2) !important;
    border-color: rgba(19, 58, 84, 0.3) !important;
    transform: scale(1.1);
}

.btn-danger-action {
    background: rgba(239, 68, 68, 0.1) !important;
    color: #dc2626 !important;
}

.btn-danger-action:hover {
    background: rgba(239, 68, 68, 0.2) !important;
    border-color: rgba(239, 68, 68, 0.3) !important;
}

/* File Item Premium */
.file-item-premium {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    margin-bottom: 12px;
    background: linear-gradient(90deg, rgba(19, 58, 84, 0.03) 0%, rgba(26, 90, 128, 0.02) 100%);
    border: 1px solid rgba(19, 58, 84, 0.1);
    border-radius: 10px;
    transition: all 0.3s ease;
}

.file-item-premium:hover {
    background: linear-gradient(90deg, rgba(19, 58, 84, 0.06) 0%, rgba(26, 90, 128, 0.04) 100%);
    border-color: rgba(19, 58, 84, 0.2);
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(19, 58, 84, 0.1);
}

.file-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.file-info i {
    font-size: 20px;
    color: #133a54;
}

.file-title {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
    color: #1a1a1a;
}

.file-actions {
    display: flex;
    gap: 8px;
}

.btn-action-file {
    background: rgba(19, 58, 84, 0.1) !important;
    color: #133a54 !important;
    border-radius: 6px !important;
    padding: 6px !important;
    transition: all 0.3s ease !important;
}

.btn-action-file:hover {
    background: rgba(19, 58, 84, 0.2) !important;
    transform: scale(1.1);
}

/* Thumbnail Upload Premium */
.thumbnail-upload-premium {
    margin: 20px 0;
}

.thumbnail-upload-premium .upload-area {
    border: 3px dashed rgba(19, 58, 84, 0.3);
    border-radius: 16px;
    padding: 20px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.02) 0%, rgba(26, 90, 128, 0.01) 100%);
    transition: all 0.3s ease;
}

.thumbnail-upload-premium .upload-area:hover {
    border-color: rgba(19, 58, 84, 0.5);
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.04) 0%, rgba(26, 90, 128, 0.02) 100%);
}

.thumbnail-upload-premium .brows-file-wrapper {
    position: relative;
    min-height: 250px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    overflow: hidden;
}

.thumbnail-upload-premium .inputfile {
    display: none;
}

.thumbnail-upload-premium .thumbnail-preview {
    width: 100%;
    height: 100%;
    object-fit: contain;
    max-height: 300px;
    border-radius: 12px;
}

.thumbnail-upload-premium .upload-label {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    cursor: pointer;
    background: rgba(0, 0, 0, 0.5);
    opacity: 0;
    transition: opacity 0.3s ease;
    color: #ffffff;
    font-weight: 700;
    font-size: 16px;
}

.thumbnail-upload-premium .brows-file-wrapper:hover .upload-label {
    opacity: 1;
}

.thumbnail-upload-premium .upload-label i {
    font-size: 40px;
}

/* Editor Wrapper Premium */
.editor-wrapper-premium {
    border: 2px solid rgba(19, 58, 84, 0.15);
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
}

.editor-wrapper-premium :deep(.ql-container.ql-snow) {
    min-height: 250px;
    font-size: 15px;
}

.editor-wrapper-premium :deep(.ql-toolbar.ql-snow) {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.05) 0%, rgba(26, 90, 128, 0.03) 100%);
    border-bottom: 2px solid rgba(19, 58, 84, 0.15) !important;
    border-top: none !important;
    border-left: none !important;
    border-right: none !important;
}

.editor-wrapper-premium :deep(.ql-editor) {
    padding: 20px;
}

.ql-container.ql-snow {
    min-height: 200px;
}

/* Copy Module Modal */
.copy-modal-body {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 8px 0;
}

.copy-modal-info {
    font-size: 15px;
    color: #4a5568;
    line-height: 1.6;
    margin: 0;
    padding: 16px 20px;
    background: rgba(19, 58, 84, 0.04);
    border-left: 4px solid #133a54;
    border-radius: 8px;
}

.copy-modal-info strong {
    color: #1a1a1a;
    font-weight: 700;
}

.copy-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 8px;
}

.btn-cancel {
    padding: 10px 24px;
    background: #f7fafc;
    color: #4a5568;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancel:hover {
    background: #edf2f7;
    border-color: #cbd5e0;
}

.btn-confirm {
    padding: 10px 24px;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.25);
}

.btn-confirm:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.35);
}

.btn-confirm:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.copy-module-label {
    display: block;
    font-weight: 700;
    font-size: 14px;
    color: #2d3748;
    margin-bottom: 6px;
}

.copy-module-field {
    display: flex;
    flex-direction: column;
    margin-bottom: 16px;
}

.copy-module-select {
    width: 100%;
    padding: 12px 40px 12px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #2d3748;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.2s;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%23f07900' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    outline: none;
}

.copy-module-select:focus {
    border-color: #133a54;
    box-shadow: none;
}

.copy-module-select:disabled {
    background: #f5f5f5;
    cursor: not-allowed;
    opacity: 0.7;
}

.copy-module-select optgroup {
    font-weight: 700;
    font-size: 13px;
    color: #133a54;
}

.copy-module-select option {
    font-weight: 600;
    color: #2d3748;
}

.loading-hint {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    font-size: 13px;
    color: #718096;
}

.loading-hint .spinner-small {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(19, 58, 84, 0.2);
    border-top-color: #133a54;
    border-radius: 50%;
    animation: spin 0.5s linear infinite;
}

.spinner-small {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.5s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Custom Filter Checkboxes Premium */
.filter-checkboxes-premium {
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: #f9fafb;
    border: 2px solid rgba(19, 58, 84, 0.15);
    border-radius: 8px;
    padding: 16px;
}

.filter-checkbox-item {
    padding: 10px 14px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.filter-checkbox-item:hover {
    border-color: #133a54;
    box-shadow: 0 2px 6px rgba(19, 58, 84, 0.08);
}

.filter-checkbox-item :deep(.checkbox-wrapper) {
    margin: 0;
}

/* Custom Filter Select Premium */
.custom-filter-input-premium {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid rgba(19, 58, 84, 0.15);
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    color: #333;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%23f07900' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 45px;
}

.custom-filter-input-premium:focus {
    border-color: #133a54;
    outline: none;
    box-shadow: 0 0 0 3px rgba(19, 58, 84, 0.1);
}

.custom-filter-input-premium:disabled {
    background: #f5f5f5;
    cursor: not-allowed;
    opacity: 0.7;
}

.custom-filter-input-premium option {
    padding: 12px;
    font-weight: 600;
}
</style>

<style>
.sortable-ghost {
    opacity: 0.4 !important;
    background: rgba(19, 58, 84, 0.05) !important;
    border: 2px dashed rgba(19, 58, 84, 0.3) !important;
    border-radius: 12px !important;
}

.modules-sortable .drag-handle {
    cursor: grab;
    user-select: none;
}
</style>
