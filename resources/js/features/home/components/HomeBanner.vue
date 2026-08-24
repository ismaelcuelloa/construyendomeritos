<script setup lang="ts">
// import AppLogin from '@/features/auth/components/AppLogin.vue';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

import type { Category } from '@/types/project';

interface Props {
    categories: Category[];
}

defineProps<Props>();

const currentYear = computed(() => new Date().getFullYear());
// const showLogin = ref(false);

onMounted(() => {
    // Inicialización del Swiper (Efecto cartas)
    // @ts-expect-error - Swiper is a global variable from external script
    if (window.Swiper) {
        // @ts-expect-error - Swiper constructor is not typed
        new Swiper('.banner-swiper-active', {
            effect: 'cards',
            grabCursor: true,
            loop: true,
            pagination: {
                el: '.rbt-swiper-pagination',
                clickable: true,
            },
        });
    }
});
</script>

<template>
    <div class="rbt-banner-area rbt-banner-1">
        <!-- Fondo animado -->
        <div class="banner-animated-background">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>
            <div class="floating-shape shape-4"></div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 pb--120 pt--70">
                    <div class="content">
                        <div class="inner">
                            <div class="rbt-new-badge rbt-new-badge-one animate-fade-in-down">
                                <span class="rbt-new-badge-icon pulse-icon">🎓</span>
                                Material de estudio - Procuraduría General de la Nación 2026
                            </div>

                            <h1 class="title animate-fade-in-up delay-1">
                                Construyendo Méritos con <span class="text-gradient">Excelencia</span>
                            </h1>

                            <p class="description animate-fade-in-up delay-2">
                                Encuentra simulacros, guías de estudio y material actualizado para prepararte de manera efectiva
                                y aumentar tus posibilidades de éxito en el concurso de la
                                <strong>Procuraduría General de la Nación.</strong>
                            </p>

                            <div class="slider-btn animate-fade-in-up delay-4">
                                <Link href="/cursos">
                                    <Button variant="default" size="lg" class="group premium-btn overflow-hidden shadow-lg">
                                        <span class="btn-text">Ver Materiales de Estudio</span>
                                        <i class="feather-arrow-right group-hover-move ms-2"></i>
                                    </Button>
                                </Link>
                                <Link href="/login">
                                    <Button variant="outline" size="lg" class="group login-btn overflow-hidden shadow-lg">
                                        <span class="btn-text">Iniciar Sesión</span>
                                        <i class="feather-log-in group-hover-move ms-2"></i>
                                    </Button>
                                </Link>
                            </div>

                            <!-- Características destacadas -->
                            <div class="features-grid animate-fade-in-up delay-5">
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="feather-check-circle"></i>
                                    </div>
                                    <span>Actualizado {{ currentYear }}</span>
                                </div>
                                <div class="feature-item">
                                    <div class="feature-icon">
                                        <i class="feather-users"></i>
                                    </div>
                                    <span>+5000 Estudiantes</span>
                                </div>
                            </div>
                        </div>

                        <div class="shape-wrapper animate-fade-in-right delay-2" id="scene">
                            <img src="/assets/images/banner/Diseño sin título (23).png" alt="Hero" class="hero-image" />
                            <div class="hero-bg-shape-1 layer" data-depth="0.4">
                                <img src="/assets/images/shape/shape-01.png" alt="Shape" />
                            </div>
                        </div>

                        <div class="banner-card pb--60 mb--50 swiper rbt-dot-bottom-center banner-swiper-active animate-fade-in-up delay-5">
                            <div class="swiper-wrapper">
                                <div v-for="category in categories" :key="category.id" class="swiper-slide">
                                    <div class="rbt-card variation-01 rbt-hover card-hover-effect shadow-lg">
                                        <div class="rbt-card-img">
                                            <Link :href="`/categorias/${category.slug}`">
                                                <img v-if="category.image && category.image.url" :src="category.image.url" :alt="category.title" />
                                                <img v-else src="/assets/images/logo/logo-color.png" :alt="category.title" />
                                            </Link>
                                        </div>
                                        <div class="rbt-card-body">
                                            <ul class="rbt-meta" v-if="category.courses">
                                                <li><i class="feather-book"></i>{{ category.courses.length }} Materiales</li>
                                            </ul>
                                            <h4 class="rbt-card-title">
                                                <Link :href="`/categorias/${category.slug}`">{{ category.title }}</Link>
                                            </h4>
                                            <p class="rbt-card-text">{{ category.description }}</p>

                                            <div class="rbt-review">
                                                <div class="rating">
                                                    <i v-for="i in 5" :key="i" class="fas fa-star text-warning"></i>
                                                </div>
                                                <span class="rating-count"> (15 Valoraciones)</span>
                                            </div>

                                            <div class="rbt-card-bottom">
                                                <Link class="rbt-btn-link" :href="`/categorias/${category.slug}`">
                                                    Explorar <i class="feather-arrow-right"></i>
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="rbt-swiper-pagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Login -->
    <!-- <AppLogin v-model:show="showLogin" :static="false" /> -->
</template>

<style scoped>
/* Fondo del Banner - Degradado Premium Naranja */
.rbt-banner-area.rbt-banner-1 {
    background: linear-gradient(180deg, rgba(19, 58, 84, 0.06) 0%, rgba(26, 90, 128, 0.03) 50%, rgba(19, 58, 84, 0.04) 100%);
    position: relative;
}

.rbt-banner-area.rbt-banner-1::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(19, 58, 84, 0.2), transparent);
}

/* Gradiente naranja para el texto del título */
.text-gradient {
    background: linear-gradient(90deg, #133a54 0%, #1a5a80 100%);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 800;
}

/* Badge con colores naranja - Premium Design */
.rbt-new-badge.rbt-new-badge-one {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.15) 0%, rgba(26, 90, 128, 0.1) 100%);
    border: 1.5px solid #133a54;
    color: #1a5a80;
    font-weight: 700;
    padding: 10px 24px;
    border-radius: 25px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 25px;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(19, 58, 84, 0.15);
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    letter-spacing: 0.5px;
}

.rbt-new-badge.rbt-new-badge-one:hover {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.25) 0%, rgba(26, 90, 128, 0.15) 100%);
    box-shadow: 0 6px 25px rgba(19, 58, 84, 0.25);
    transform: translateY(-2px);
}

.rbt-new-badge-icon {
    font-size: 20px;
    filter: drop-shadow(0 2px 4px rgba(19, 58, 84, 0.2));
}

/* Texto destacado en descripción */
.description strong {
    color: #1a5a80;
}

/* Botón principal mejorado - Diseño Premium */
.premium-btn {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: #ffffff;
    border: none;
    font-weight: 700;
    padding: 14px 36px;
    border-radius: 8px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(19, 58, 84, 0.3);
    letter-spacing: 0.5px;
    font-size: 15px;
}

.premium-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.6s ease;
}

.premium-btn:hover::before {
    left: 100%;
}

.premium-btn:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(19, 58, 84, 0.4);
    letter-spacing: 0.8px;
}

.premium-btn:active {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.3);
}

/* TARJETAS MANTENIDAS COMO EL ORIGINAL - Solo colores cambiados */

/* Estrellas de valoración */
.rating i.text-warning {
    color: #133a54 !important;
}

/* Precios con color naranja */
.current-price {
    font-weight: 700;
    color: #133a54; /* Cambiado de #2f57ef a naranja */
    font-size: 18px;
}

.off-price {
    text-decoration: line-through;
    font-size: 14px;
    margin-left: 8px;
    opacity: 0.6;
}

/* Enlace "Saber más" con color naranja */
.rbt-btn-link {
    color: #133a54; /* Color naranja agregado */
    transition: color 0.3s ease;
}

.rbt-btn-link:hover {
    color: #1a5a80; /* Naranja claro al hover */
}

.rbt-btn-link i {
    transition: transform 0.3s ease;
}

.rbt-btn-link:hover i {
    transform: translateX(4px);
}

/* Badge de descuento con color naranja */
.rbt-badge-3.bg-white {
    background: linear-gradient(90deg, #133a54 0%, #1a5a80 100%) !important;
    color: #151515 !important;
    font-weight: 600;
}

/* Iconos en meta información */
.rbt-meta i {
    color: #133a54; /* Color naranja para iconos */
}

/* Paginación del swiper con color naranja */
:deep(.swiper-pagination-bullet-active) {
    background: #133a54 !important; /* Color naranja para la paginación activa */
}

/* Mejora de la tarjeta (manteniendo el diseño original) */
.rbt-card {
    border-radius: 15px;
    overflow: hidden;
    background: white;
}

/* ===== NUEVAS ANIMACIONES Y MEJORAS ===== */

/* Fondo animado */
.banner-animated-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    overflow: hidden;
    pointer-events: none;
}

.floating-shape {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.08), rgba(26, 90, 128, 0.05));
    animation: float-around 20s infinite ease-in-out;
}

.floating-shape.shape-1 {
    width: 400px;
    height: 400px;
    top: -100px;
    right: 5%;
    animation-delay: 0s;
}

.floating-shape.shape-2 {
    width: 250px;
    height: 250px;
    bottom: -50px;
    left: 10%;
    animation-delay: 5s;
}

.floating-shape.shape-3 {
    width: 150px;
    height: 150px;
    top: 50%;
    right: 25%;
    animation-delay: 10s;
}

.floating-shape.shape-4 {
    width: 100px;
    height: 100px;
    bottom: 30%;
    left: 30%;
    animation-delay: 15s;
}

@keyframes float-around {
    0%,
    100% {
        transform: translate(0, 0) rotate(0deg);
    }
    25% {
        transform: translate(30px, -30px) rotate(90deg);
    }
    50% {
        transform: translate(-20px, 30px) rotate(180deg);
    }
    75% {
        transform: translate(20px, 20px) rotate(270deg);
    }
}

/* Animaciones de entrada */
.animate-fade-in-down {
    animation: fadeInDown 0.8s ease-out forwards;
    opacity: 0;
}

.animate-fade-in-up {
    animation: fadeInUp 1s ease-out forwards;
    opacity: 0;
}

.animate-fade-in-right {
    animation: fadeInRight 1s ease-out forwards;
    opacity: 0;
}

.delay-1 {
    animation-delay: 0.2s;
}
.delay-2 {
    animation-delay: 0.4s;
}
.delay-3 {
    animation-delay: 0.6s;
}
.delay-4 {
    animation-delay: 0.8s;
}
.delay-5 {
    animation-delay: 1s;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Icono pulsante */
.pulse-icon {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%,
    100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* Características destacadas */
.features-grid {
    display: flex;
    gap: 24px;
    margin-top: 40px;
    flex-wrap: wrap;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.9);
    padding: 12px 20px;
    border-radius: 50px;
    box-shadow: 0 4px 15px rgba(19, 58, 84, 0.1);
    transition: all 0.3s ease;
    border: 1.5px solid rgba(19, 58, 84, 0.1);
}

.feature-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(19, 58, 84, 0.2);
    border-color: rgba(19, 58, 84, 0.3);
}

.feature-icon {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1), rgba(26, 90, 128, 0.15));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #133a54;
    font-size: 16px;
}

.feature-item span {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

/* Mejoras en las cards */
.card-hover-effect {
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
}

.card-hover-effect:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(19, 58, 84, 0.2) !important;
}

.rbt-card-img {
    position: relative;
    overflow: hidden;
}

.card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.9), rgba(26, 90, 128, 0.8));
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.card-overlay i {
    color: white;
    font-size: 32px;
    transform: scale(0.8);
    transition: transform 0.3s ease;
}

.rbt-card-img:hover .card-overlay {
    opacity: 1;
}

.rbt-card-img:hover .card-overlay i {
    transform: scale(1);
}

/* Contenedor de botones */
.slider-btn {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-top: 32px;
}

/* Botón de Login */
.login-btn {
    background: white;
    color: #133a54;
    border: 2px solid #133a54;
    font-weight: 700;
    padding: 14px 36px;
    border-radius: 8px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(19, 58, 84, 0.15);
    letter-spacing: 0.5px;
    font-size: 15px;
}

.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    transition: left 0.4s ease;
    z-index: -1;
}

.login-btn:hover::before {
    left: 0;
}

.login-btn:hover {
    color: white;
    border-color: #133a54;
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(19, 58, 84, 0.3);
}

.login-btn:active {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(19, 58, 84, 0.2);
}

/* Responsive para los botones */
@media (max-width: 768px) {
    .slider-btn {
        flex-direction: column;
        align-items: stretch;
    }

    .slider-btn :deep(a),
    .slider-btn button {
        width: 100%;
    }
}
</style>
