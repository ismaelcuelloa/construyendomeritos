<template>
    <div class="chart-card">
        <div class="chart-header">
            <h4 class="chart-title">{{ title }}</h4>
            <div class="chart-filter">
                <button v-for="period in periods" :key="period" :class="{ active: selectedPeriod === period }" @click="selectedPeriod = period">
                    {{ period }}
                </button>
            </div>
        </div>
        <div class="chart-body">
            <div class="chart-placeholder">
                <svg viewBox="0 0 300 150" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#133a54" stop-opacity="0.3" />
                            <stop offset="100%" stop-color="#133a54" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                    <polyline
                        fill="url(#chartGradient)"
                        stroke="#133a54"
                        stroke-width="3"
                        points="0,180 50,140 100,90 150,100 200,50 250,60 300,20"
                    />
                    <circle cx="30" cy="120" r="3" fill="#133a54" />
                    <circle cx="80" cy="80" r="3" fill="#133a54" />
                    <circle cx="130" cy="50" r="3" fill="#133a54" />
                    <circle cx="180" cy="70" r="3" fill="#133a54" />
                    <circle cx="230" cy="30" r="3" fill="#133a54" />
                    <circle cx="280" cy="50" r="3" fill="#133a54" />
                </svg>
            </div>
            <div class="chart-stats">
                <div class="chart-stat-item">
                    <span class="stat-label">Promedio</span>
                    <span class="stat-number">$4,250.00</span>
                </div>
                <div class="chart-stat-item">
                    <span class="stat-label">Total</span>
                    <span class="stat-number">$25,500.00</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

interface Props {
    title: string;
    periods?: string[];
}

const props = withDefaults(defineProps<Props>(), {
    periods: () => ['1D', '1W', '1M', '1Y'],
});

const selectedPeriod = ref(props.periods[0]);
</script>

<style scoped>
.chart-card {
    background: white;
    border: 1.5px solid rgba(19, 58, 84, 0.1);
    border-radius: 16px;
    padding: 24px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

.chart-card:hover {
    border-color: rgba(19, 58, 84, 0.3);
    box-shadow: 0 12px 35px rgba(19, 58, 84, 0.1);
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}

.chart-title {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
}

.chart-filter {
    display: flex;
    gap: 8px;
}

.chart-filter button {
    padding: 6px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    background: white;
    color: #666;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.chart-filter button:hover {
    border-color: #133a54;
    color: #133a54;
}

.chart-filter button.active {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    border-color: #133a54;
    color: white;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.2);
}

.chart-body {
    display: grid;
    gap: 20px;
}

.chart-placeholder {
    width: 100%;
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chart-placeholder svg {
    width: 100%;
    height: 100%;
    max-width: 100%;
}

.chart-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.chart-stat-item {
    padding: 12px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.05) 0%, rgba(26, 90, 128, 0.02) 100%);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.stat-label {
    font-size: 12px;
    color: #999;
    font-weight: 600;
    text-transform: uppercase;
}

.stat-number {
    font-size: 18px;
    font-weight: 700;
    color: #133a54;
}

@media (max-width: 768px) {
    .chart-header {
        flex-direction: column;
    }

    .chart-title {
        width: 100%;
    }
}
</style>
