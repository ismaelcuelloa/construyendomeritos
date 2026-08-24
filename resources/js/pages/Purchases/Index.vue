<script setup lang="ts">
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Client } from '@/lib/client';
import { Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

interface Purchase {
    id: number;
    number: string;
    amount: number;
    created_at: string;
    items: Array<{
        id: number;
        course_id: number;
        course: {
            id: number;
            title: string;
            short_description: string;
            image: string;
            slug: string;
        };
        unit_price: number;
    }>;
}

const purchases = ref<Purchase[]>([]);
const loading = ref(true);

const getPurchases = async () => {
    try {
        loading.value = true;
        const response = await Client.get('/mis_compras/list');
        purchases.value = response.data.purchases || [];
    } catch (error) {
        console.error('Error cargando compras:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
    }).format(price);
};

const goToCourse = (slug: string) => {
    return `/cursos/${slug}`;
};

onMounted(() => {
    getPurchases();
});
</script>

<template>
    <AppLayout>
        <div class="rbt-page-banner-wrapper">
            <div class="rbt-banner-image"></div>
            <div class="banner-animated-circles">
                <div class="animated-circle circle-1"></div>
                <div class="animated-circle circle-2"></div>
                <div class="animated-circle circle-3"></div>
                <div class="animated-circle circle-4"></div>
                <div class="animated-circle circle-5"></div>
            </div>
            <div class="rbt-banner-content">
                <div class="rbt-banner-content-top">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="section-title pt--30 pb--20 text-center">
                                    <span class="subtitle bg-primary-opacity"> <i class="feather-shopping-bag"></i> Historial </span>
                                    <h2 class="title">Mis Compras</h2>
                                    <p class="description mt--20">Aquí puedes ver todos los materiales de estudio que has adquirido</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rbt-dashboard-area rbt-section-overlayping-top rbt-section-gapBottom">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div v-if="loading" class="py-5 text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>

                        <div v-else-if="purchases.length === 0" class="py-5 text-center">
                            <div class="empty-state">
                                <i class="feather-shopping-bag empty-icon"></i>
                                <h3 class="mt-4">No tienes compras aún</h3>
                                <p class="text-muted">Explora nuestros materiales de estudio y comienza tu preparación</p>
                                <Link href="/cursos" class="btn-explore-empty mt-4">
                                    <span>Ver Materiales</span>
                                    <i class="feather-arrow-right"></i>
                                </Link>
                            </div>
                        </div>

                        <div v-else class="purchases-container">
                            <div v-for="purchase in purchases" :key="purchase.id" class="purchase-card mb-4">
                                <Card>
                                    <CardHeader class="purchase-header">
                                        <div class="purchase-info">
                                            <div class="purchase-number">
                                                <i class="feather-file-text"></i>
                                                <span>Orden: {{ purchase.number }}</span>
                                            </div>
                                            <div class="purchase-date">
                                                <i class="feather-calendar"></i>
                                                <span>{{ formatDate(purchase.created_at) }}</span>
                                            </div>
                                        </div>
                                        <div class="purchase-total">
                                            <span class="total-label">Total:</span>
                                            <span class="total-amount">{{ formatPrice(purchase.amount) }}</span>
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <div class="courses-list">
                                            <div v-for="item in purchase.items" :key="item.id" class="course-item">
                                                <div class="course-image">
                                                    <img v-if="item.course.image" :src="item.course.image" :alt="item.course.title" />
                                                    <div v-else class="course-image-placeholder">
                                                        <i class="feather-book-open"></i>
                                                    </div>
                                                </div>
                                                <div class="course-details">
                                                    <h4 class="course-title">{{ item.course.title }}</h4>
                                                    <p class="course-description">{{ item.course.short_description }}</p>
                                                    <div class="course-price">{{ formatPrice(item.unit_price) }}</div>
                                                </div>
                                                <div class="course-actions">
                                                    <Link :href="goToCourse(item.course.slug)" class="btn-view-course">
                                                        <span>Ver Materiales</span>
                                                        <i class="feather-arrow-right"></i>
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.rbt-page-banner-wrapper {
    position: relative;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    overflow: hidden;
}

.rbt-banner-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('/assets/images/bg/bg-image-10.jpg');
    background-size: cover;
    background-position: center;
    opacity: 0.1;
}

.rbt-banner-content {
    position: relative;
    z-index: 1;
}

/* Círculos animados en el fondo */
.banner-animated-circles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 0;
}

.animated-circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: float 20s infinite ease-in-out;
}

.circle-1 {
    width: 100px;
    height: 100px;
    top: 20%;
    left: 10%;
    animation-delay: 0s;
    animation-duration: 15s;
}

.circle-2 {
    width: 150px;
    height: 150px;
    top: 60%;
    right: 15%;
    animation-delay: 2s;
    animation-duration: 20s;
}

.circle-3 {
    width: 80px;
    height: 80px;
    top: 40%;
    right: 25%;
    animation-delay: 4s;
    animation-duration: 18s;
}

.circle-4 {
    width: 120px;
    height: 120px;
    bottom: 20%;
    left: 20%;
    animation-delay: 1s;
    animation-duration: 22s;
}

.circle-5 {
    width: 60px;
    height: 60px;
    top: 10%;
    right: 40%;
    animation-delay: 3s;
    animation-duration: 16s;
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0) translateX(0) scale(1);
        opacity: 0.3;
    }
    25% {
        transform: translateY(-30px) translateX(20px) scale(1.1);
        opacity: 0.5;
    }
    50% {
        transform: translateY(-60px) translateX(-20px) scale(0.9);
        opacity: 0.3;
    }
    75% {
        transform: translateY(-30px) translateX(-30px) scale(1.05);
        opacity: 0.4;
    }
}

.subtitle {
    display: inline-block;
    padding: 8px 20px;
    border-radius: 50px;
    font-weight: 600;
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.2) !important;
    backdrop-filter: blur(10px);
    font-size: 14px;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
}

.subtitle i {
    margin-right: 8px;
    color: #ffffff !important;
}

.title {
    color: #ffffff;
    font-size: 48px;
    font-weight: 800;
    margin-top: 20px;
}

.description {
    color: rgba(255, 255, 255, 0.9);
    font-size: 18px;
}

.purchases-container {
    margin-top: -80px;
}

.purchase-card {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.purchase-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.purchase-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px 24px !important;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.purchase-info {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
}

.purchase-number,
.purchase-date {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #666;
}

.purchase-number i,
.purchase-date i {
    color: #133a54;
    font-size: 16px;
}

.purchase-total {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.total-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    font-weight: 600;
}

.total-amount {
    font-size: 24px;
    font-weight: 800;
    color: #133a54;
}

.courses-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.course-item {
    display: flex;
    gap: 20px;
    padding: 20px;
    background: #ffffff;
    border: 2px solid #f1f1f1;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.course-item:hover {
    border-color: #133a54;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.1);
}

.course-image {
    flex-shrink: 0;
    width: 120px;
    height: 90px;
    border-radius: 8px;
    overflow: hidden;
}

.course-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.course-image-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.course-image-placeholder i {
    font-size: 32px;
    color: #adb5bd;
}

.course-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.course-title {
    font-size: 18px;
    font-weight: 700;
    color: #333;
    margin: 0;
}

.course-description {
    font-size: 14px;
    color: #666;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.course-price {
    font-size: 16px;
    font-weight: 700;
    color: #28a745;
}

.course-actions {
    flex-shrink: 0;
    display: flex;
    align-items: center;
}

.btn-view-course {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: #ffffff;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(19, 58, 84, 0.25);
    border: none;
    cursor: pointer;
}

.btn-view-course::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition:
        width 0.6s ease,
        height 0.6s ease;
}

.btn-view-course:hover::before {
    width: 300px;
    height: 300px;
}

.btn-view-course:hover {
    transform: translateX(4px);
    box-shadow: 0 8px 25px rgba(19, 58, 84, 0.35);
    color: #ffffff;
}

.btn-view-course span {
    position: relative;
    z-index: 1;
}

.btn-view-course i {
    position: relative;
    z-index: 1;
    transition: transform 0.3s ease;
    font-size: 16px;
}

.btn-view-course:hover i {
    transform: translateX(4px);
}

.btn-view-course i {
    font-size: 18px;
}

/* Botón para empty state - mismo estilo que Explorar */
.btn-explore-empty {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: #ffffff;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(19, 58, 84, 0.25);
}

.btn-explore-empty::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: translate(-50%, -50%);
    transition:
        width 0.6s ease,
        height 0.6s ease;
}

.btn-explore-empty:hover::before {
    width: 300px;
    height: 300px;
}

.btn-explore-empty:hover {
    transform: translateX(4px);
    box-shadow: 0 8px 25px rgba(19, 58, 84, 0.35);
    color: #ffffff;
}

.btn-explore-empty span {
    position: relative;
    z-index: 1;
}

.btn-explore-empty i {
    position: relative;
    z-index: 1;
    transition: transform 0.3s ease;
    font-size: 16px;
}

.btn-explore-empty:hover i {
    transform: translateX(4px);
}

.empty-state {
    padding: 60px 20px;
    margin-top: 80px;
}

.empty-icon {
    font-size: 80px;
    color: #dee2e6;
}

@media (max-width: 768px) {
    .title {
        font-size: 32px;
    }

    .course-item {
        flex-direction: column;
    }

    .course-image {
        width: 100%;
        height: 200px;
    }

    .purchase-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .purchase-total {
        align-items: flex-start;
    }

    .btn-view-course {
        width: 100%;
        justify-content: center;
    }
}
</style>
