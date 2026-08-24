<template>
    <div class="admin-table-container">
        <div class="table-header">
            <div class="header-left">
                <h4 class="table-title">{{ title }}</h4>
                <p class="table-subtitle">{{ items.length }} {{ itemLabel }}</p>
            </div>
            <div class="header-right">
                <slot name="actions"></slot>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th v-for="col in columns" :key="col.key" :style="{ width: col.width }">
                            {{ col.label }}
                        </th>
                        <th style="width: 100px; text-align: right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, idx) in items" :key="idx" class="table-row">
                        <td v-for="col in columns" :key="col.key">
                            <slot :name="`cell-${col.key}`" :item="item" :value="getNestedValue(item, col.key)">
                                {{ getNestedValue(item, col.key) }}
                            </slot>
                        </td>
                        <td style="text-align: right">
                            <div class="action-buttons">
                                <slot name="row-actions" :item="item"></slot>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="items.length === 0" class="empty-state">
            <i class="feather-inbox"></i>
            <p>No hay {{ itemLabel }} disponibles</p>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Column {
    key: string;
    label: string;
    width?: string;
}

interface Props {
    title: string;
    itemLabel?: string;
    items: any[];
    columns: Column[];
}

withDefaults(defineProps<Props>(), {
    itemLabel: 'items',
});

const getNestedValue = (obj: any, path: string) => {
    return path.split('.').reduce((acc, part) => acc?.[part], obj);
};
</script>

<style scoped>
.admin-table-container {
    background: white;
    border: 1.5px solid rgba(19, 58, 84, 0.1);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

.admin-table-container:hover {
    border-color: rgba(19, 58, 84, 0.2);
    box-shadow: 0 8px 24px rgba(19, 58, 84, 0.1);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.05) 0%, rgba(26, 90, 128, 0.02) 100%);
    border-bottom: 1.5px solid rgba(19, 58, 84, 0.1);
    flex-wrap: wrap;
    gap: 12px;
}

.header-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.table-title {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
}

.table-subtitle {
    font-size: 12px;
    color: #999;
    margin: 0;
}

.header-right {
    display: flex;
    gap: 8px;
}

.table-wrapper {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table thead th {
    padding: 16px 20px;
    text-align: left;
    font-size: 12px;
    font-weight: 700;
    color: #133a54;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.05) 0%, rgba(26, 90, 128, 0.02) 100%);
    border-bottom: 2px solid rgba(19, 58, 84, 0.2);
}

.admin-table tbody td {
    padding: 16px 20px;
    border-bottom: 1px solid rgba(19, 58, 84, 0.05);
    font-size: 13px;
    color: #333;
}

.table-row {
    transition: all 0.3s ease;
}

.table-row:hover {
    background: linear-gradient(90deg, rgba(19, 58, 84, 0.05) 0%, rgba(26, 90, 128, 0.02) 100%);
}

.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.empty-state {
    padding: 60px 20px;
    text-align: center;
    color: #999;
}

.empty-state i {
    font-size: 48px;
    color: rgba(19, 58, 84, 0.2);
    display: block;
    margin-bottom: 12px;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

@media (max-width: 768px) {
    .table-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-right {
        width: 100%;
        justify-content: flex-start;
    }

    .admin-table {
        font-size: 12px;
    }

    .admin-table thead th,
    .admin-table tbody td {
        padding: 12px 12px;
    }

    .action-buttons {
        gap: 4px;
    }
}
</style>
