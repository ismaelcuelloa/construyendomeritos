<script setup lang="ts">
import DashboardStatCard from '@/features/admin/components/DashboardStatCard.vue';
import { isAdmin, isSuperUser } from '@/composables/useUser';
import AppAdminLayout from '@/layouts/AppAdminLayout.vue';
import { computed } from 'vue';

interface DashboardProps {
    stats: {
        students: any;
        courses: any;
        revenue: any;
        orders: any;
    };
    monthlyRevenue: number;
    revenueData: any[];
    activities: any[];
    summary: any;
}

const props = defineProps<DashboardProps>();

const currentDate = computed(() => {
    const date = new Date();
    const options: Intl.DateTimeFormatOptions = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    };
    return date.toLocaleDateString('es-ES', options);
});
</script>

<template>
    <AppAdminLayout title="Dashboard">
        <!-- Header Section -->
        <div class="dashboard-header mb--40">
            <div class="header-content">
                <h1 class="dashboard-title">Bienvenido al Panel de Control</h1>
                <p class="dashboard-subtitle">Aquí está el resumen de tu plataforma educativa</p>
            </div>
            <div class="header-date">
                <i class="feather-calendar"></i>
                <span>{{ currentDate }}</span>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid mb--40">
            <DashboardStatCard
                :label="props.stats.students.label"
                :value="props.stats.students.value"
                :icon="props.stats.students.icon"
                :change-percent="props.stats.students.changePercent"
                :is-positive="props.stats.students.isPositive"
            />
            <DashboardStatCard
                :label="props.stats.courses.label"
                :value="props.stats.courses.value"
                :icon="props.stats.courses.icon"
                :change-percent="props.stats.courses.changePercent"
                :is-positive="props.stats.courses.isPositive"
            />
            <DashboardStatCard
                v-if="isSuperUser()"
                :label="props.stats.revenue.label"
                :value="props.stats.revenue.value"
                :icon="props.stats.revenue.icon"
                :change-percent="props.stats.revenue.changePercent"
                :is-positive="props.stats.revenue.isPositive"
            />
            <DashboardStatCard
                v-else-if="isAdmin()"
                label="Categorías"
                :value="props.summary.totalCategories"
                icon="feather-layers"
                :change-percent="0"
                :is-positive="true"
            />
        </div>

        <!-- Bottom Section -->
        <div class="bottom-grid">
            <div class="info-card">
                <div class="info-icon">
                    <i class="feather-gift"></i>
                </div>
                <div class="info-content">
                    <h4>Promover Cursos</h4>
                    <p>Tienes {{ props.summary.totalCourses }} cursos disponibles. Aumenta visibilidad con promociones especiales</p>
                    <a href="/admin/cursos" class="info-link">Ir a Cursos <i class="feather-arrow-right"></i></a>
                </div>
            </div>

            <div class="info-card">
                <div class="info-icon">
                    <i class="feather-users"></i>
                </div>
                <div class="info-content">
                    <h4>Gestionar Usuarios</h4>
                    <p>Tienes {{ props.summary.totalStudents }} usuarios registrados en tu plataforma</p>
                    <a href="/admin/usuarios" class="info-link">Ir a Usuarios <i class="feather-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </AppAdminLayout>
</template>

<style scoped>
/* Dashboard Header Premium */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 32px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.12) 0%, rgba(26, 90, 128, 0.08) 100%);
    border: 2px solid rgba(19, 58, 84, 0.25);
    border-radius: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(19, 58, 84, 0.12);
    transition: all 0.3s ease;
}

.dashboard-header:hover {
    box-shadow: 0 12px 32px rgba(19, 58, 84, 0.18);
    transform: translateY(-2px);
}

.dashboard-header::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -80px;
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, rgba(19, 58, 84, 0.2), transparent);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}

.header-content {
    position: relative;
    z-index: 1;
}

.dashboard-title {
    font-size: 32px;
    font-weight: 900;
    color: #1a1a1a;
    margin: 0 0 10px 0;
    background: linear-gradient(135deg, #1a1a1a 0%, #133a54 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -1px;
}

.dashboard-subtitle {
    font-size: 16px;
    color: #555;
    margin: 0;
    font-weight: 600;
}

.header-date {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    color: #555;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.8);
    padding: 12px 20px;
    border-radius: 12px;
    position: relative;
    z-index: 1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.header-date i {
    color: #133a54;
    font-size: 20px;
}

/* Stats Grid Premium */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}

/* Bottom Grid Premium */
.bottom-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 24px;
}

.info-card {
    background: white;
    border: 2px solid rgba(19, 58, 84, 0.15);
    border-radius: 20px;
    padding: 28px;
    display: flex;
    gap: 20px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(19, 58, 84, 0.12), transparent);
    transition: left 0.6s ease;
}

.info-card:hover {
    transform: translateY(-8px);
    border-color: rgba(19, 58, 84, 0.4);
    box-shadow: 0 16px 48px rgba(19, 58, 84, 0.2);
}

.info-card:hover::before {
    left: 100%;
}

.info-icon {
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
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.3);
    transition: all 0.3s ease;
    position: relative;
}

.info-card:hover .info-icon {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 12px 28px rgba(19, 58, 84, 0.4);
}

.info-content {
    flex: 1;
    position: relative;
    z-index: 1;
}

.info-content h4 {
    font-size: 18px;
    font-weight: 800;
    color: #1a1a1a;
    margin: 0 0 8px 0;
    letter-spacing: -0.3px;
}

.info-content p {
    font-size: 14px;
    color: #666;
    margin: 0 0 16px 0;
    line-height: 1.6;
    font-weight: 500;
}

.info-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #133a54;
    text-decoration: none;
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    transition: all 0.3s ease;
    position: relative;
    padding-bottom: 2px;
}

.info-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #133a54, #1a5a80);
    transition: width 0.3s ease;
}

.info-link:hover {
    color: #1a5a80;
    gap: 12px;
}

.info-link:hover::after {
    width: 100%;
}

.info-link i {
    font-size: 14px;
    transition: transform 0.3s ease;
}

.info-link:hover i {
    transform: translateX(4px);
}

/* Spacing Utilities */
.mb--40 {
    margin-bottom: 40px;
}

/* Responsive */
@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }

    .bottom-grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    }
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        gap: 16px;
        padding: 24px;
    }

    .dashboard-title {
        font-size: 26px;
    }

    .dashboard-subtitle {
        font-size: 14px;
    }

    .header-date {
        width: 100%;
        justify-content: center;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .bottom-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .info-card {
        padding: 20px;
    }

    .info-icon {
        width: 60px;
        height: 60px;
        font-size: 28px;
    }
}
</style>
