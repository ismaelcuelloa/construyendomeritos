<script setup lang="ts">
import Breadcrumbs from '@/components/shared/Breadcrumbs.vue';
import SeoHead from '@/components/shared/SeoHead.vue';
import { Button } from '@/components/ui/button';
import Container from '@/components/ui/Container.vue';
import * as Cart from '@/composables/useCart';
import { useSeo } from '@/composables/useSeo';
import AppWebLayout from '@/layouts/AppWebLayout.vue';
import * as COURSE from '@/lib/course';
import ListModules from '@/features/courses/components/modules/ListModules.vue';
import type { User } from '@/types';
import { BreadcrumbItemType } from '@/types';
import { type Course } from '@/types/project';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const user = page.props.auth.user as User;

interface Props {
    course: Course;
}

const props = defineProps<Props>();

// SEO
const { generateStructuredData } = useSeo();
const seoData = computed(() => page.props.seo || {});

const courseSchema = computed(() => {
    const schema = {
        name: props.course.title,
        description: props.course.description,
        provider: {
            '@type': 'Organization',
            name: 'Construyendo Méritos con Excelencia',
            sameAs: window.location.origin,
        },
    };

    if (props.course.metadata?.banner) {
        schema['image'] = window.location.origin + '/' + props.course.metadata.banner;
    }

    if (props.course.price) {
        schema['offers'] = {
            '@type': 'Offer',
            price: props.course.price,
            priceCurrency: 'COP',
            availability: 'https://schema.org/InStock',
        };
    }

    return generateStructuredData('Course', schema);
});

// Intersection Observer para animaciones
onMounted(() => {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px',
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    const elements = document.querySelectorAll('.animate-on-scroll');
    elements.forEach((el) => observer.observe(el));

    // Si el usuario está autenticado y tiene items en el carrito, procesar checkout
    const cart = Cart.useCart();
    if (user && cart.items.value.length > 0) {
        // Verificar si acabamos de volver del registro
        const hasCheckoutFlag = sessionStorage.getItem('should_checkout');
        if (hasCheckoutFlag) {
            sessionStorage.removeItem('should_checkout');
            // Dar un pequeño delay para que la página cargue completamente
            setTimeout(() => {
                cart.checkout();
            }, 1000);
        }
    }

    onUnmounted(() => {
        elements.forEach((el) => observer.unobserve(el));
    });
});

const currentYear = computed(() => new Date().getFullYear());

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Home',
        href: '/',
    },
    {
        title: props.course.category?.title ?? '',
        href: '/categorias/' + props.course.category?.slug,
    },
    {
        title: props.course.title ?? '',
        href: '',
    },
];

const image_default = '/assets/images/others/thumbnail-placeholder.svg';
const image = ref(props.course.metadata?.banner ? '/' + props.course.metadata.banner : image_default);

const isSubscribed = computed(() => {
    return COURSE.isSubscribed(props.course, user);
});

const textCart = computed(() => {
    if (Cart.useCart().has(props.course.id)) {
        return 'Agregado al carrito';
    }
    return 'Agregar al carrito';
});

const handleAddToCart = () => {
    const cart = Cart.useCart();
    cart.addFromCourse(props.course);
    // Abrir el modal del carrito inmediatamente después de agregar
    cart.open();
};
</script>

<template>
    <SeoHead
        :title="seoData.title"
        :description="seoData.description"
        :image="seoData.image"
        :url="seoData.url"
        :type="seoData.type"
        :keywords="seoData.keywords"
        :structured-data="courseSchema"
    />
    <AppWebLayout>
        <div class="course-detail-banner">
            <!-- Animated Background -->
            <div class="banner-animated-bg">
                <div class="banner-shape shape-1"></div>
                <div class="banner-shape shape-2"></div>
                <div class="banner-shape shape-3"></div>
            </div>

            <Container>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="animate-on-scroll">
                            <Breadcrumbs :breadcrumbs="breadcrumbs" />
                        </div>

                        <div class="course-hero-content">
                            <div class="course-info animate-on-scroll">
                                <div class="course-badges">
                                    <div class="category-badge" v-if="course.category">
                                        <i class="feather-folder"></i>
                                        <span>{{ course.category.title }}</span>
                                    </div>
                                    <div class="status-badge" v-if="isSubscribed">
                                        <i class="feather-check-circle"></i>
                                        <span>Inscrito</span>
                                    </div>
                                </div>

                                <h1 class="course-title animate-on-scroll">{{ props.course.title }}</h1>
                                <p class="course-description animate-on-scroll">{{ props.course.description }}</p>

                                <div class="course-meta animate-on-scroll">
                                    <div class="meta-item">
                                        <i class="feather-book-open"></i>
                                        <span>{{ course.modules?.length ?? 0 }} Módulos</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="feather-users"></i>
                                        <span>{{ 350 + (course.subscriptions_count ?? 0) }} Estudiantes</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="feather-calendar"></i>
                                        <span>Actualizado {{ currentYear }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="course-image-wrapper animate-on-scroll">
                                <div class="course-image-container">
                                    <img :src="image" alt="Course image" class="course-hero-image" />
                                    <div class="image-overlay" v-if="!isSubscribed">
                                        <div class="price-badge">
                                            <span class="price">{{ course.price_formatted }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Container>
        </div>

        <div class="course-details-section">
            <Container>
                <div class="row g-5">
                    <div :class="isSubscribed ? 'col-lg-12 col-12' : 'col-lg-8 col-12'">
                        <div class="course-content-wrapper">
                            <!-- Course Description -->
                            <div class="course-description-section animate-on-scroll" v-if="course.metadata?.description">
                                <h3 class="description-title">Descripción del Material</h3>
                                <div class="description-content" v-html="course.metadata.description"></div>
                            </div>

                            <!-- Course Modules -->
                            <div class="content-card animate-on-scroll">
                                <div class="content-card-header">
                                    <div class="header-icon">
                                        <i class="feather-layers"></i>
                                    </div>
                                    <h3 class="content-title">Contenido del Material</h3>
                                    <div class="modules-count">
                                        <span>{{ course.modules?.length ?? 0 }} módulos</span>
                                    </div>
                                </div>
                                <div class="content-card-body">
                                    <ListModules :is-subscribed="isSubscribed" :modules="course.modules ?? []" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="!isSubscribed" class="col-lg-4">
                        <div class="course-sidebar-premium sticky-top animate-on-scroll">
                            <div class="sidebar-card">
                                <div class="price-section">
                                    <div class="price-header">
                                        <h4 class="price-title">Adquiere este material</h4>
                                        <div class="price-display">
                                            <span class="current-price">{{ course.price_formatted }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="action-section">
                                    <Button
                                        :disabled="Cart.useCart().has(course.id)"
                                        @click="handleAddToCart"
                                        class="btn-add-cart"
                                        variant="default"
                                        size="lg"
                                    >
                                        <i class="feather-shopping-cart"></i>
                                        <span>{{ textCart }}</span>
                                        <i class="feather-arrow-right"></i>
                                    </Button>
                                </div>

                                <div class="course-features">
                                    <h5 class="features-title">Este material incluye:</h5>
                                    <ul class="features-list">
                                        <li>
                                            <i class="feather-book-open"></i>
                                            <span>{{ course.modules?.length ?? 0 }} Módulos de estudio</span>
                                        </li>
                                        <li>
                                            <i class="feather-smartphone"></i>
                                            <span>Acceso desde cualquier dispositivo</span>
                                        </li>
                                        <li>
                                            <i class="feather-clock"></i>
                                            <span>Acceso hasta el día de las pruebas escritas</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="contact-section">
                                    <div class="contact-card">
                                        <div class="contact-icon">
                                            <i class="feather-help-circle"></i>
                                        </div>
                                        <div class="contact-content">
                                            <h6>¿Tienes dudas?</h6>
                                            <p>Contáctanos para más información</p>
                                            <a
                                                href="https://api.whatsapp.com/send/?phone=573054208045&text&type=phone_number&app_absent=0"
                                                class="contact-link"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                <i class="feather-phone"></i>
                                                +57 305 4208045
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Container>
        </div>

    </AppWebLayout>
</template>

<style scoped>
/* Course Detail Banner */
.course-detail-banner {
    background: url('/assets/images/bg/bg-merito.svg') center center / cover no-repeat;
    background-color: #ffe500;
    position: relative;
    overflow: hidden;
    padding: 80px 0 100px;
}

.banner-animated-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
}

.banner-shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    animation: float 20s infinite ease-in-out;
}

.banner-shape.shape-1 {
    width: 300px;
    height: 300px;
    top: -100px;
    right: 10%;
    animation-delay: 0s;
}

.banner-shape.shape-2 {
    width: 200px;
    height: 200px;
    bottom: -50px;
    left: 15%;
    animation-delay: 5s;
}

.banner-shape.shape-3 {
    width: 150px;
    height: 150px;
    top: 40%;
    right: 20%;
    animation-delay: 10s;
}

@keyframes float {
    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }
    25% {
        transform: translate(20px, -20px) scale(1.1);
    }
    50% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    75% {
        transform: translate(20px, 20px) scale(1.05);
    }
}

/* Breadcrumbs en blanco */
.course-detail-banner :deep(.page-list) {
    margin: 0 !important;
}

.course-detail-banner :deep(.page-list .rbt-breadcrumb-item) {
    color: #133a54 !important;
}

.course-detail-banner :deep(.page-list .rbt-breadcrumb-item a) {
    color: #133a54 !important;
    transition: color 0.3s ease;
}

.course-detail-banner :deep(.page-list .rbt-breadcrumb-item a:hover) {
    color: #0a2135 !important;
}

/* Course Hero Content */
.course-hero-content {
    display: flex;
    align-items: flex-start;
    gap: 60px;
    margin-top: 40px;
    position: relative;
    z-index: 1;
}

.course-info {
    flex: 1;
}

.course-badges {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.category-badge,
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    backdrop-filter: blur(10px);
}

.category-badge {
    background: rgba(255, 255, 255, 0.2);
    color: #133a54;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.status-badge {
    background: rgba(255, 255, 255, 0.95);
    color: #28a745;
    border: 1px solid rgba(40, 167, 69, 0.3);
}

.course-title {
    font-size: 3.5rem;
    font-weight: 900;
    color: #133a54;
    line-height: 1.2;
    margin-bottom: 20px;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    letter-spacing: -1px;
}

.course-description {
    font-size: 18px;
    color: #133a54;
    line-height: 1.7;
    margin-bottom: 30px;
}

.course-meta {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #133a54;
    font-weight: 600;
}

.meta-item i {
    font-size: 18px;
    color: #133a54;
}

/* Course Image */
.course-image-wrapper {
    flex: 0 0 400px;
}

.course-image-container {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    transform: perspective(1000px) rotateY(-5deg) rotateX(2deg);
    transition: transform 0.3s ease;
}

.course-image-container:hover {
    transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
}

.course-hero-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
}

.image-overlay {
    position: absolute;
    top: 20px;
    right: 20px;
}

.price-badge {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 12px 20px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.price {
    font-size: 24px;
    font-weight: 900;
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Course Details Section */
.course-details-section {
    padding: 80px 0;
    background: #ffffff;
}

.course-content-wrapper {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

.course-description-section {
    margin-bottom: 50px;
}

.description-title {
    font-size: 28px;
    font-weight: 800;
    color: #151515;
    margin-bottom: 20px;
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.description-content {
    font-size: 16px;
    line-height: 1.8;
    color: #444;
}

.description-content h1,
.description-content h2,
.description-content h3 {
    color: #151515;
    font-weight: 700;
    margin: 30px 0 15px 0;
}

.description-content h1 {
    font-size: 24px;
}
.description-content h2 {
    font-size: 22px;
}
.description-content h3 {
    font-size: 20px;
}

.description-content p {
    margin-bottom: 15px;
}

.description-content ul,
.description-content ol {
    margin: 15px 0;
    padding-left: 25px;
}

.description-content li {
    margin-bottom: 8px;
}

.description-content strong {
    color: #133a54;
    font-weight: 700;
}

.description-content a {
    color: #133a54;
    text-decoration: none;
    font-weight: 600;
}

.description-content a:hover {
    text-decoration: underline;
}

.content-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    border: 2px solid rgba(19, 58, 84, 0.05);
    overflow: hidden;
    transition: all 0.3s ease;
}

.content-card:hover {
    box-shadow: 0 16px 50px rgba(0, 0, 0, 0.12);
    border-color: rgba(19, 58, 84, 0.1);
}

.content-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 30px 40px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.05) 0%, rgba(19, 58, 84, 0.02) 100%);
    border-bottom: 1px solid rgba(19, 58, 84, 0.1);
}

.header-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 22px;
}

.content-title {
    font-size: 24px;
    font-weight: 800;
    color: #151515;
    margin: 0;
    flex: 1;
}

.modules-count {
    background: rgba(19, 58, 84, 0.1);
    color: #133a54;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
}

.content-card-body {
    padding: 40px;
}

/* Sidebar Premium */
.course-sidebar-premium {
    position: sticky;
    top: 20px;
}

.sidebar-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
    border: 2px solid rgba(19, 58, 84, 0.08);
    overflow: hidden;
}

.price-section {
    padding: 30px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.05) 0%, rgba(19, 58, 84, 0.02) 100%);
    text-align: center;
}

.price-title {
    font-size: 18px;
    font-weight: 700;
    color: #333;
    margin-bottom: 16px;
}

.current-price {
    font-size: 36px;
    font-weight: 900;
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.action-section {
    padding: 30px;
}

.btn-add-cart {
    width: 100%;
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%) !important;
    color: #ffffff !important;
    border: none !important;
    padding: 16px 32px !important;
    font-weight: 700 !important;
    font-size: 16px !important;
    border-radius: 12px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 12px !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 8px 25px rgba(19, 58, 84, 0.3) !important;
}

.btn-add-cart:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 12px 35px rgba(19, 58, 84, 0.4) !important;
}

.btn-add-cart:disabled {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
    cursor: not-allowed !important;
}

.course-features {
    padding: 30px;
    border-top: 1px solid rgba(19, 58, 84, 0.1);
}

.features-title {
    font-size: 16px;
    font-weight: 700;
    color: #333;
    margin-bottom: 20px;
}

.features-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.features-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    color: #666;
    font-weight: 500;
}

.features-list i {
    color: #133a54;
    font-size: 16px;
    width: 16px;
}

.contact-section {
    padding: 30px;
    background: #f8f9fa;
}

.contact-card {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.contact-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 18px;
}

.contact-content h6 {
    font-size: 14px;
    font-weight: 700;
    color: #333;
    margin-bottom: 8px;
}

.contact-content p {
    font-size: 13px;
    color: #666;
    margin-bottom: 12px;
}

.contact-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #133a54;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
}

.contact-link:hover {
    color: #133a54;
}

/* Animaciones */
.animate-on-scroll {
    opacity: 0;
    transform: translateY(30px);
    transition:
        opacity 0.8s ease-out,
        transform 0.8s ease-out;
}

.animate-on-scroll.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Responsive */
@media (max-width: 768px) {
    .course-detail-banner {
        padding: 60px 0 80px;
    }

    .course-hero-content {
        flex-direction: column;
        gap: 40px;
    }

    .course-image-wrapper {
        flex: none;
        width: 100%;
    }

    .course-image-container {
        transform: none;
    }

    .course-title {
        font-size: 2.5rem;
    }

    .course-meta {
        gap: 20px;
    }

    .content-card-header,
    .content-card-body {
        padding: 20px;
    }

    .price-section,
    .action-section,
    .course-features,
    .contact-section {
        padding: 20px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .animate-on-scroll,
    .banner-shape {
        animation: none;
        opacity: 1;
        transform: none;
    }
}
</style>
