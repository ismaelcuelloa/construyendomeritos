<script setup lang="ts">
import CreateCategory from '@/features/catalog/components/CreateCategory.vue';
import BadgeStatusOrder from '@/features/orders/components/BadgeStatusOrder.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import SelectOrderStatus from '@/features/orders/components/selects/SelectOrderStatus.vue';
import { Separator } from '@/components/ui/separator';
import AppAdminLayout from '@/layouts/AppAdminLayout.vue';
import { Client } from '@/lib/client';
import { DataTable } from '@/lib/tables';
import Vue3Datatable from '@bhplugin/vue3-datatable';
import { Link, router } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';

let timer: number;
const table = new DataTable();
const newCategory = ref(false);
const title = 'Ordenes';

// Filtros
const filterSearch = ref('');
// Refrescar automáticamente la tabla al cambiar el filtro de búsqueda
watch(filterSearch, () => {
    table.params.current_page = 1;
    filterOrders();
});
const filterStatus = ref<number | null>(null);
const filterDateFrom = ref('');
const filterDateTo = ref('');

table.setCols([
    { field: 'number', title: 'Número' },
    { field: 'user.full_name', title: 'Usuario' },
    { field: 'user.email', title: 'Correo', sort: false },
    { field: 'user.phone', title: 'Teléfono', sort: false },
    { field: 'items_count', title: 'Cursos', sort: false },
    { field: 'created_at', title: 'Fecha', sort: true },
    { field: 'status', title: 'Estado', sort: false },
    { field: 'options', title: '', sort: false, width: 'fit-content' },
]);
table.setSort('title', 'asc');

const changeServer = (data: any) => {
    table.setParams(data);
    filterOrders();
};

const getOrders = async () => {
    try {
        table.loading.value = true;

        const sort: any = {};
        sort[table.params.sort_column] = table.params.sort_direction;

        const options: any = {
            per_page: table.params.pagesize,
            page: table.params.current_page,
            sort: JSON.stringify(sort),
            search: '',
        };

        if (table.params.search !== '') {
            options.search = table.params.search;
        }

        // Agregar filtros
        if (filterSearch.value !== '') {
            options.search = filterSearch.value;
        }
        if (filterStatus.value !== null) {
            options.status = filterStatus.value;
        }
        if (filterDateFrom.value !== '') {
            options.date_from = filterDateFrom.value;
        }
        if (filterDateTo.value !== '') {
            options.date_to = filterDateTo.value;
        }

        const response = await Client.get(Client.ADMIN_ORDERS + '/list', options);

        table.rows.value = response.data.data;
        table.total_rows.value = response.data.total;
    } catch {}

    table.loading.value = false;
};

const filterOrders = () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        getOrders();
    }, 300);
};

const goToOrder = (id: string) => {
    return Client.ADMIN_ORDERS + `/${id}`;
};

const onSaveCategory = (category: any) => {
    router.visit(goToOrder(category.id));
};

const formatDate = (dateString: string | any) => {
    if (!dateString) return 'N/A';

    try {
        const dateValue = new Date(dateString);

        if (isNaN(dateValue.getTime())) {
            console.warn('Invalid date:', dateString);
            return 'N/A';
        }

        const year = dateValue.getFullYear();
        const month = String(dateValue.getMonth() + 1).padStart(2, '0');
        const day = String(dateValue.getDate()).padStart(2, '0');

        return `${day}/${month}/${year}`;
    } catch (e) {
        console.error('Error formatting date:', dateString, e);
        return 'N/A';
    }
};

onMounted(() => {
    getOrders();
});
</script>

<template>
    <AppAdminLayout :title="title">
        <Card class="orders-card">
            <CardContent>
                <CardHeader>
                    <CardTitle class="admin-title">{{ title }}</CardTitle>
                </CardHeader>

                <Separator class="separator-compact" />

                <!-- Filtros -->
                <div class="filters-section">
                    <!-- Filtro principal: Búsqueda General -->
                    <div class="filter-row-main">
                        <div class="filter-item-full">
                            <label class="filter-label">Búsqueda General</label>
                            <Input
                                v-model="filterSearch"
                                @input="filterOrders"
                                placeholder="Buscar por número de orden, nombre, correo o celular..."
                                type="text"
                                class="filter-input"
                            />
                        </div>
                    </div>

                    <!-- Filtros de Estado y Fechas -->
                    <div class="filter-group">
                        <h4 class="filter-group-title">Estado y Fechas</h4>
                        <div class="filters-grid-3">
                            <div class="filter-item">
                                <label class="filter-label">Estado</label>
                                <SelectOrderStatus
                                    v-model="filterStatus"
                                    @update:modelValue="filterOrders"
                                    placeholder="Buscar por estado..."
                                    :allowClear="true"
                                    :showLabel="false"
                                />
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Desde</label>
                                <Input v-model="filterDateFrom" @change="filterOrders" type="date" class="filter-input" />
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Hasta</label>
                                <Input v-model="filterDateTo" @change="filterOrders" type="date" class="filter-input" />
                            </div>
                        </div>
                    </div>
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
                            noDataContent="No ordenes encontrados"
                            paginationInfo="Mostrando {0} de {2}"
                            @change="changeServer"
                            skin="bh-table-striped bh-table-hover bh-table-compact table-premium"
                        >
                            <template #items_count="data: any">
                                <div class="d-flex justify-content-center">
                                    <Badge variant="compact" type="default">{{ data.value.items_count }}</Badge>
                                </div>
                            </template>

                            <template #created_at="data: any">
                                <div>{{ formatDate(data.value.created_at) }}</div>
                            </template>

                            <template #status="data: any">
                                <div class="d-flex justify-content-center">
                                    <BadgeStatusOrder variant="compact" :status_id="data.value.status_id" />
                                </div>
                            </template>

                            <template #options="data: any">
                                <div class="rbt-button-group">
                                    <div class="rbt-button-group justify-content-end m-0">
                                        <Link :href="goToOrder(data.value.id)" class="rbt-btn-link left-icon"><i class="feather-eye"></i> Ver</Link>
                                    </div>
                                </div>
                            </template>
                        </vue3-datatable>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppAdminLayout>

    <CreateCategory @update:open="(val) => (newCategory.value = val)" :open="newCategory" @onSave="onSaveCategory" />
</template>

<style scoped>
/* Card Premium */
.orders-card {
    border: 2px solid rgba(19, 58, 84, 0.15) !important;
    border-radius: 20px !important;
    box-shadow: 0 8px 24px rgba(19, 58, 84, 0.1) !important;
    background: #ffffff !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    overflow: hidden;
}

.orders-card:hover {
    border-color: rgba(19, 58, 84, 0.3) !important;
    box-shadow: 0 12px 32px rgba(19, 58, 84, 0.15) !important;
    transform: translateY(-2px);
}

.orders-card :deep(.card-header) {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(26, 90, 128, 0.05) 100%) !important;
    border-bottom: 2px solid rgba(19, 58, 84, 0.2) !important;
    padding: 28px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
}

.orders-card :deep(.card-content) {
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
    background: rgba(19, 58, 84, 3.1) !important;
}

.search-wrapper-compact {
    padding: 20px 28px;
    background: linear-gradient(180deg, rgba(19, 58, 84, 0.03) 0%, #ffffff 100%);
    border-bottom: 2px solid rgba(19, 58, 84, 0.1);
}

/* Filtros Premium */
.filters-section {
    padding: 24px 28px;
    background: linear-gradient(180deg, #ffffff 0%, rgba(19, 58, 84, 0.02) 100%);
    border-bottom: 2px solid rgba(19, 58, 84, 0.1);
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.filter-row-main {
    width: 100%;
}

.filter-item-full {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.filter-group-title {
    font-size: 14px;
    font-weight: 800;
    color: #133a54;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0;
    padding-bottom: 8px;
    border-bottom: 2px solid rgba(19, 58, 84, 0.2);
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    align-items: start;
}

.filters-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    align-items: start;
}

.filter-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: 72px;
}

.filter-label {
    font-size: 13px;
    font-weight: 700;
    color: #1a1a1a;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0;
    min-height: 20px;
    line-height: 20px;
}

/* Input Filters */
:deep(.filter-input) {
    width: 100%;
    display: block;
}

:deep(.filter-input input) {
    width: 100%;
    height: 44px;
    border: 2px solid rgba(19, 58, 84, 0.3) !important;
    border-radius: 12px !important;
    padding: 0 16px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    background: linear-gradient(135deg, #ffffff 0%, rgba(19, 58, 84, 0.03) 100%) !important;
    box-sizing: border-box !important;
}

:deep(.filter-input input:hover) {
    border-color: rgba(19, 58, 84, 0.5) !important;
    background: linear-gradient(135deg, #ffffff 0%, rgba(19, 58, 84, 0.05) 100%) !important;
}

:deep(.filter-input input:focus) {
    border-color: #133a54 !important;
    box-shadow: 0 0 0 4px rgba(19, 58, 84, 0.15) !important;
    outline: none !important;
    background: #ffffff !important;
    transform: translateY(-1px);
}

/* Select Filter Styles */
:deep(.filter-item .rbt-modern-select) {
    height: 44px !important;
    width: 100%;
    margin-top: 4px;
}

:deep(.filter-item .selectpicker) {
    height: 44px !important;
    border: 2px solid rgba(19, 58, 84, 0.3) !important;
    border-radius: 12px !important;
    padding: 0 16px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    background: linear-gradient(135deg, #ffffff 0%, rgba(19, 58, 84, 0.03) 100%) !important;
}

:deep(.filter-item .bootstrap-select) {
    width: 100% !important;
    margin-top: 4px;
}

:deep(.filter-item .bootstrap-select .dropdown-toggle) {
    height: 44px !important;
    width: 100% !important;
    border: 2px solid rgba(19, 58, 84, 0.3) !important;
    border-radius: 12px !important;
    padding: 0 16px !important;
    padding-top: 0 !important;
    display: flex !important;
    align-items: center !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    background: linear-gradient(135deg, #ffffff 0%, rgba(19, 58, 84, 0.03) 100%) !important;
    box-sizing: border-box !important;
}

:deep(.filter-item .bootstrap-select .filter-option) {
    line-height: normal !important;
    padding-top: 0 !important;
}

:deep(.filter-item .bootstrap-select .filter-option-inner-inner) {
    line-height: normal !important;
}

:deep(.filter-item .bootstrap-select .dropdown-toggle:hover) {
    border-color: rgba(19, 58, 84, 0.5) !important;
    background: linear-gradient(135deg, #ffffff 0%, rgba(19, 58, 84, 0.05) 100%) !important;
}

:deep(.filter-item .bootstrap-select.show .dropdown-toggle) {
    border-color: #133a54 !important;
    box-shadow: 0 0 0 4px rgba(19, 58, 84, 0.15) !important;
    background: #ffffff !important;
}

.table-container {
    width: 100%;
    overflow: hidden;
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
    font-size: 13px !important;
    letter-spacing: 1px !important;
    padding: 18px 16px !important;
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
    padding: 20px 16px !important;
    color: #555 !important;
    font-weight: 600;
    font-size: 14px;
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

:deep(.table-premium tbody td:last-child) {
    max-width: none;
    width: 200px;
    white-space: normal;
}

/* Badges Premium */
:deep(.badge) {
    padding: 6px 16px !important;
    border-radius: 20px !important;
    font-size: 12px !important;
    font-weight: 800 !important;
    letter-spacing: 0.8px !important;
    border: 2px solid;
    text-transform: uppercase;
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
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: white !important;
    border-color: #133a54 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(19, 58, 84, 0.3);
}

.rbt-btn-link:hover::before {
    width: 300px;
    height: 300px;
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
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: white !important;
    border-color: #133a54 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(19, 58, 84, 0.3);
}

:deep(.pagination button.active) {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: white !important;
    border-color: #133a54 !important;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.25);
}

/* Responsive */
@media (max-width: 768px) {
    .admin-title {
        font-size: 22px !important;
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
