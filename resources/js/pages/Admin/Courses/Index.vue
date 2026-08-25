<script setup lang="ts">
import CreateCourse from '@/features/courses/components/CreateCourse.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Confirmation from '@/components/ui/modal/Confirmation.vue';
import { Search } from '@/components/ui/search';
import { Separator } from '@/components/ui/separator';
import Toast from '@/composables/toast';
import { isSuperUser } from '@/composables/useUser';
import AppAdminLayout from '@/layouts/AppAdminLayout.vue';
import { Client } from '@/lib/client';
import { DataTable } from '@/lib/tables';
import Vue3Datatable from '@bhplugin/vue3-datatable';
import { Link, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

let timer: number;
const table = new DataTable();
const newCourse = ref(false);
const title = 'Material de Estudio';
const showDeleteModal = ref(false);
const courseToDelete = ref<any>(null);
const deleting = ref(false);

table.setCols([
    { field: 'title', title: 'Titulo' },
    { field: 'category', title: 'Categoría' },
    { field: 'grado', title: 'Código y Grado', sort: false },
    { field: 'price_formatted', title: 'Precio' },
    { field: 'modules_count', title: 'Módulos', sort: false },
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

const goToCurse = (course: any) => {
    // Si es super_user, va a la página de edición
    if (isSuperUser()) {
        return Client.ADMIN_COURSES + `/${course.id}`;
    }
    // Si es admin, va a la página pública del curso
    return `/cursos/${course.slug}`;
};

const handleSearch = (value: string | number) => {
    table.params.search = String(value);
    table.params.current_page = 1;
    filterCourses();
};

const openNewCourse = (value: boolean = true) => {
    newCourse.value = value;
};

const onSaveCourse = (course: any) => {
    router.push({ url: goToCurse(course) });
};

const openDeleteModal = (course: any) => {
    courseToDelete.value = course;
    showDeleteModal.value = true;
};

const deleteCourse = async () => {
    if (!courseToDelete.value) return;

    deleting.value = true;
    try {
        await Client.delete(`${Client.ADMIN_COURSES}/${courseToDelete.value.id}`);
        Toast.success('Curso eliminado con éxito');
        showDeleteModal.value = false;
        courseToDelete.value = null;
        getCourses();
    } catch (e) {
        Toast.error('Error al eliminar el curso');
        console.log(e);
    }
    deleting.value = false;
};

const copyingCourseId = ref<number | null>(null);

const copyCourse = async (course: any) => {
    copyingCourseId.value = course.id;
    try {
        await Client.post(`${Client.ADMIN_COURSES}/${course.id}/copy`, {});
        Toast.success('Curso copiado con éxito');
        getCourses();
    } catch (e: any) {
        Toast.error(e?.response?.data?.message || 'Error al copiar el curso');
        console.log(e);
    }
    copyingCourseId.value = null;
};

onMounted(() => {
    getCourses();
});
</script>

<template>
    <AppAdminLayout :title="title">
        <Card class="courses-card">
            <CardContent>
                <CardHeader>
                    <CardTitle class="admin-title">{{ title }}</CardTitle>
                    <Button v-if="isSuperUser()" @click="openNewCourse" class="btn-create-course">
                        <i class="feather-plus me-2"></i>
                        Crear Curso
                    </Button>
                </CardHeader>

                <Separator class="separator-compact" />

                <div class="search-wrapper-compact">
                    <Search placeholder="Buscar por nombre..." @search="handleSearch" />
                </div>

                <div class="table-container">
                    <div class="">
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
                            noDataContent="No hay material de estudio"
                            paginationInfo="Mostrando {0} de {2}"
                            @change="changeServer"
                            skin="bh-table-striped bh-table-hover bh-table-compact table-premium"
                        >
                            <template #category="data: any">
                                {{ data.value.category.title }}
                            </template>

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
                                <div class="rbt-button-group">
                                    <div class="rbt-button-group justify-content-end m-0">
                                        <Link :href="goToCurse(data.value)" class="rbt-btn-link left-icon" :title="isSuperUser() ? 'Editar' : 'Ver'">
                                            <i :class="isSuperUser() ? 'feather-edit' : 'feather-eye'"></i>
                                        </Link>
                                        <button
                                            v-if="isSuperUser()"
                                            @click="copyCourse(data.value)"
                                            class="rbt-btn-link left-icon"
                                            type="button"
                                            title="Copiar"
                                            :disabled="copyingCourseId === data.value.id"
                                        >
                                            <i class="feather-copy"></i>
                                        </button>
                                        <button
                                            v-if="isSuperUser()"
                                            @click="openDeleteModal(data.value)"
                                            class="rbt-btn-link left-icon color-danger"
                                            type="button"
                                            title="Eliminar"
                                        >
                                            <i class="feather-trash-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </vue3-datatable>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppAdminLayout>

    <CreateCourse @update:open="openNewCourse" :open="newCourse" @onSave="onSaveCourse" />

    <Confirmation
        :show="showDeleteModal"
        @update:show="(val) => (showDeleteModal = val)"
        title="Eliminar curso"
        message="¿Está seguro de eliminar este curso? Esta acción no se puede deshacer."
        @yes="deleteCourse"
        :loading="deleting"
    />
</template>

<style scoped>
/* Card Premium */
.courses-card {
    border: 2px solid rgba(19, 58, 84, 0.15) !important;
    border-radius: 20px !important;
    box-shadow: 0 8px 24px rgba(19, 58, 84, 0.1) !important;
    background: #ffffff !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    overflow: hidden;
}

.courses-card:hover {
    border-color: rgba(19, 58, 84, 0.3) !important;
    box-shadow: 0 12px 32px rgba(19, 58, 84, 0.15) !important;
    transform: translateY(-2px);
}

.courses-card :deep(.card-header) {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(26, 90, 128, 0.05) 100%) !important;
    border-bottom: 2px solid rgba(19, 58, 84, 0.2) !important;
    padding: 28px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
}

.courses-card :deep(.card-content) {
    padding: 0 !important;
}

.admin-title {
    font-size: 28px !important;
    font-weight: 900 !important;
    color: #1a1a1a !important;
    letter-spacing: -0.8px !important;
    margin: 0 !important;
    background: linear-gradient(135deg, #1a1a1a 0%, #133a54 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.separator-compact {
    margin: 0 !important;
    background: rgba(19, 58, 84, 0.1) !important;
}

.search-wrapper-compact {
    padding: 20px 28px;
    background: linear-gradient(180deg, rgba(19, 58, 84, 0.03) 0%, #ffffff 100%);
    border-bottom: 2px solid rgba(19, 58, 84, 0.1);
}

.table-container {
    width: 100%;
    overflow: hidden;
}

/* Botón Crear Curso Premium */
.btn-create-course {
    background: linear-gradient(135deg, #133a54 0%, #f5e42c 100%) !important;
    color: #ffffff !important;
    border: none !important;
    font-weight: 800 !important;
    padding: 14px 32px !important;
    border-radius: 12px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.3) !important;
    position: relative !important;
    overflow: hidden !important;
    font-size: 14px !important;
    letter-spacing: 0.5px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 10px !important;
    white-space: nowrap;
    height: auto;
    text-transform: uppercase;
}

.btn-create-course::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.6s ease;
    z-index: 1;
}

.btn-create-course::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition:
        width 0.6s,
        height 0.6s;
}

.btn-create-course:hover::before {
    left: 100%;
}

.btn-create-course:hover::after {
    width: 300px;
    height: 300px;
}

.btn-create-course:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 40px rgba(19, 58, 84, 0.4) !important;
    letter-spacing: 1px;
}

.btn-create-course:active {
    transform: translateY(0);
    box-shadow: 0 6px 16px rgba(19, 58, 84, 0.25) !important;
}

.btn-create-course i {
    position: relative;
    z-index: 2;
    font-size: 18px;
}

/* Tabla Premium */
:deep(.table-premium) {
    border-collapse: collapse;
    width: 100%;
}

:deep(.vue3-datatable) {
    overflow: hidden;
}

:deep(.vue3-datatable-wrapper) {
    overflow-x: auto;
}

:deep(.table-premium thead) {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.12) 0%, rgba(26, 90, 128, 0.08) 100%);
}

:deep(.table-premium thead th) {
    color: #1a1a1a !important;
    font-weight: 900 !important;
    font-size: 12px !important;
    letter-spacing: 0.8px !important;
    padding: 16px 12px !important;
    border-bottom: 3px solid #133a54 !important;
    text-transform: uppercase;
}

:deep(.table-premium tbody tr) {
    border-bottom: 1px solid rgba(19, 58, 84, 0.1) !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

:deep(.table-premium tbody tr:hover) {
    background: linear-gradient(90deg, rgba(19, 58, 84, 0.05) 0%, rgba(26, 90, 128, 0.03) 100%) !important;
    box-shadow: inset 0 0 15px rgba(19, 58, 84, 0.1);
    transform: scale(1.005);
}

:deep(.table-premium tbody td) {
    padding: 16px 12px !important;
    color: #555 !important;
    font-weight: 600;
    font-size: 13px;
    max-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

:deep(.table-premium tbody td:last-child) {
    max-width: none;
    width: 180px;
    white-space: normal;
}

/* Badges Premium */
.rbt-badge-5 {
    padding: 5px 12px !important;
    border-radius: 16px !important;
    font-size: 11px !important;
    font-weight: 800 !important;
    letter-spacing: 0.5px !important;
    border: 2px solid;
    text-transform: uppercase;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    line-height: 1 !important;
}

.bg-color-success-opacity {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, rgba(52, 211, 153, 0.2) 100%) !important;
    border-color: rgba(16, 185, 129, 0.3);
}

.color-success {
    color: #059669 !important;
}

.bg-color-warning-opacity {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.25) 0%, rgba(251, 146, 60, 0.2) 100%) !important;
    border-color: rgba(245, 158, 11, 0.3);
}

.color-warning {
    color: #d97706 !important;
}

.bg-color-danger-opacity {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.25) 0%, rgba(248, 113, 113, 0.2) 100%) !important;
    border-color: rgba(239, 68, 68, 0.3);
}

.color-danger {
    color: #dc2626 !important;
}

/* Botones de Acción Premium */
.rbt-button-group {
    display: flex !important;
    flex-direction: row !important;
    gap: 8px !important;
    align-items: center !important;
    flex-wrap: nowrap !important;
    justify-content: flex-end !important;
}

.rbt-btn-link {
    padding: 8px 12px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
    color: #133a54 !important;
    background: rgba(19, 58, 84, 0.1) !important;
    border: 2px solid rgba(19, 58, 84, 0.3) !important;
    white-space: nowrap !important;
}

.rbt-btn-link::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition:
        width 0.5s,
        height 0.5s;
    background: rgba(19, 58, 84, 0.2);
}

.rbt-btn-link:hover {
    background: linear-gradient(135deg, #133a54 0%, #f5e42c 100%) !important;
    color: white !important;
    border-color: #133a54 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(19, 58, 84, 0.3);
}

.rbt-btn-link:hover::before {
    width: 300px;
    height: 300px;
}

.rbt-btn-link.color-danger {
    color: #ef4444 !important;
    background: rgba(239, 68, 68, 0.1) !important;
    border: 2px solid rgba(239, 68, 68, 0.3) !important;
}

.rbt-btn-link:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.rbt-btn-link.color-danger::before {
    background: rgba(239, 68, 68, 0.2);
}

.rbt-btn-link.color-danger:hover {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    color: white !important;
    border-color: #ef4444 !important;
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
}

.left-icon {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.justify-content-end {
    justify-content: flex-end;
}

.m-0 {
    margin: 0;
}

.d-flex {
    display: flex;
}

.justify-content-center {
    justify-content: center;
}

/* Búsqueda Premium */
:deep(.search-input) {
    border: 2px solid rgba(19, 58, 84, 0.2) !important;
    border-radius: 12px !important;
    padding: 14px 20px !important;
    transition: all 0.3s ease !important;
    font-size: 14px !important;
    font-weight: 600;
}

:deep(.search-input:focus) {
    border-color: #133a54 !important;
    box-shadow: 0 0 0 4px rgba(19, 58, 84, 0.15) !important;
    outline: none !important;
}

:deep(.search-input::placeholder) {
    color: #999 !important;
    font-weight: 500;
}

/* Pagination Premium */
:deep(.pagination) {
    display: flex;
    gap: 8px;
    padding: 20px;
    justify-content: center;
}

:deep(.pagination button) {
    background: white !important;
    border: 2px solid rgba(19, 58, 84, 0.2) !important;
    color: #133a54 !important;
    padding: 10px 16px !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    transition: all 0.3s ease !important;
}

:deep(.pagination button:hover) {
    background: linear-gradient(135deg, #133a54 0%, #f5e42c 100%) !important;
    color: white !important;
    border-color: #133a54 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(19, 58, 84, 0.3);
}

:deep(.pagination button.active) {
    background: linear-gradient(135deg, #133a54 0%, #f5e42c 100%) !important;
    color: white !important;
    border-color: #133a54 !important;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.25);
}

/* Responsive */
@media (max-width: 768px) {
    .admin-title {
        font-size: 22px !important;
    }

    .btn-create-course {
        width: 100% !important;
        justify-content: center !important;
        padding: 12px 24px !important;
        font-size: 13px !important;
    }

    :deep(.table-premium) {
        font-size: 12px;
    }

    .rbt-btn-link {
        padding: 8px 14px !important;
        font-size: 12px !important;
    }

    .search-wrapper-compact {
        padding: 16px 20px;
    }

    :deep(.search-input) {
        padding: 12px 16px !important;
        font-size: 13px !important;
    }
}
</style>
