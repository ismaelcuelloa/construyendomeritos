<script setup lang="ts">
import AppListCategories from '@/features/catalog/components/AppListCategories.vue';
import { type Category } from '@/types/project';
import { Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';

interface Props {
    categories?: Category[];
}

defineProps<Props>();

// Intersection Observer para animaciones de scroll
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

    const elements = document.querySelectorAll('.animate-on-scroll, .animate-on-scroll-stagger');
    elements.forEach((el) => observer.observe(el));

    onUnmounted(() => {
        elements.forEach((el) => observer.unobserve(el));
    });
});
</script>

<template>
    <!-- Start Course Area -->
    <div class="rbt-course-area bg-color-white mb--20">
        <div class="container">
            <div class="row mb--60">
                <div class="col-lg-12">
                    <div class="section-title animate-on-scroll text-center">
                        <div class="badge-container">
                            <span class="subtitle-badge">⭐ Más populares</span>
                        </div>
                        <h2 class="title-premium">
                            Materiales de estudio que nuestros <br />
                            estudiantes <span class="text-gradient">recomiendan.</span>
                        </h2>
                        <p class="section-description">Descubre el contenido más valorado por nuestra comunidad</p>
                    </div>
                </div>
            </div>
            <!-- Start Card Area -->
            <div class="categories-wrapper animate-on-scroll-stagger">
                <AppListCategories :categories="categories" />
            </div>
            <!-- End Card Area -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="load-more-btn mt--60 animate-on-scroll text-center">
                        <Link href="/cursos">
                            <button class="premium-btn-load">
                                <span>Ver más materiales de estudio</span>
                                <i class="feather-arrow-right ms-2"></i>
                            </button>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Course Area -->
</template>

<style scoped>
/* Gradiente naranja para el texto del título */
.text-gradient {
    background: linear-gradient(90deg, #133a54 0%, #1a5a80 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 800;
}

/* Subtitle Badge - Premium Design */
.subtitle-badge {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.15) 0%, rgba(26, 90, 128, 0.1) 100%);
    color: #1a5a80;
    font-weight: 700;
    padding: 8px 20px;
    border-radius: 25px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(19, 58, 84, 0.15);
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    font-size: 13px;
    letter-spacing: 0.5px;
    position: relative;
    border: 1.5px solid transparent;
    background-clip: padding-box;
    animation:
        float-badge 3s ease-in-out infinite,
        pulse-badge 2s ease-in-out infinite;
}

.subtitle-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.25);
}

.subtitle-badge::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 25px;
    padding: 1.5px;
    background: linear-gradient(90deg, #133a54 0%, #1a5a80 50%, #133a54 100%);
    background-size: 200% 100%;
    animation: gradientBorder 3s ease infinite;
    -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
}

@keyframes gradientBorder {
    0%,
    100% {
        background-position: 0% center;
    }
    50% {
        background-position: 100% center;
    }
}

@keyframes float-badge {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-5px);
    }
}

@keyframes pulse-badge {
    0%,
    100% {
        box-shadow: 0 4px 15px rgba(19, 58, 84, 0.15);
    }
    50% {
        box-shadow: 0 6px 25px rgba(19, 58, 84, 0.3);
    }
}

/* Container for badge with proper overflow handling */
.badge-container {
    padding: 15px 0;
    margin-bottom: 10px;
    overflow: visible;
}

/* Ensure section-title doesn't clip animations */
.section-title {
    overflow: visible !important;
}

/* Title Premium */
.title-premium {
    font-size: 2.5rem;
    font-weight: 800;
    color: #151515;
    letter-spacing: -0.5px;
    line-height: 1.2;
}

/* Botón Load More - Premium Design */
.premium-btn-load {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: #ffffff;
    border: none;
    font-weight: 700;
    padding: 16px 48px;
    border-radius: 8px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(19, 58, 84, 0.3);
    letter-spacing: 0.5px;
    font-size: 15px;
    min-height: 50px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
}

.premium-btn-load::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.6s ease;
}

.premium-btn-load:hover::before {
    left: 100%;
}

.premium-btn-load:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(19, 58, 84, 0.4);
    letter-spacing: 0.8px;
}

.premium-btn-load:active {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.3);
}

.premium-btn-load i {
    transition: transform 0.3s ease;
    font-size: 16px;
}

.premium-btn-load:hover i {
    transform: translateX(4px);
}

/* Animaciones de scroll */
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

.animate-on-scroll-stagger {
    opacity: 0;
    transform: translateY(30px);
    transition:
        opacity 0.8s ease-out,
        transform 0.8s ease-out;
}

.animate-on-scroll-stagger.visible {
    opacity: 1;
    transform: translateY(0);
}

.animate-on-scroll-stagger .category-item:nth-child(1) {
    transition-delay: 0.1s;
}

.animate-on-scroll-stagger .category-item:nth-child(2) {
    transition-delay: 0.2s;
}

.animate-on-scroll-stagger .category-item:nth-child(3) {
    transition-delay: 0.3s;
}

.animate-on-scroll-stagger .category-item:nth-child(4) {
    transition-delay: 0.4s;
}

.animate-on-scroll-stagger .category-item:nth-child(5) {
    transition-delay: 0.5s;
}

.animate-on-scroll-stagger .category-item:nth-child(6) {
    transition-delay: 0.6s;
}

.section-description {
    font-size: 16px;
    color: #666;
    margin-bottom: 30px;
    text-align: center;
    font-weight: 400;
}

/* Responsive animations */
@media (prefers-reduced-motion: reduce) {
    .animate-on-scroll,
    .animate-on-scroll-stagger {
        opacity: 1;
        transform: none;
        transition: none;
    }
}
</style>
