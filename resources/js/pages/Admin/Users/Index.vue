<script setup lang="ts">
import Link from '@/components/ui/link/Link.vue';
// @ts-expect-error - Vue3Datatable does not have TypeScript declarations
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Search } from '@/components/ui/search';
import { Separator } from '@/components/ui/separator';
import CreateUser from '@/features/users/components/CreateUser.vue';
import AppAdminLayout from '@/layouts/AppAdminLayout.vue';
import { Client } from '@/lib/client';
import { DataTable } from '@/lib/tables';
import Vue3Datatable from '@bhplugin/vue3-datatable';
import { computed, onMounted, ref } from 'vue';

import { usePage } from '@inertiajs/vue3';

let timer: number;
const table = new DataTable();
const newUser = ref(false);
const title = 'Gestión de Usuarios';
const totalUsers = ref(0);
const userToDelete = ref<any>(null);
const showDeleteDialog = ref(false);
const dateFrom = ref('');
const dateTo = ref('');

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

// Verificar si el usuario autenticado es super_user
const isSuperUser = computed(() => {
    return authUser.value?.roles?.some((role: any) => role.name === 'super_user');
});

table.setCols([
    { field: 'name', title: 'Nombres' },
    { field: 'email', title: 'Email' },
    { field: 'phone', title: 'Teléfono', sort: false },
    { field: 'role', title: 'Rol', sort: false },
    { field: 'created_at', title: 'Fecha de Registro', sort: true },
    { field: 'options', title: '', sort: false, width: 'fit-content' },
]);
table.setSort('created_at', 'desc');

const changeServer = (data: any) => {
    table.setParams(data);
    filterUsers();
};

const getUsers = async () => {
    try {
        table.loading.value = true;

        const sort: any = {};
        sort[table.params.sort_column] = table.params.sort_direction;

        const options = {
            per_page: table.params.pagesize,
            page: table.params.current_page,
            sort: JSON.stringify(sort),
            search: '',
            date_from: '',
            date_to: '',
        };

        if (table.params.search !== '') {
            options.search = table.params.search;
        }

        if (dateFrom.value) {
            options.date_from = dateFrom.value;
        }

        if (dateTo.value) {
            options.date_to = dateTo.value;
        }

        const response = await Client.get(Client.ADMIN_USERS + '/list', options);

        table.rows.value = response.data.data;
        table.total_rows.value = response.data.total;
        totalUsers.value = response.data.total;
    } catch {}

    table.loading.value = false;
};

const filterUsers = () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        getUsers();
    }, 300);
};

const goToUser = (id: string) => {
    return Client.ADMIN_USERS + `/${id}`;
};

const handleSearch = (value: string | number) => {
    table.params.search = String(value);
    table.params.current_page = 1;
    filterUsers();
};

const handleDateFilter = () => {
    table.params.current_page = 1;
    filterUsers();
};

const clearDateFilters = () => {
    dateFrom.value = '';
    dateTo.value = '';
    table.params.current_page = 1;
    filterUsers();
};

const openNewUser = (value: boolean = true) => {
    newUser.value = value;
};

const onSaveUser = () => {
    getUsers();
};

const confirmDelete = (user: any) => {
    userToDelete.value = user;
    showDeleteDialog.value = true;
};

const deleteUser = async () => {
    if (!userToDelete.value) return;

    try {
        await Client.delete(`${Client.ADMIN_USERS}/${userToDelete.value.id}`);
        showDeleteDialog.value = false;
        userToDelete.value = null;
        getUsers();
    } catch (error: any) {
        console.error('Error deleting user:', error);
        alert(error.response?.data?.message || 'Error al eliminar usuario');
    }
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    userToDelete.value = null;
};

const formatDate = (dateString: string | any) => {
    try {
        if (!dateString) return 'N/A';

        let dateValue: Date | null = null;

        // Si es un objeto con propiedades de fecha
        if (typeof dateString === 'object' && dateString.date) {
            dateValue = new Date(dateString.date);
        }
        // Si es un string, intentar parsearlo
        else if (typeof dateString === 'string') {
            dateValue = new Date(dateString);
        }

        if (!dateValue || isNaN(dateValue.getTime())) {
            console.warn('Invalid date value:', dateString);
            return 'Fecha inválida';
        }

        // Formatear manualmente para evitar problemas de timezone
        const year = dateValue.getFullYear();
        const month = String(dateValue.getMonth() + 1).padStart(2, '0');
        const day = String(dateValue.getDate()).padStart(2, '0');

        return `${day}/${month}/${year}`;
    } catch (e) {
        console.error('Error formatting date:', dateString, e);
        return 'Fecha inválida';
    }
};

onMounted(() => {
    getUsers();
});
</script>

<template>
    <AppAdminLayout :title="title">
        <Card class="users-card">
            <CardContent>
                <CardHeader>
                    <CardTitle class="admin-title">Usuarios</CardTitle>
                    <Button @click="openNewUser" class="btn-create-user">
                        <i class="feather-plus me-2"></i>
                        Nuevo Usuario
                    </Button>
                </CardHeader>

                <Separator class="separator-compact" />

                <div class="filters-wrapper">
                    <div class="search-wrapper-compact">
                        <Search placeholder="Buscar por nombre o email..." @search="handleSearch" />
                    </div>

                    <div class="date-filters">
                        <div class="date-input-group">
                            <label>Desde:</label>
                            <input type="date" v-model="dateFrom" @change="handleDateFilter" class="date-input" />
                        </div>
                        <div class="date-input-group">
                            <label>Hasta:</label>
                            <input type="date" v-model="dateTo" @change="handleDateFilter" class="date-input" />
                        </div>
                        <button v-if="dateFrom || dateTo" @click="clearDateFilters" class="btn-clear-filters" title="Limpiar filtros de fecha">
                            <i class="feather-x"></i>
                        </button>
                    </div>
                </div>

                <div class="table-container">
                    <div class="col-12">
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
                            noDataContent="No usuarios encontrados"
                            paginationInfo="Mostrando {0} de {2}"
                            @change="changeServer"
                            skin="bh-table-striped bh-table-hover bh-table-compact table-premium"
                        >
                            <template #role="data: any">
                                <Badge class="badge-role">
                                    {{ data.value.roles[0]?.description }}
                                </Badge>
                            </template>

                            <template #phone="data: any">
                                <span class="phone-text">{{ data.value.phone || 'N/A' }}</span>
                            </template>

                            <template #created_at="data: any">
                                <span class="date-text">{{ formatDate(data.value.created_at) }}</span>
                            </template>

                            <template #options="data: any">
                                <div class="rbt-button-group justify-content-end">
                                    <Link :href="goToUser(data.value.id)" class="btn-option btn-edit"> <i class="feather-edit-2"></i> Editar </Link>
                                    <button v-if="isSuperUser" class="btn-option btn-delete" @click="confirmDelete(data.value)">
                                        <i class="feather-trash-2"></i> Eliminar
                                    </button>
                                </div>
                            </template>
                        </vue3-datatable>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppAdminLayout>

    <CreateUser @update:open="openNewUser" :open="newUser" @onSave="onSaveUser" />

    <!-- Diálogo de confirmación de eliminación -->
    <div v-if="showDeleteDialog" class="delete-dialog-overlay" @click="cancelDelete">
        <div class="delete-dialog-content" @click.stop>
            <div class="delete-dialog-header">
                <h3>Confirmar eliminación</h3>
            </div>
            <div class="delete-dialog-body">
                <p>
                    ¿Estás seguro de que deseas eliminar al usuario <strong>{{ userToDelete?.name }}</strong
                    >?
                </p>
                <p>Esta acción no se puede deshacer.</p>
            </div>
            <div class="delete-dialog-footer">
                <button class="btn-cancel" @click="cancelDelete">Cancelar</button>
                <button class="btn-delete-confirm" @click="deleteUser">Eliminar</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Users Card Premium */
.users-card {
    border: 2px solid rgba(19, 58, 84, 0.15) !important;
    border-radius: 20px !important;
    box-shadow: 0 8px 24px rgba(19, 58, 84, 0.1) !important;
    background: #ffffff !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    overflow: hidden;
}

.users-card:hover {
    border-color: rgba(19, 58, 84, 0.3) !important;
    box-shadow: 0 12px 32px rgba(19, 58, 84, 0.15) !important;
    transform: translateY(-2px);
}

.users-card :deep(.card-header) {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(26, 90, 128, 0.05) 100%) !important;
    border-bottom: 2px solid rgba(19, 58, 84, 0.2) !important;
    padding: 28px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
}

.users-card :deep(.card-content) {
    padding: 0 !important;
}

.separator-compact {
    margin: 0 !important;
    background: rgba(19, 58, 84, 0.1) !important;
}

/* Admin Title Premium */
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

/* Botón Crear Usuario Premium */
.btn-create-user {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
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

.btn-create-user::before {
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

.btn-create-user::after {
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

.btn-create-user:hover::before {
    left: 100%;
}

.btn-create-user:hover::after {
    width: 300px;
    height: 300px;
}

.btn-create-user:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 40px rgba(19, 58, 84, 0.4) !important;
    letter-spacing: 1px;
}

.btn-create-user:active {
    transform: translateY(0);
    box-shadow: 0 6px 16px rgba(19, 58, 84, 0.25) !important;
}

.btn-create-user i {
    position: relative;
    z-index: 2;
    font-size: 18px;
}

.btn-create-user span {
    position: relative;
    z-index: 2;
}

/* Search Wrapper Premium */
.search-wrapper-compact {
    padding: 20px 28px;
    background: linear-gradient(180deg, rgba(19, 58, 84, 0.03) 0%, #ffffff 100%);
}

/* Filters Wrapper */
.filters-wrapper {
    border-bottom: 2px solid rgba(19, 58, 84, 0.1);
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* Date Filters */
.date-filters {
    padding: 12px 28px 20px 28px;
    display: flex;
    gap: 16px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.date-input-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.date-input-group label {
    font-size: 12px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.date-input {
    padding: 10px 14px;
    border: 2px solid rgba(19, 58, 84, 0.2);
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
    color: #333;
    min-width: 160px;
}

.date-input:focus {
    outline: none;
    border-color: #133a54;
    box-shadow: 0 0 0 3px rgba(19, 58, 84, 0.1);
}

.btn-clear-filters {
    padding: 10px 16px;
    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.btn-clear-filters:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-clear-filters i {
    font-size: 16px;
}

/* Tabla Container Premium */
.table-container {
    width: 100%;
    overflow: hidden;
}

:deep(.vue3-datatable) {
    overflow: hidden;
}

:deep(.vue3-datatable-wrapper) {
    overflow-x: auto;
}

/* Tabla Premium */
:deep(.table-premium) {
    border-collapse: collapse;
    width: 100%;
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
    position: relative;
}

:deep(.table-premium thead th::after) {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 0;
    height: 3px;
    background: linear-gradient(90deg, #133a54, #1a5a80);
    transition: width 0.3s ease;
}

:deep(.table-premium thead th:hover::after) {
    width: 100%;
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

/* Badge de Rol Premium */
.badge-role {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, rgba(52, 211, 153, 0.2) 100%) !important;
    color: #059669 !important;
    font-weight: 800 !important;
    padding: 6px 16px !important;
    border-radius: 20px !important;
    font-size: 12px !important;
    letter-spacing: 0.8px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: 2px solid rgba(16, 185, 129, 0.3);
    text-transform: uppercase;
    line-height: 1 !important;
}

.date-text {
    color: #666 !important;
    font-size: 14px;
    font-weight: 600;
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

.btn-option {
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
    white-space: nowrap !important;
}

.btn-option::before {
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
}

.btn-edit {
    color: #133a54 !important;
    background: rgba(19, 58, 84, 0.1) !important;
    border: 2px solid rgba(19, 58, 84, 0.3) !important;
}

.btn-edit::before {
    background: rgba(19, 58, 84, 0.2);
}

.btn-edit:hover {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: white !important;
    border-color: #133a54 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(19, 58, 84, 0.3);
}

.btn-edit:hover::before {
    width: 300px;
    height: 300px;
}

.btn-delete {
    color: #ef4444 !important;
    background: rgba(239, 68, 68, 0.1) !important;
    border: 2px solid rgba(239, 68, 68, 0.3) !important;
    padding: 10px 18px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    border-radius: 10px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
    cursor: pointer;
    background: transparent;
    position: relative;
    overflow: hidden;
}

.btn-delete::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.2);
    transform: translate(-50%, -50%);
    transition:
        width 0.5s,
        height 0.5s;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    color: white !important;
    border-color: #ef4444 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(239, 68, 68, 0.3);
}

.btn-delete:hover::before {
    width: 300px;
    height: 300px;
}

/* Search Premium */
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

    .btn-create-user {
        width: 100% !important;
        justify-content: center !important;
        padding: 12px 24px !important;
        font-size: 13px !important;
    }

    :deep(.table-premium) {
        font-size: 12px;
    }

    .btn-option {
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

/* Diálogo de eliminación personalizado */
.delete-dialog-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.delete-dialog-content {
    background: white;
    border-radius: 12px;
    padding: 0;
    width: 90%;
    max-width: 450px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.delete-dialog-header {
    padding: 24px 24px 16px;
    border-bottom: 1px solid #e5e5e5;
}

.delete-dialog-header h3 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
}

.delete-dialog-body {
    padding: 24px;
}

.delete-dialog-body p {
    margin: 0 0 12px;
    color: #666;
    line-height: 1.6;
}

.delete-dialog-body p:last-child {
    margin-bottom: 0;
}

.delete-dialog-footer {
    padding: 16px 24px;
    border-top: 1px solid #e5e5e5;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.btn-cancel,
.btn-delete-confirm {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.btn-cancel {
    background: #f5f5f5;
    color: #666;
}

.btn-cancel:hover {
    background: #e5e5e5;
    color: #333;
}

.btn-delete-confirm {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.btn-delete-confirm:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}
</style>
