<script setup lang="ts">
import Link from '@/components/ui/link/Link.vue';
import AppAdminLayout from '@/layouts/AppAdminLayout.vue';
// @ts-expect-error - Vue3Datatable does not have TypeScript declarations
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import Vue3Datatable from '@bhplugin/vue3-datatable';

import { Button } from '@/components/ui/button';
import { Input, InputError } from '@/components/ui/input';
import Confirmation from '@/components/ui/modal/Confirmation.vue';
import Modal from '@/components/ui/modal/Modal.vue';
import TextArea from '@/components/ui/text-area/TextArea.vue';
import Toast from '@/composables/toast';
import { Client } from '@/lib/client';
import { onMounted, ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

import { Checkbox } from '@/components/ui/checkbox';
import { LabelForm } from '@/components/ui/label';
import { Search } from '@/components/ui/search';
import { DataTable } from '@/lib/tables';
import type { Category, Subcategory } from '@/types/project';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const props = defineProps<{
    category: Category;
}>();

const page = usePage();
const authUser = computed(() => page.props.auth?.user);
const isSuperUser = computed(() => {
    return authUser.value?.roles?.some((role: any) => role.name === 'super_user');
});

const saving = ref(false);
const title = ref(props.category.title);
const description = ref(props.category.description ?? '');
const published = ref(props.category.published);
const enableCustomFilter = ref(props.category.enable_custom_filter ?? false);
const customFilterOptions = ref<{ label: string; values: string[] }[]>(
    (props.category.custom_filter_options ?? []).map((opt: any) => {
        if (opt.value !== undefined && opt.values === undefined) {
            return { label: opt.label, values: [opt.value] };
        }
        return { label: opt.label, values: opt.values ?? [] };
    }),
);
const newFilterOption = ref({ label: '', value: '', values: [] as string[] });
const enableSubcategories = ref(props.category.enable_subcategories ?? false);

const description_title = ref('');
const description_description = ref('');

const image_default = '/assets/images/others/thumbnail-placeholder.svg';
const image = ref(props.category.image ? props.category.image.url : image_default);
const file = ref<File | null>(null);

const table = new DataTable();
let timer: number;

const showDeleteConfirmation = ref(false);
const courseToDelete = ref<string | null>(null);

// Subcategory management
const subcategoryTable = new DataTable();
let subcategoryTimer: number;
const showSubcategoryModal = ref(false);
const editingSubcategory = ref<Subcategory | null>(null);
const subcategoryForm = ref({ title: '', description: '', published: true, active: true });
const subcategoryFile = ref<File | null>(null);
const savingSubcategory = ref(false);
const showDeleteSubcategoryConfirmation = ref(false);
const subcategoryToDelete = ref<string | null>(null);
const parentSubcategories = ref<Subcategory[]>([]);

subcategoryTable.setCols([
    { field: 'title', title: 'Título' },
    { field: 'description', title: 'Descripción' },
    { field: 'published', title: 'Publicado', sort: false },
    { field: 'active', title: 'Estado', sort: false },
    { field: 'options', title: '', sort: false, width: 'fit-content' },
]);
subcategoryTable.setSort('title', 'asc');

const changeSubcategoryServer = (data: any) => {
    subcategoryTable.setParams(data);
    filterSubcategories();
};

const getSubcategories = async () => {
    try {
        subcategoryTable.loading.value = true;
        const sort: any = {};
        sort[subcategoryTable.params.sort_column] = subcategoryTable.params.sort_direction;

        const options = {
            per_page: subcategoryTable.params.pagesize,
            page: subcategoryTable.params.current_page,
            sort: JSON.stringify(sort),
            search: subcategoryTable.params.search || '',
        };

        const response = await Client.post(
            `${Client.ADMIN_SUBCATEGORIES}/${props.category.id}/subcategorias/list`,
            options,
        );
        subcategoryTable.rows.value = response.data.data;
        subcategoryTable.total_rows.value = response.data.total;
    } catch {}
    subcategoryTable.loading.value = false;
};

const filterSubcategories = () => {
    clearTimeout(subcategoryTimer);
    subcategoryTimer = setTimeout(() => getSubcategories(), 300);
};

const openSubcategoryModal = async (subcategory: Subcategory | null = null) => {
    if (subcategory) {
        editingSubcategory.value = subcategory;
        subcategoryForm.value = {
            title: subcategory.title,
            description: subcategory.description ?? '',
            published: subcategory.published,
            active: subcategory.active,
            parent_id: (subcategory as any).parent_id ?? '',
        };
    } else {
        editingSubcategory.value = null;
        subcategoryForm.value = { title: '', description: '', published: true, active: true, parent_id: '' };
    }
    subcategoryFile.value = null;

    try {
        const response = await Client.post(
            `${Client.ADMIN_SUBCATEGORIES}/${props.category.id}/subcategorias/list?per_page=500`,
            { search: '' },
        );
        parentSubcategories.value = (response.data.data || []).filter(
            (s: any) => !s.parent_id && s.id !== (editingSubcategory.value?.id ?? 0),
        );
    } catch {
        parentSubcategories.value = [];
    }

    showSubcategoryModal.value = true;
};

const saveSubcategory = async () => {
    if (!subcategoryForm.value.title.trim()) return;
    savingSubcategory.value = true;

    try {
        const formData = new FormData();
        formData.append('title', subcategoryForm.value.title);
        formData.append('description', subcategoryForm.value.description);
        formData.append('published', subcategoryForm.value.published.toString());
        formData.append('active', subcategoryForm.value.active.toString());
        if (subcategoryForm.value.parent_id) {
            formData.append('parent_id', subcategoryForm.value.parent_id.toString());
        }
        if (subcategoryFile.value) {
            formData.append('image', subcategoryFile.value);
        }

        if (editingSubcategory.value) {
            formData.append('_method', 'PUT');
            await Client.post(
                `${Client.ADMIN_SUBCATEGORIES}/${props.category.id}/subcategorias/${editingSubcategory.value.id}`,
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } },
            );
            Toast.success('Subcategoría actualizada con éxito');
        } else {
            await Client.post(
                `${Client.ADMIN_SUBCATEGORIES}/${props.category.id}/subcategorias`,
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } },
            );
            Toast.success('Subcategoría creada con éxito');
        }
        showSubcategoryModal.value = false;
        getSubcategories();
    } catch (e: any) {
        Toast.error(e.response?.data?.message || 'Error al guardar la subcategoría');
    }
    savingSubcategory.value = false;
};

const confirmDeleteSubcategory = (id: string) => {
    subcategoryToDelete.value = id;
    showDeleteSubcategoryConfirmation.value = true;
};

const executeDeleteSubcategory = async () => {
    if (!subcategoryToDelete.value) return;
    try {
        await Client.delete(
            `${Client.ADMIN_SUBCATEGORIES}/${props.category.id}/subcategorias/${subcategoryToDelete.value}`,
        );
        Toast.success('Subcategoría eliminada con éxito');
        getSubcategories();
    } catch (e: any) {
        Toast.error(e.response?.data?.message || 'Error al eliminar la subcategoría');
    }
    subcategoryToDelete.value = null;
    showDeleteSubcategoryConfirmation.value = false;
};

const handleSubcategorySearch = (value: string | number) => {
    subcategoryTable.params.search = String(value);
    subcategoryTable.params.current_page = 1;
    filterSubcategories();
};

const handleSubcategoryFile = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        if (!target.files[0].type.match('image.*')) {
            target.value = '';
            return;
        }
        subcategoryFile.value = target.files[0];
    } else {
        subcategoryFile.value = null;
    }
};

table.setCols([
    { field: 'title', title: 'Titulo' },
    { field: 'description', title: 'Descripcion' },
    { field: 'price_formatted', title: 'Precio' },
    { field: 'modules_count', title: 'Modulos', sort: false },
    { field: 'subscriptions_count', title: 'Suscripciones', sort: false },
    { field: 'published', title: 'Publicado', sort: false },
    { field: 'active', title: 'Estado', sort: false },
    { field: 'options', title: '', sort: false, width: 'fit-content' },
]);
table.setSort('order_no', 'asc');

const changeServer = (data: any) => {
    table.setParams(data);
    filterCourses();
};

const getCourses = async () => {
    try {
        table.loading.value = true;

        const sort: any = {};
        sort[table.params.sort_column] = table.params.sort_direction;

        const options = {
            per_page: table.params.pagesize,
            page: table.params.current_page,
            sort: JSON.stringify(sort),
            search: '',
            category_id: props.category.id,
        };

        if (table.params.search !== '') {
            options.search = table.params.search;
        }

        const response = await Client.post(Client.ADMIN_COURSES + '/list', options);

        table.rows.value = response.data.data;
        table.total_rows.value = response.data.total;
    } catch {}

    table.loading.value = false;
};

const filterCourses = () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        getCourses();
    }, 300);
};

const goToCurse = (id: string) => {
    return Client.ADMIN_COURSES + `/${id}`;
};

const deleteCourse = (courseId: string) => {
    courseToDelete.value = courseId;
    showDeleteConfirmation.value = true;
};

const confirmDeleteCourse = async () => {
    if (!courseToDelete.value) return;

    try {
        await Client.delete(Client.ADMIN_COURSES + `/${courseToDelete.value}`);
        Toast.success('Curso eliminado con éxito');
        getCourses();
    } catch (e: any) {
        const errorMessage = e.response?.data?.message || 'Error al eliminar el curso';
        Toast.error(errorMessage);
    }

    courseToDelete.value = null;
    showDeleteConfirmation.value = false;
};

const handleSearch = (value: string | number) => {
    table.params.search = String(value);
    table.params.current_page = 1;
    filterCourses();
};

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
            const formData = new FormData();
            formData.append('title', title.value);
            formData.append('description', description.value);
            formData.append('published', published.value.toString());
            formData.append('enable_custom_filter', enableCustomFilter.value.toString());
            formData.append('custom_filter_options', JSON.stringify(customFilterOptions.value));
            formData.append('enable_subcategories', enableSubcategories.value.toString());

            if (file.value) {
                formData.append('image', file.value);
            }

            formData.append('_method', 'PUT');
            const response = await Client.post(Client.ADMIN_CATEGORIES + '/' + props.category.id, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            console.log('Response:', response.data);

            // Actualizar la imagen con la URL del servidor si se subió una nueva
            if (file.value && response.data?.category?.image?.url) {
                image.value = response.data.category.image.url;
                file.value = null;
            }

            Toast.success('Categoría actualizado con exito');
        } catch (e) {
            Toast.error('Error al actualizar la categoría');
            console.log(e);
        }

        saving.value = false;
    }
};

const addFilterValue = () => {
    if (newFilterOption.value.label.trim() && newFilterOption.value.value.trim()) {
        newFilterOption.value.values.push(newFilterOption.value.value.trim());
        newFilterOption.value.value = '';
    }
};

const removeFilterValue = (index: number) => {
    newFilterOption.value.values.splice(index, 1);
};

const confirmFilterOption = () => {
    if (newFilterOption.value.label.trim() && newFilterOption.value.values.length > 0) {
        customFilterOptions.value.push({
            label: newFilterOption.value.label.trim(),
            values: [...newFilterOption.value.values],
        });
        newFilterOption.value = { label: '', value: '', values: [] };
    }
};

const removeFilterOption = (index: number) => {
    customFilterOptions.value.splice(index, 1);
};

const validate = () => {
    let validate = true;
    resetDescriptionsFields();

    if (title.value.trim() == '') {
        description_title.value = 'El campo es requerido';
        validate = false;
    }

    return validate;
};

const resetDescriptionsFields = () => {
    description_title.value = '';
    description_description.value = '';
};

const goToCategory = (slug: string) => {
    return `/categorias/${slug}`;
};

onMounted(() => {
    getCourses();
    if (enableSubcategories.value) {
        getSubcategories();
    }
});

watch(
    props.category,
    (value: any) => {
        console.log(value);
    },
    { deep: true, immediate: true },
);

watch(file, (value: any) => {
    if (value && value instanceof File) {
        image.value = URL.createObjectURL(value);
    }
});
</script>

<template>
    <AppAdminLayout title="Categoría">
        <!-- Información de la Categoría Card -->
        <Card class="category-card">
            <CardContent>
                <CardHeader>
                    <CardTitle class="admin-title">Información de la Categoría</CardTitle>
                    <Link as="a" class="btn-preview" target="_blank" :href="goToCategory(category.slug)">
                        <i class="feather-eye"></i> Vista previa
                    </Link>
                </CardHeader>

                <Separator class="separator-compact" />

                <div class="category-form">
                    <div class="form-row">
                        <div class="form-col">
                            <Input :disabled="saving" title="Título de la Categoría" v-model="title" placeholder="Ej: Programación Web">
                                <template v-if="description_title.trim() != ''" #description>
                                    <InputError :text="description_title" />
                                </template>
                            </Input>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <TextArea
                                :disabled="saving"
                                title="Descripción"
                                v-model="description"
                                placeholder="Describe esta categoría detalladamente"
                            >
                                <template #description>
                                    <small class="d-block mt-2"
                                        ><i class="feather-info"></i>
                                        Se permite solo texto sin formato, no emojis. Este campo se utiliza para búsquedas, así que por favor, sea
                                        descriptivo.
                                    </small>
                                </template>
                            </TextArea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-section">
                                <LabelForm title="Imagen de Miniatura" />
                                <div class="upload-wrapper">
                                    <div class="upload-preview">
                                        <img :src="image" alt="Categoría thumbnail" />
                                    </div>
                                    <div class="upload-controls">
                                        <input id="categoryImageInput" type="file" accept="image/*" class="file-input" @input="handleFileInput" />
                                        <label for="categoryImageInput" class="upload-button">
                                            <i class="feather-upload"></i>
                                            Elegir Imagen
                                        </label>
                                        <small class="upload-hint">
                                            <i class="feather-info"></i>
                                            Dimensiones: 3/2 (720x480 píxeles) | Formatos: JPG, JPEG, PNG
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="checkbox-wrapper">
                                <Checkbox v-model="published" :disabled="saving" title="Publicar Categoría" />
                                <small class="checkbox-hint">Esta categoría será visible para los usuarios</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="checkbox-wrapper">
                                <Checkbox v-model="enableCustomFilter" :disabled="saving" title="Habilitar Selector Personalizado" />
                                <small class="checkbox-hint">Permite filtrar cursos con opciones personalizadas en la página pública</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="checkbox-wrapper">
                                <Checkbox v-model="enableSubcategories" :disabled="saving" title="Habilitar Subcategorías" />
                                <small class="checkbox-hint">Muestra una página intermedia de subcategorías antes de los cursos</small>
                            </div>
                        </div>
                    </div>

                    <div v-if="enableCustomFilter" class="form-row">
                        <div class="form-col">
                            <div class="filter-options-section">
                                <LabelForm title="Opciones del Selector" />
                                <small class="d-block mb-3">
                                    <i class="feather-info"></i>
                                    Agrega opciones personalizadas para filtrar los cursos. Los cursos deben tener el valor correspondiente en su
                                    metadata.
                                </small>

                                <div class="filter-input-group mb-3">
                                    <Input
                                        v-model="newFilterOption.label"
                                        placeholder="Etiqueta (Ej: Nivel 1)"
                                        :disabled="saving"
                                        class="filter-input"
                                        :maxlength="120"
                                    />
                                    <Input
                                        v-model="newFilterOption.value"
                                        placeholder="Valor (Ej: nivel_1)"
                                        :disabled="saving || !newFilterOption.label.trim()"
                                        class="filter-input ml-2"
                                        :maxlength="80"
                                        @keyup.enter="addFilterValue"
                                    />
                                    <Button
                                        @click="addFilterValue"
                                        :disabled="saving || !newFilterOption.label.trim() || !newFilterOption.value.trim()"
                                        class="btn-add-option ml-2"
                                    >
                                        <i class="feather-plus"></i> Agregar
                                    </Button>
                                    <Button
                                        @click="confirmFilterOption"
                                        :disabled="saving || newFilterOption.values.length === 0"
                                        class="btn-confirm-option ml-2"
                                        variant="outline"
                                    >
                                        <i class="feather-check"></i> Confirmar
                                    </Button>
                                </div>

                                <div v-if="newFilterOption.values.length > 0" class="filter-pending-values mb-3">
                                    <div class="filter-pending-header">
                                        <span class="filter-pending-label">Etiqueta: <strong>{{ newFilterOption.label }}</strong></span>
                                        <span class="filter-pending-count">{{ newFilterOption.values.length }} valor(es) pendiente(s)</span>
                                    </div>
                                    <div v-for="(val, idx) in newFilterOption.values" :key="idx" class="filter-pending-item">
                                        <span class="filter-pending-value">{{ val }}</span>
                                        <Button @click="removeFilterValue(idx)" :disabled="saving" class="btn-remove-option" variant="ghost">
                                            <i class="feather-trash-2"></i>
                                        </Button>
                                    </div>
                                </div>

                                <div v-if="customFilterOptions.length > 0" class="filter-options-list">
                                    <div v-for="(option, index) in customFilterOptions" :key="index" class="filter-option-item">
                                        <div class="filter-option-content">
                                            <span class="filter-option-label">{{ option.label }}</span>
                                            <span class="filter-option-values">{{ option.values.join(', ') }}</span>
                                        </div>
                                        <Button @click="removeFilterOption(index)" :disabled="saving" class="btn-remove-option" variant="ghost">
                                            <i class="feather-trash-2"></i>
                                        </Button>
                                    </div>
                                </div>
                                <div v-else-if="!newFilterOption.label" class="filter-options-empty">
                                    <i class="feather-filter"></i>
                                    <span>No hay opciones agregadas aún</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <Button @click="save" :loading="saving" class="btn-save"> <i class="feather-check"></i> Guardar Cambios </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Cursos Asignados Card -->
        <Card class="category-card mt-6">
            <CardContent>
                <CardHeader>
                    <CardTitle class="admin-title">Cursos Asignados</CardTitle>
                </CardHeader>

                <Separator class="separator-compact" />

                <div class="search-wrapper-compact">
                    <Search placeholder="Buscar por nombre o descripción..." @search="handleSearch" />
                </div>

                <div class="table-container">
                    <vue3-datatable
                        :ref="table.table"
                        :loading="table.loading.value"
                        :rows="table.rows.value"
                        :columns="table.cols"
                        :totalRows="table.total_rows.value"
                        :isServerMode="true"
                        :page="table.params.current_page"
                        :pageSize="table.params.pagesize"
                        :showPageSize="false"
                        :sortable="true"
                        :sortColumn="table.params.sort_column"
                        :sortDirection="table.params.sort_direction"
                        :search="table.params.search"
                        :hasCheckbox="false"
                        :columnFilter="false"
                        noDataContent="No hay cursos asignados"
                        @change="changeServer"
                        skin="bh-table-striped bh-table-hover bh-table-compact table-premium"
                    >
                        <template #published="data: any">
                            <div class="d-flex justify-content-center">
                                <span v-if="data.value.published" class="rbt-badge-5 bg-color-success-opacity color-success">Publicado</span>
                                <span v-else class="rbt-badge-5 bg-color-warning-opacity color-warning">No publicado</span>
                            </div>
                        </template>

                        <template #active="data: any">
                            <div class="d-flex justify-content-center">
                                <span v-if="data.value.active" class="rbt-badge-5 bg-color-success-opacity color-success">Activo</span>
                                <span v-else class="rbt-badge-5 bg-color-danger-opacity color-danger">Inactivo</span>
                            </div>
                        </template>

                        <template #options="data: any">
                            <div class="rbt-button-group justify-content-end">
                                <Link :href="goToCurse(data.value.id)" class="btn-option btn-edit"> <i class="feather-edit-2"></i> Editar </Link>
                                <button @click="() => deleteCourse(data.value.id)" class="btn-option btn-delete" type="button">
                                    <i class="feather-trash-2"></i> Eliminar
                                </button>
                            </div>
                        </template>
                    </vue3-datatable>
                </div>
            </CardContent>
        </Card>

        <!-- Subcategorías Card (only shown when subcategories enabled) -->
        <Card v-if="enableSubcategories" class="category-card mt-6">
            <CardContent>
                <CardHeader>
                    <CardTitle class="admin-title">Subcategorías</CardTitle>
                    <Button v-if="isSuperUser" @click="openSubcategoryModal()" class="btn-create-subcategory">
                        <i class="feather-plus me-2"></i>
                        Crear Subcategoría
                    </Button>
                </CardHeader>

                <Separator class="separator-compact" />

                <div class="search-wrapper-compact">
                    <Search placeholder="Buscar por nombre o descripción..." @search="handleSubcategorySearch" />
                </div>

                <div class="table-container">
                    <vue3-datatable
                        :ref="subcategoryTable.table"
                        :loading="subcategoryTable.loading.value"
                        :rows="subcategoryTable.rows.value"
                        :columns="subcategoryTable.cols"
                        :totalRows="subcategoryTable.total_rows.value"
                        :isServerMode="true"
                        :page="subcategoryTable.params.current_page"
                        :pageSize="subcategoryTable.params.pagesize"
                        :showPageSize="false"
                        :sortable="true"
                        :sortColumn="subcategoryTable.params.sort_column"
                        :sortDirection="subcategoryTable.params.sort_direction"
                        :search="subcategoryTable.params.search"
                        :hasCheckbox="false"
                        :columnFilter="false"
                        noDataContent="No hay subcategorías creadas"
                        @change="changeSubcategoryServer"
                        skin="bh-table-striped bh-table-hover bh-table-compact table-premium"
                    >
                        <template #published="data: any">
                            <div class="d-flex justify-content-center">
                                <span v-if="data.value.published" class="rbt-badge-5 bg-color-success-opacity color-success">Publicado</span>
                                <span v-else class="rbt-badge-5 bg-color-warning-opacity color-warning">No publicado</span>
                            </div>
                        </template>

                        <template #active="data: any">
                            <div class="d-flex justify-content-center">
                                <span v-if="data.value.active" class="rbt-badge-5 bg-color-success-opacity color-success">Activo</span>
                                <span v-else class="rbt-badge-5 bg-color-danger-opacity color-danger">Inactivo</span>
                            </div>
                        </template>

                        <template #options="data: any">
                            <div class="rbt-button-group justify-content-end">
                                <button @click="openSubcategoryModal(data.value)" class="btn-option btn-edit">
                                    <i class="feather-edit-2"></i> Editar
                                </button>
                                <button @click="confirmDeleteSubcategory(data.value.id)" class="btn-option btn-delete">
                                    <i class="feather-trash-2"></i> Eliminar
                                </button>
                            </div>
                        </template>
                    </vue3-datatable>
                </div>
            </CardContent>
        </Card>

    </AppAdminLayout>
    <Confirmation
        :show="showDeleteConfirmation"
        @update:show="(val) => (showDeleteConfirmation = val)"
        @yes="confirmDeleteCourse"
        title="Eliminar Curso"
        message="¿Está seguro de que desea eliminar este curso? Esta acción eliminará permanentemente el curso y toda su información asociada."
        textYes="Sí, eliminar"
        textNo="Cancelar"
    />

    <Confirmation
        :show="showDeleteSubcategoryConfirmation"
        @update:show="(val) => (showDeleteSubcategoryConfirmation = val)"
        @yes="executeDeleteSubcategory"
        title="Eliminar Subcategoría"
        message="¿Está seguro de que desea eliminar esta subcategoría? Esta acción no se puede deshacer."
        textYes="Sí, eliminar"
        textNo="Cancelar"
    />

    <!-- Subcategory Create/Edit Modal -->
    <Modal :show="showSubcategoryModal" @update:show="(val) => (showSubcategoryModal = val)" :title="editingSubcategory ? 'Editar Subcategoría' : 'Crear Subcategoría'" size="lg">
        <div class="create-subcategory-modal-body">
            <Input :disabled="savingSubcategory" title="Título" v-model="subcategoryForm.title" placeholder="Ej: Subcategoría 1" class="mb-4" />

            <TextArea :disabled="savingSubcategory" title="Descripción" v-model="subcategoryForm.description" placeholder="Describe esta subcategoría detalladamente">
                <template #description>
                    <small class="info-text-modal d-block mt-2">
                        <i class="feather-info"></i>
                        Se permite solo texto sin formato, no emojis.
                    </small>
                </template>
            </TextArea>

            <div v-if="parentSubcategories.length > 0" class="mt-4">
                <label class="subcat-label">Subcategoría Padre (opcional)</label>
                <select v-model="subcategoryForm.parent_id" class="subcat-select">
                    <option value="">Ninguna (nivel principal)</option>
                    <option v-for="p in parentSubcategories" :key="p.id" :value="p.id">{{ p.title }}</option>
                </select>
            </div>

            <div class="mt-4">
                <LabelForm title="Imagen de Miniatura" />
                <input type="file" accept="image/*" class="file-input-modal" @input="handleSubcategoryFile" />
                <small class="upload-hint-modal d-block mt-2">
                    <i class="feather-info"></i> Formatos: JPG, PNG. Opcional.
                </small>
            </div>

            <div class="mt-4">
                <Checkbox v-model="subcategoryForm.published" :disabled="savingSubcategory" title="Publicar Subcategoría" />
            </div>
        </div>

        <template #footer>
            <div class="footer-buttons-modal">
                <Button :disabled="savingSubcategory" @click="showSubcategoryModal = false" variant="outline" size="sm" class="btn-cancel-modal">
                    <i class="feather-x"></i> Cancelar
                </Button>
                <Button :loading="savingSubcategory" @click="saveSubcategory" size="sm" class="btn-save-modal">
                    <i class="feather-check"></i> {{ editingSubcategory ? 'Actualizar' : 'Crear' }}
                </Button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>
/* Quill Editor */
.ql-container.ql-snow {
    min-height: 200px;
}

/* Category Cards */
.category-card {
    border: 1.5px solid rgba(19, 58, 84, 0.15) !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.08) !important;
    background: #ffffff !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    margin-bottom: 24px;
}

.category-card:hover {
    border-color: rgba(19, 58, 84, 0.25) !important;
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.12) !important;
}

.category-card :deep(.card-header) {
    background: linear-gradient(180deg, rgba(19, 58, 84, 0.08) 0%, rgba(19, 58, 84, 0.03) 100%) !important;
    border-bottom: 2px solid rgba(19, 58, 84, 0.2) !important;
    padding: 24px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
}

.category-card :deep(.card-content) {
    padding: 0 !important;
}

.admin-title {
    font-size: 24px !important;
    font-weight: 800 !important;
    color: #1a1a1a !important;
    letter-spacing: -0.5px !important;
    margin: 0 !important;
}

.separator-compact {
    margin: 0 !important;
}

.btn-preview {
    color: #133a54 !important;
    text-decoration: none !important;
    font-weight: 700 !important;
    font-size: 12px !important;
    transition: all 0.3s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 8px 12px !important;
    border-radius: 6px !important;
    background: rgba(19, 58, 84, 0.08) !important;
}

.btn-preview:hover {
    background: rgba(19, 58, 84, 0.15) !important;
    transform: translateX(2px);
}

/* Form Styling */
.category-form {
    padding: 24px;
}

.form-row {
    margin-bottom: 20px;
}

.form-col {
    width: 100%;
}

.form-section {
    margin-bottom: 24px;
}

/* Upload Area */
.upload-wrapper {
    display: flex;
    gap: 24px;
    align-items: flex-start;
    margin: 16px 0;
    padding: 20px;
    background: rgba(19, 58, 84, 0.02);
    border: 1.5px solid rgba(19, 58, 84, 0.1);
    border-radius: 12px;
}

.upload-preview {
    flex-shrink: 0;
    width: 180px;
    height: 120px;
    border-radius: 8px;
    overflow: hidden;
    border: 1.5px solid rgba(19, 58, 84, 0.2);
    background: #f5f5f5;
}

.upload-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.upload-controls {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.file-input {
    display: none;
}

.upload-button {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: #ffffff;
    border: none;
    font-weight: 700;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    box-shadow: 0 6px 16px rgba(19, 58, 84, 0.25);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    width: fit-content;
}

.upload-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(19, 58, 84, 0.35);
}

.upload-button i {
    font-size: 16px;
}

.upload-hint {
    font-size: 12px;
    color: #666;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 6px;
}

.upload-hint i {
    color: #133a54;
}

/* Checkbox Wrapper */
.checkbox-wrapper {
    padding: 16px;
    background: rgba(19, 58, 84, 0.02);
    border: 1.5px solid rgba(19, 58, 84, 0.1);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.checkbox-hint {
    font-size: 12px;
    color: #666;
    font-weight: 500;
    margin-left: 28px;
}

/* Form Actions */
.form-actions {
    padding: 20px 24px;
    background: rgba(19, 58, 84, 0.02);
    border-top: 1px solid rgba(19, 58, 84, 0.1);
    display: flex;
    gap: 12px;
}

.btn-save {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    border: none !important;
    font-weight: 700 !important;
    padding: 10px 24px !important;
    border-radius: 8px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    box-shadow: 0 6px 16px rgba(19, 58, 84, 0.25) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
}

.btn-save:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 10px 25px rgba(19, 58, 84, 0.35) !important;
}

/* Search Wrapper */
.search-wrapper-compact {
    padding: 16px 20px;
    background: #ffffff;
    border-bottom: 1px solid rgba(19, 58, 84, 0.08);
}

.table-container {
    width: 100%;
    overflow: hidden;
    border-top: 1px solid rgba(19, 58, 84, 0.08);
}

/* Table Styling */
:deep(.table-premium) {
    border-collapse: collapse;
    width: 100%;
}

:deep(.table-premium thead th) {
    color: #151515 !important;
    font-weight: 800 !important;
    font-size: 13px !important;
    letter-spacing: 0.5px !important;
    padding: 16px 12px !important;
    border-bottom: 2px solid #133a54 !important;
    text-transform: uppercase;
}

:deep(.table-premium tbody tr) {
    border-bottom: 1px solid rgba(19, 58, 84, 0.1) !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

:deep(.table-premium tbody tr:hover) {
    background: rgba(19, 58, 84, 0.05) !important;
    box-shadow: inset 0 0 10px rgba(19, 58, 84, 0.08);
}

:deep(.table-premium tbody td) {
    padding: 16px 12px !important;
    color: #666 !important;
    font-weight: 500;
}

/* Badges */
.rbt-badge-5 {
    padding: 6px 12px !important;
    border-radius: 20px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    letter-spacing: 0.5px !important;
}

.bg-color-success-opacity {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(52, 211, 153, 0.15) 100%) !important;
}

.color-success {
    color: #10b981 !important;
}

.bg-color-warning-opacity {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(251, 146, 60, 0.15) 100%) !important;
}

.color-warning {
    color: #f59e0b !important;
}

.bg-color-danger-opacity {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(248, 113, 113, 0.15) 100%) !important;
}

.color-danger {
    color: #ef4444 !important;
}

/* Action Buttons */
.btn-option {
    padding: 8px 16px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    border: 1px solid transparent;
}

.btn-edit {
    color: #133a54 !important;
    background: rgba(19, 58, 84, 0.08) !important;
    border: 1px solid rgba(19, 58, 84, 0.2) !important;
}

.btn-edit:hover {
    background: rgba(19, 58, 84, 0.15) !important;
    border-color: rgba(19, 58, 84, 0.4) !important;
    transform: translateY(-1px);
}

.btn-delete {
    color: #ef4444 !important;
    background: rgba(239, 68, 68, 0.08) !important;
    border: 1px solid rgba(239, 68, 68, 0.2) !important;
}

.btn-delete:hover {
    background: rgba(239, 68, 68, 0.15) !important;
    border-color: rgba(239, 68, 68, 0.4) !important;
    transform: translateY(-1px);
}

/* Utility Classes */
.d-flex {
    display: flex;
}

.justify-content-center {
    justify-content: center;
}

.justify-content-end {
    justify-content: flex-end;
}

.m-0 {
    margin: 0;
}

.mt-2 {
    margin-top: 8px;
}

.mt-6 {
    margin-top: 32px;
}

.d-block {
    display: block;
}

/* Form Input Styling */
:deep(.input-label) {
    color: #151515 !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    letter-spacing: 0.5px !important;
    margin-bottom: 8px !important;
    text-transform: uppercase;
    display: block !important;
}

:deep(.input-field),
:deep(.textarea-field) {
    border: 1.5px solid #e0e0e0 !important;
    border-radius: 8px !important;
    padding: 12px 16px !important;
    font-size: 14px !important;
    transition: all 0.3s ease !important;
    background: #ffffff !important;
    width: 100% !important;
}

:deep(.input-field:focus),
:deep(.textarea-field:focus) {
    border-color: #133a54 !important;
    box-shadow: 0 0 0 3px rgba(19, 58, 84, 0.1) !important;
    outline: none !important;
}

:deep(.textarea-field) {
    min-height: 120px !important;
    resize: vertical !important;
}

/* Checkbox Styling */
:deep(input[type='checkbox']) {
    cursor: pointer !important;
    width: 18px !important;
    height: 18px !important;
}

/* Separator Styling */
:deep(.separator) {
    margin: 0 !important;
}

/* Custom Filter Styles */
.filter-options-section {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.filter-input-group {
    display: flex;
    align-items: center;
    gap: 12px;
}

.filter-input {
    flex: 1;
}

.btn-add-option {
    background: #133a54 !important;
    color: white !important;
    padding: 10px 20px !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    white-space: nowrap;
}

.btn-add-option:hover:not(:disabled) {
    background: #0d2a3e !important;
    transform: translateY(-1px);
}

.btn-add-option:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.filter-options-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-option-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: white;
    padding: 12px 16px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.filter-option-item:hover {
    border-color: #133a54;
    box-shadow: 0 2px 4px rgba(19, 58, 84, 0.1);
}

.filter-option-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
}

.filter-option-label {
    font-weight: 600;
    color: #1f2937;
    font-size: 14px;
}

.filter-option-values {
    font-size: 12px;
    color: #6b7280;
    font-family: monospace;
    background: #f3f4f6;
    padding: 2px 8px;
    border-radius: 4px;
    display: inline-block;
    width: fit-content;
}

.btn-confirm-option {
    border-color: #10b981 !important;
    color: #10b981 !important;
    padding: 10px 20px !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    white-space: nowrap;
    border-style: solid !important;
    border-width: 2px !important;
    background: transparent !important;
}

.btn-confirm-option:hover:not(:disabled) {
    background: #10b981 !important;
    color: white !important;
    transform: translateY(-1px);
}

.btn-confirm-option:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.filter-pending-values {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-pending-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    background: #fef3c7;
    border-radius: 6px;
    border: 1px dashed #f59e0b;
    font-size: 13px;
}

.filter-pending-label {
    color: #92400e;
}

.filter-pending-label strong {
    color: #b45309;
}

.filter-pending-count {
    font-size: 12px;
    color: #a16207;
    background: #fde68a;
    padding: 2px 10px;
    border-radius: 12px;
    font-weight: 600;
}

.filter-pending-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fffbeb;
    padding: 8px 16px;
    border-radius: 6px;
    border: 1px solid #fde68a;
}

.filter-pending-value {
    font-size: 13px;
    font-family: monospace;
    color: #78350f;
}

.btn-remove-option {
    color: #ef4444 !important;
    padding: 8px !important;
    border-radius: 4px !important;
    transition: all 0.2s ease !important;
}

.btn-remove-option:hover {
    background: rgba(239, 68, 68, 0.1) !important;
}

.filter-options-empty {
    text-align: center;
    padding: 40px 20px;
    color: #9ca3af;
    font-size: 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.filter-options-empty i {
    font-size: 32px;
    opacity: 0.5;
}

.ml-2 {
    margin-left: 8px;
}

.mb-3 {
    margin-bottom: 12px;
}

/* Subcategory Create Button */
.btn-create-subcategory {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    border: none !important;
    font-weight: 700 !important;
    padding: 10px 20px !important;
    border-radius: 8px !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.25) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    font-size: 13px !important;
    white-space: nowrap;
}

.btn-create-subcategory:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.35) !important;
}

/* Subcategory Modal */
.create-subcategory-modal-body {
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

.file-input-modal {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #ffffff;
}

.file-input-modal:focus {
    border-color: #133a54;
    box-shadow: 0 0 0 3px rgba(19, 58, 84, 0.1);
    outline: none;
}

.upload-hint-modal {
    color: #999;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.upload-hint-modal i {
    color: #133a54;
    font-size: 14px;
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

.subcat-label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #151515;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.subcat-select {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    background: #ffffff;
    transition: all 0.3s ease;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg width='14' height='8' viewBox='0 0 14 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L7 7L13 1' stroke='%23f07900' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 40px;
}

.subcat-select:focus {
    border-color: #133a54;
    box-shadow: 0 0 0 3px rgba(19, 58, 84, 0.1);
    outline: none;
}
</style>
