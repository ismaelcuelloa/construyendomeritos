<script setup lang="ts">
import { Button } from '@/components/ui/button';
import Modal from '@/components/ui/modal/Modal.vue';
import { DataTable, TableParams } from '@/lib/tables';
import { ref, watch } from 'vue';

// @ts-expect-error - Vue3Datatable does not have TypeScript declarations
import { Client } from '@/lib/client';
import Vue3Datatable from '@bhplugin/vue3-datatable';

const props = defineProps<{
    open?: boolean;
    user_id: string | number;
    exclude_courses_ids?: number[];
}>();

const emit = defineEmits<{
    (e: 'onSelect', course: any): void;
    (e: 'update:open', value: boolean): void;
}>();

let timer: number;
const table = new DataTable();

table.setCols([
    { field: 'title', title: 'Nombre' },
    { field: 'price', title: 'Precio' },
    { field: 'options', title: '', sort: false, width: 'fit-content' },
]);
table.setSort('title', 'asc');

const changeServer = (data: TableParams) => {
    table.setParams(data);
    filterCourses();
};

const filterCourses = () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        getCourses();
    }, 300);
};

const getCourses = async () => {
    try {
        table.loading.value = true;

        const sort: any = {};
        sort[table.params.sort_column] = table.params.sort_direction;

        const options: any = {
            per_page: 9999,
            page: table.params.current_page,
            sort: JSON.stringify(sort),
            search: '',
            exclude_user_id: props.user_id,
            exclude_courses_ids: [],
        };

        if (props.exclude_courses_ids !== undefined && props.exclude_courses_ids.length > 0) {
            options.exclude_courses_ids = props.exclude_courses_ids ?? [];
        }

        if (table.params.search !== '') {
            options.search = table.params.search;
        }

        const response = await Client.post(Client.ADMIN_COURSES + '/list', options);

        table.rows.value = response.data.data;
        table.total_rows.value = response.data.total;
    } catch {}

    table.loading.value = false;
};

const selectCourse = (course: any) => {
    emit('onSelect', course);
    close();
};

const close = () => {
    emit('update:open', false);
};

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

const reset = () => {
    getCourses();
};
</script>

<template>
    <Modal :show="isOpen" @update:show="(val) => (isOpen = val)" title="Seleccionar Material de Estudio" size="xl">
        <div class="select-course-container">
            <vue3-datatable
                :ref="table.table"
                :loading="table.loading"
                :rows="table.rows.value"
                :columns="table.cols"
                :totalRows="table.total_rows.value"
                :isServerMode="true"
                :page="table.params.current_page"
                :pageSize="9999"
                :showPageSize="false"
                :sortable="true"
                :sortColumn="table.params.sort_column"
                :sortDirection="table.params.sort_direction"
                :search="table.params.search"
                :hasCheckbox="false"
                :columnFilter="false"
                :pagination="false"
                noDataContent="No hay material de estudio encontrado"
                paginationInfo=""
                @change="changeServer"
                skin="bh-table-striped bh-table-hover table-select-premium"
            >
                <template #price="data: any">
                    <span class="price-badge">${{ new Intl.NumberFormat('es-CO').format(data.value.price) }} COP</span>
                </template>

                <template #options="data: any">
                    <div class="button-cell">
                        <Button variant="outline" @click="selectCourse(data.value)" class="btn-select-course">
                            <i class="feather-check-circle"></i> Seleccionar
                        </Button>
                    </div>
                </template>
            </vue3-datatable>
        </div>
    </Modal>
</template>

<style scoped>
.select-course-container {
    padding: 10px 0;
}

.select-course-container :deep(.table-select-premium) {
    border-collapse: collapse;
    width: 100%;
}

.select-course-container :deep(.table-select-premium thead) {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.12) 0%, rgba(26, 90, 128, 0.08) 100%);
}

.select-course-container :deep(.table-select-premium thead th) {
    color: #1a1a1a !important;
    font-weight: 900 !important;
    font-size: 13px !important;
    letter-spacing: 0.8px !important;
    padding: 16px 12px !important;
    border-bottom: 3px solid #133a54 !important;
    text-transform: uppercase;
}

.select-course-container :deep(.table-select-premium tbody tr) {
    border-bottom: 1px solid rgba(19, 58, 84, 0.1) !important;
    transition: all 0.3s ease;
}

.select-course-container :deep(.table-select-premium tbody tr:hover) {
    background: linear-gradient(90deg, rgba(19, 58, 84, 0.05) 0%, rgba(26, 90, 128, 0.03) 100%) !important;
}

.select-course-container :deep(.table-select-premium tbody td) {
    padding: 16px 12px !important;
    color: #555 !important;
    font-weight: 600;
    font-size: 14px;
}

.price-badge {
    display: inline-block;
    padding: 6px 12px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(26, 90, 128, 0.05) 100%);
    color: #133a54;
    font-weight: 800;
    font-size: 15px;
    border-radius: 8px;
    border: 2px solid rgba(19, 58, 84, 0.2);
}

.button-cell {
    display: flex;
    justify-content: flex-end;
}

.btn-select-course {
    background: #ffffff !important;
    color: #133a54 !important;
    border: 2px solid #133a54 !important;
    padding: 8px 16px !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    transition: all 0.3s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    font-size: 13px !important;
}

.btn-select-course:hover {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.3) !important;
}

.btn-select-course i {
    font-size: 16px;
}
</style>
