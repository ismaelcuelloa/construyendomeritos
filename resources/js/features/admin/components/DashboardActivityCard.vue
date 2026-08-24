<template>
    <div class="activity-card">
        <div class="activity-header">
            <h4 class="activity-title">{{ title }}</h4>
            <a href="#" class="view-all">Ver todo</a>
        </div>
        <div class="activity-list">
            <div v-for="(activity, index) in activities" :key="index" class="activity-item">
                <div class="activity-icon" :style="{ background: activity.color }">
                    <i :class="activity.icon"></i>
                </div>
                <div class="activity-content">
                    <p class="activity-text">{{ activity.text }}</p>
                    <p class="activity-time">{{ activity.time }}</p>
                </div>
                <div class="activity-badge" :class="activity.type">
                    {{ activity.status }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Activity {
    icon: string;
    text: string;
    time: string;
    color: string;
    type: string;
    status: string;
}

interface Props {
    title: string;
    activities?: Activity[];
}

withDefaults(defineProps<Props>(), {
    activities: () => [
        {
            icon: 'feather-user-plus',
            text: 'Nuevo usuario registrado',
            time: 'hace 5 minutos',
            color: 'linear-gradient(135deg, #133a54 0%, #1a5a80 100%)',
            type: 'new',
            status: 'Nuevo',
        },
        {
            icon: 'feather-shopping-cart',
            text: 'Nueva orden completada',
            time: 'hace 15 minutos',
            color: 'linear-gradient(135deg, #10b981 0%, #34d399 100%)',
            type: 'success',
            status: 'Completado',
        },
        {
            icon: 'feather-book',
            text: 'Nuevo curso creado',
            time: 'hace 1 hora',
            color: 'linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%)',
            type: 'new',
            status: 'Nuevo',
        },
        {
            icon: 'feather-alert-circle',
            text: 'Error en procesamiento de pago',
            time: 'hace 2 horas',
            color: 'linear-gradient(135deg, #ef4444 0%, #f87171 100%)',
            type: 'error',
            status: 'Error',
        },
    ],
});
</script>

<style scoped>
.activity-card {
    background: white;
    border: 1.5px solid rgba(19, 58, 84, 0.1);
    border-radius: 16px;
    padding: 24px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

.activity-card:hover {
    border-color: rgba(19, 58, 84, 0.3);
    box-shadow: 0 12px 35px rgba(19, 58, 84, 0.1);
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.activity-title {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
}

.view-all {
    color: #133a54;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.view-all:hover {
    color: #1a5a80;
    gap: 4px;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.activity-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    background: #f9f9f9;
    border-radius: 8px;
    transition: all 0.3s ease;
    align-items: flex-start;
}

.activity-item:hover {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.05) 0%, rgba(26, 90, 128, 0.02) 100%);
    transform: translateX(4px);
}

.activity-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
}

.activity-content {
    flex: 1;
    min-width: 0;
}

.activity-text {
    font-size: 13px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 4px 0;
    word-break: break-word;
}

.activity-time {
    font-size: 11px;
    color: #999;
    margin: 0;
}

.activity-badge {
    flex-shrink: 0;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    white-space: nowrap;
}

.activity-badge.new {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.15) 0%, rgba(26, 90, 128, 0.1) 100%);
    color: #133a54;
    border: 1px solid rgba(19, 58, 84, 0.2);
}

.activity-badge.success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(52, 211, 153, 0.1) 100%);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.activity-badge.error {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(248, 113, 113, 0.1) 100%);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.2);
}
</style>
