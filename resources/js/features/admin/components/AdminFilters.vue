<template>
    <div class="admin-filters">
        <div class="filters-header">
            <h4 class="filters-title">Filtros</h4>
            <button @click="clearFilters" v-if="hasFilters" class="clear-btn">
                <i class="feather-x"></i>
                Limpiar
            </button>
        </div>

        <div class="filters-content">
            <!-- Search -->
            <div class="filter-group">
                <label class="filter-label">Buscar</label>
                <div class="search-input-wrapper">
                    <i class="feather-search"></i>
                    <input v-model="searchQuery" type="text" placeholder="Buscar..." class="search-input" @input="emitFilters" />
                </div>
            </div>

            <!-- Date Range -->
            <div class="filter-group" v-if="showDateRange">
                <label class="filter-label">Fecha</label>
                <div class="date-range">
                    <input v-model="dateFrom" type="date" class="date-input" @change="emitFilters" />
                    <span class="date-separator">a</span>
                    <input v-model="dateTo" type="date" class="date-input" @change="emitFilters" />
                </div>
            </div>

            <!-- Status Filter -->
            <div class="filter-group" v-if="showStatus">
                <label class="filter-label">Estado</label>
                <select v-model="status" class="filter-select" @change="emitFilters">
                    <option value="">Todos</option>
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                    <option value="pending">Pendiente</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div class="filter-group" v-if="showCategory">
                <label class="filter-label">Categoría</label>
                <select v-model="category" class="filter-select" @change="emitFilters">
                    <option value="">Todas</option>
                    <option value="category1">Categoría 1</option>
                    <option value="category2">Categoría 2</option>
                    <option value="category3">Categoría 3</option>
                </select>
            </div>
        </div>

        <!-- Active Filters Badge -->
        <div v-if="hasFilters" class="active-filters">
            <div v-if="searchQuery" class="filter-badge">
                <span>{{ searchQuery }}</span>
                <button
                    @click="
                        searchQuery = '';
                        emitFilters();
                    "
                >
                    <i class="feather-x"></i>
                </button>
            </div>
            <div v-if="status" class="filter-badge">
                <span>{{ status }}</span>
                <button
                    @click="
                        status = '';
                        emitFilters();
                    "
                >
                    <i class="feather-x"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

interface Props {
    showDateRange?: boolean;
    showStatus?: boolean;
    showCategory?: boolean;
}

withDefaults(defineProps<Props>(), {
    showDateRange: false,
    showStatus: true,
    showCategory: false,
});

const emit = defineEmits<{
    (e: 'filter', value: any): void;
}>();

const searchQuery = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const status = ref('');
const category = ref('');

const hasFilters = computed(() => {
    return searchQuery.value || dateFrom.value || dateTo.value || status.value || category.value;
});

const clearFilters = () => {
    searchQuery.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    status.value = '';
    category.value = '';
    emitFilters();
};

const emitFilters = () => {
    emit('filter', {
        search: searchQuery.value,
        dateFrom: dateFrom.value,
        dateTo: dateTo.value,
        status: status.value,
        category: category.value,
    });
};
</script>

<style scoped>
.admin-filters {
    background: white;
    border: 1.5px solid rgba(19, 58, 84, 0.1);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
    transition: all 0.3s ease;
}

.admin-filters:hover {
    border-color: rgba(19, 58, 84, 0.2);
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.08);
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.filters-title {
    font-size: 14px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.clear-btn {
    padding: 6px 12px;
    background: none;
    border: 1px solid rgba(19, 58, 84, 0.2);
    border-radius: 6px;
    color: #133a54;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 4px;
}

.clear-btn:hover {
    background: rgba(19, 58, 84, 0.1);
    border-color: #133a54;
}

.filters-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-label {
    font-size: 12px;
    font-weight: 700;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-input-wrapper i {
    position: absolute;
    left: 12px;
    color: #999;
    font-size: 16px;
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 10px 12px 10px 36px;
    border: 1.5px solid rgba(19, 58, 84, 0.1);
    border-radius: 8px;
    font-size: 13px;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: #133a54;
    box-shadow: 0 0 0 3px rgba(19, 58, 84, 0.1);
}

.date-range {
    display: flex;
    align-items: center;
    gap: 8px;
}

.date-input {
    flex: 1;
    padding: 10px 12px;
    border: 1.5px solid rgba(19, 58, 84, 0.1);
    border-radius: 8px;
    font-size: 13px;
    transition: all 0.3s ease;
}

.date-input:focus {
    outline: none;
    border-color: #133a54;
    box-shadow: 0 0 0 3px rgba(19, 58, 84, 0.1);
}

.date-separator {
    color: #999;
    font-weight: 600;
    font-size: 12px;
}

.filter-select {
    padding: 10px 12px;
    border: 1.5px solid rgba(19, 58, 84, 0.1);
    border-radius: 8px;
    font-size: 13px;
    background: white;
    color: #333;
    cursor: pointer;
    transition: all 0.3s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23133a54' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 28px;
}

.filter-select:focus {
    outline: none;
    border-color: #133a54;
    box-shadow: 0 0 0 3px rgba(19, 58, 84, 0.1);
}

.active-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid rgba(19, 58, 84, 0.1);
}

.filter-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.15) 0%, rgba(26, 90, 128, 0.1) 100%);
    border: 1px solid rgba(19, 58, 84, 0.2);
    border-radius: 6px;
    color: #133a54;
    font-size: 12px;
    font-weight: 600;
}

.filter-badge button {
    background: none;
    border: none;
    color: #133a54;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.filter-badge button:hover {
    opacity: 1;
}

.filter-badge button i {
    font-size: 10px;
}

@media (max-width: 768px) {
    .filters-content {
        grid-template-columns: 1fr;
    }

    .filters-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
}
</style>
