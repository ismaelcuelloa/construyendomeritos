<template>
    <div class="stat-card">
        <div class="stat-icon">
            <i :class="icon"></i>
        </div>
        <div class="stat-content">
            <p class="stat-label">{{ label }}</p>
            <h3 class="stat-value">{{ value }}</h3>
            <p class="stat-change" :class="changeClass">
                <i :class="changeIcon"></i>
                {{ changePercent }}%
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    label: string;
    value: string | number;
    icon: string;
    changePercent?: number;
    isPositive?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    changePercent: 0,
    isPositive: true,
});

const changeClass = computed(() => ({
    'text-success': props.isPositive,
    'text-danger': !props.isPositive,
}));

const changeIcon = computed(() => {
    return props.isPositive ? 'feather-arrow-up' : 'feather-arrow-down';
});
</script>

<style scoped>
.stat-card {
    background: white;
    border: 2px solid rgba(19, 58, 84, 0.2);
    border-radius: 20px;
    padding: 28px;
    display: flex;
    gap: 20px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(19, 58, 84, 0.08), transparent);
    transition: left 0.5s ease;
}

.stat-card::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 150px;
    height: 150px;
    background: radial-gradient(circle, rgba(19, 58, 84, 0.1), transparent);
    border-radius: 50%;
    z-index: 0;
}

.stat-card:hover {
    transform: translateY(-10px);
    border-color: rgba(19, 58, 84, 0.5);
    box-shadow: 0 16px 48px rgba(19, 58, 84, 0.2);
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.05) 0%, rgba(26, 90, 128, 0.03) 100%);
}

.stat-card:hover::before {
    left: 100%;
}

.stat-icon {
    flex-shrink: 0;
    width: 70px;
    height: 70px;
    border-radius: 16px;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: white;
    box-shadow: 0 8px 24px rgba(19, 58, 84, 0.35);
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

.stat-card:hover .stat-icon {
    transform: scale(1.1) rotate(-5deg);
    box-shadow: 0 12px 32px rgba(19, 58, 84, 0.45);
}

.stat-content {
    flex: 1;
    position: relative;
    z-index: 1;
}

.stat-label {
    font-size: 13px;
    color: #999;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0 0 10px 0;
}

.stat-value {
    font-size: 36px;
    font-weight: 900;
    color: #1a1a1a;
    margin: 0 0 10px 0;
    letter-spacing: -1.5px;
    line-height: 1;
}

.stat-change {
    font-size: 13px;
    font-weight: 800;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 20px;
    width: fit-content;
}

.stat-change i {
    font-size: 12px;
    font-weight: 900;
}

.text-success {
    color: #059669;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(52, 211, 153, 0.15) 100%);
    border: 2px solid rgba(16, 185, 129, 0.3);
}

.text-danger {
    color: #dc2626;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(248, 113, 113, 0.15) 100%);
    border: 2px solid rgba(239, 68, 68, 0.3);
}

@media (max-width: 768px) {
    .stat-card {
        padding: 20px;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        font-size: 28px;
    }

    .stat-value {
        font-size: 30px;
    }
}
</style>
