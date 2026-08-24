<script setup lang="ts">
import Breadcrumbs from '@/components/shared/Breadcrumbs.vue';
import SeoHead from '@/components/shared/SeoHead.vue';
import AppWebLayout from '@/layouts/AppWebLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { type Category } from '@/types/project';
import { router, usePage, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

interface Props {
    categories: Category[];
}

defineProps<Props>();

const page = usePage();
const seoData = computed(() => page.props.seo || {});

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Home',
        href: '/',
    },
    {
        title: 'Material de Estudio',
        href: '/cursos',
    },
];

const searchValue = ref('');
let searchTimeout: number;

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    searchValue.value = urlParams.get('search') || '';
});

// Búsqueda con debounce sobre categorías
watch(searchValue, (newValue) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = window.setTimeout(() => {
        router.get(
            window.location.pathname,
            {
                search: newValue,
            },
            {
                preserveState: true,
                preserveScroll: false,
                only: ['categories'],
            },
        );
    }, 500);
});

const handleSearch = (e: Event) => {
    e.preventDefault();
};

const imageDefault = '/assets/images/others/thumbnail-placeholder.svg';
const categoryUrl = (slug: string) => `/categorias/${slug}`;
</script>

<template>
    <SeoHead :title="seoData.title" :description="seoData.description" :url="page.url" :keywords="seoData.keywords" />
    <AppWebLayout>
        <div class="rbt-page-banner-wrapper courses-banner">
            <div class="banner-animated-bg">
                <div class="banner-shape shape-1"></div>
                <div class="banner-shape shape-2"></div>
                <div class="banner-shape shape-3"></div>
            </div>
            <div class="rbt-banner-content">
                <div class="rbt-banner-content-top">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <Breadcrumbs :breadcrumbs="breadcrumbs" />

                                <div class="title-wrapper-premium">
                                    <div class="title-content">
                                        <div class="welcome-tag">
                                            <i class="feather-grid"></i>
                                            <span>Categorías disponibles</span>
                                        </div>
                                        <h1 class="title-premium mb--0">Material de Estudio</h1>
                                        <p class="subtitle-premium">
                                            <i class="feather-folder"></i>
                                            Explora por categorías y encuentra tu preparación ideal
                                        </p>
                                    </div>
                                    <div class="stats-wrapper">
                                        <div class="badge-premium">
                                            <div class="badge-icon">
                                                <i class="feather-layers"></i>
                                            </div>
                                            <div class="badge-content">
                                                <span class="badge-number">{{ categories.length }}</span>
                                                <span class="badge-text">Categorías</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rbt-course-top-wrapper-premium mt--40">
                    <div class="container">
                        <div class="filter-bar-premium">
                            <div class="filter-left">
                                <div class="results-info-premium">
                                    <div class="info-icon">
                                        <i class="feather-layers"></i>
                                    </div>
                                    <div class="info-text">
                                        <span class="info-count"
                                            ><strong>{{ categories.length }}</strong> categorías de materiales disponibles</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="search-box-premium">
                                <form @submit="handleSearch" class="search-form-premium">
                                    <div class="search-icon-left">
                                        <i class="feather-search"></i>
                                    </div>
                                    <input v-model="searchValue" type="text" placeholder="Buscar categoría..." />
                                    <button type="submit" class="search-btn-premium">
                                        <span>Buscar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rbt-section-overlayping-top rbt-section-gapBottom">
            <div class="inner">
                <div class="container">
                    <div v-if="categories.length > 0" class="row g-5">
                        <div v-for="category in categories" :key="category.id" class="col-lg-4 col-md-6 col-12">
                            <div class="rbt-card variation-01 rbt-hover category-card-premium">
                                <div class="rbt-card-img">
                                    <Link :href="categoryUrl(category.slug)">
                                        <img :src="category.image?.url ?? imageDefault" :alt="category.title" />
                                        <div class="image-overlay">
                                            <div class="overlay-badge">
                                                <i class="feather-folder"></i>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                                <div class="rbt-card-body">
                                    <div class="category-badge">
                                        <i class="feather-book-open"></i>
                                        <span>{{ category.courses_count ?? 0 }} Materiales</span>
                                        <span v-if="category.subcategories_count" class="dot">•</span>
                                        <span v-if="category.subcategories_count">{{ category.subcategories_count }} Subcategorías</span>
                                    </div>

                                    <h4 class="rbt-card-title">
                                        <Link :href="categoryUrl(category.slug)">
                                            {{ category.title }}
                                        </Link>
                                    </h4>

                                    <p v-if="category.description" class="rbt-card-text">{{ category.description }}</p>
                                    <p v-else class="rbt-card-text muted">Explora los materiales de {{ category.title }} preparados para tu convocatoria.</p>

                                    <div class="card-footer-premium">
                                        <Link class="btn-explore" :href="categoryUrl(category.slug)">
                                            <span>Explorar</span>
                                            <i class="feather-arrow-right"></i>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="no-results-container">
                        <h3 class="no-results-title">No hay categorías</h3>
                        <p class="no-results-text">No se encontraron categorías para tu búsqueda. Intenta con otros términos.</p>
                        <button @click="searchValue = ''" class="btn-clear-search"><i class="feather-x-circle"></i> Limpiar búsqueda</button>
                    </div>
                </div>
            </div>
        </div>
    </AppWebLayout>
</template>

<style scoped>
/* Pasar estilos existentes + nuevos para categorías */
.courses-banner :deep(.page-list) {
    margin: 0 !important;
}
.courses-banner :deep(.page-list .rbt-breadcrumb-item) {
    color: #133a54 !important;
}
.courses-banner :deep(.page-list .rbt-breadcrumb-item a) {
    color: #133a54 !important;
    transition: color 0.3s ease;
}
.courses-banner :deep(.page-list .rbt-breadcrumb-item a:hover) {
    color: #0a2135 !important;
}
.courses-banner :deep(.page-list .rbt-breadcrumb-item.active) {
    color: #0a2135 !important;
    font-weight: 600;
}
.courses-banner :deep(.page-list .icon-right i) {
    color: rgba(17, 54, 79, 0.5) !important;
}
.courses-banner {
    padding-bottom: 160px !important;
    background: url('/assets/images/bg/bg-merito.svg') center center / cover no-repeat;
    background-color: #ffe500;
    position: relative;
    overflow: hidden;
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
.title-wrapper-premium {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 40px;
    flex-wrap: wrap;
    margin-top: 40px;
    position: relative;
    z-index: 1;
}
.title-content {
    flex: 1;
}
.welcome-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 10px 20px;
    border-radius: 50px;
    margin-bottom: 20px;
    animation: fadeInDown 0.8s ease;
}
.welcome-tag i {
    color: #133a54;
    font-size: 18px;
}
.welcome-tag span {
    color: #133a54;
    font-weight: 600;
    font-size: 14px;
    letter-spacing: 0.5px;
}
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.title-premium {
    font-size: 56px !important;
    font-weight: 900 !important;
    color: #133a54 !important;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    letter-spacing: -1.5px;
    margin-bottom: 16px !important;
    line-height: 1.1 !important;
    animation: fadeInUp 1s ease;
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
.subtitle-premium {
    font-size: 19px;
    color: #133a54;
    margin: 0;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: fadeInUp 1.2s ease;
}
.subtitle-premium i {
    font-size: 20px;
}
.stats-wrapper {
    display: flex;
    gap: 16px;
    animation: fadeInRight 1s ease;
}
@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
.badge-premium {
    background: rgba(255, 255, 255, 0.98);
    padding: 12px 18px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    min-width: 140px;
}
.badge-premium:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
}
.badge-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(19, 58, 84, 0.15) 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.badge-icon i {
    font-size: 20px;
    color: #133a54;
}
.badge-content {
    display: flex;
    flex-direction: row;
    gap: 6px;
    align-items: center;
    justify-content: center;
}
.badge-number {
    font-size: 18px;
    font-weight: 900;
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1;
}
.badge-text {
    font-size: 18px;
    font-weight: 700;
    color: #133a54;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1;
}
.rbt-course-top-wrapper-premium {
    margin-top: -80px;
    position: relative;
    z-index: 10;
}
.filter-bar-premium {
    background: #ffffff;
    padding: 32px 40px;
    border-radius: 20px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 32px;
    flex-wrap: wrap;
    border: 2px solid rgba(19, 58, 84, 0.08);
    transition: all 0.3s ease;
}
.filter-bar-premium:hover {
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
}
.filter-left {
    flex: 0 0 auto;
}
.results-info-premium {
    display: flex;
    align-items: center;
    gap: 16px;
}
.info-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(19, 58, 84, 0.08) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.info-icon i {
    color: #133a54;
    font-size: 22px;
}
.info-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.info-count {
    font-size: 16px;
    color: #133a54;
    font-weight: 500;
}
.info-count strong {
    color: #133a54;
    font-weight: 800;
}
.search-box-premium {
    flex: 1;
    min-width: 350px;
    max-width: 500px;
}
.search-form-premium {
    position: relative;
    display: flex;
    align-items: center;
    background: rgba(19, 58, 84, 0.04);
    border: 2px solid rgba(19, 58, 84, 0.15);
    border-radius: 14px;
    transition: all 0.3s ease;
    overflow: hidden;
}
.search-form-premium:focus-within {
    border-color: #133a54;
    background: #ffffff;
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.12);
}
.search-icon-left {
    position: absolute;
    left: 20px;
    color: #133a54;
    font-size: 20px;
    pointer-events: none;
    z-index: 2;
}
.search-form-premium input {
    flex: 1;
    padding: 16px 140px 16px 56px;
    border: none;
    background: transparent;
    font-size: 15px;
    color: #333;
    font-weight: 500;
}
.search-form-premium input::placeholder {
    color: #999;
}
.search-form-premium input:focus {
    outline: none;
}
.search-btn-premium {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: #ffffff;
    border: none;
    padding: 0 28px;
    border-radius: 0 12px 12px 0;
    font-weight: 700;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: -2px 0 8px rgba(19, 58, 84, 0.15);
}
.search-btn-premium:hover {
    background: linear-gradient(135deg, #1a5a80 0%, #133a54 100%);
    box-shadow: -4px 0 12px rgba(19, 58, 84, 0.25);
}

/* Cards categorías */
.category-card-premium {
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
    border-radius: 20px;
    overflow: hidden;
    background: #ffffff;
    border: 2px solid transparent;
    box-shadow: 0 8px 30px rgba(19, 58, 84, 0.08);
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
}
.category-card-premium:hover {
    box-shadow: 0 20px 60px rgba(19, 58, 84, 0.2);
    transform: translateY(-8px);
    border-color: rgba(19, 58, 84, 0.08);
}
.rbt-card-img {
    position: relative;
    overflow: hidden;
    height: 210px;
    background: linear-gradient(135deg, #e8eef3 0%, #f1f5f9 100%);
}
.rbt-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.category-card-premium:hover .rbt-card-img img {
    transform: scale(1.08);
}
.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.55) 100%);
    opacity: 0;
    transition: opacity 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.category-card-premium:hover .image-overlay {
    opacity: 1;
}
.overlay-badge {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #133a54, #1a5a80);
    display: flex;
    align-items: center;
    justify-content: center;
    transform: scale(0.8);
    transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1);
}
.category-card-premium:hover .overlay-badge {
    transform: scale(1);
}
.overlay-badge i {
    color: #ffffff;
    font-size: 22px;
}
.rbt-card-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
.category-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.08) 0%, rgba(26, 90, 128, 0.05) 100%);
    color: #133a54;
    padding: 8px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 14px;
    width: fit-content;
    border: 1px solid rgba(19, 58, 84, 0.12);
}
.category-badge .dot {
    opacity: 0.4;
}
.rbt-card-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: #151515;
    margin-bottom: 10px;
    letter-spacing: -0.5px;
    line-height: 1.3;
}
.rbt-card-title a {
    color: inherit;
    text-decoration: none;
}
.category-card-premium:hover .rbt-card-title a {
    background: linear-gradient(90deg, #133a54 0%, #1a5a80 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.rbt-card-text {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 20px;
    flex-grow: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.rbt-card-text.muted {
    color: #94a3b8;
    font-style: italic;
}
.card-footer-premium {
    margin-top: auto;
    padding-top: 16px;
    border-top: 1px solid rgba(19, 58, 84, 0.08);
}
.btn-explore {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: #ffffff;
    text-decoration: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    box-shadow: 0 4px 15px rgba(19, 58, 84, 0.25);
}
.btn-explore:hover {
    transform: translateX(4px);
    box-shadow: 0 8px 25px rgba(19, 58, 84, 0.35);
    color: #fff;
}
.btn-explore i {
    transition: transform 0.3s ease;
    font-size: 16px;
}
.btn-explore:hover i {
    transform: translateX(4px);
}

/* No results */
.no-results-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    text-align: center;
}
.no-results-title {
    font-size: 26px;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 12px;
}
.no-results-text {
    font-size: 15px;
    color: #64748b;
    margin-bottom: 24px;
    max-width: 420px;
    line-height: 1.6;
}
.btn-clear-search {
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%);
    color: #ffffff;
    border: none;
    padding: 12px 28px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(19, 58, 84, 0.25);
}
.btn-clear-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.35);
}

@media (max-width: 768px) {
    .title-premium {
        font-size: 36px !important;
    }
    .filter-bar-premium {
        flex-direction: column;
        align-items: stretch;
        padding: 24px;
        gap: 20px;
    }
    .search-box-premium {
        min-width: 100%;
        max-width: 100%;
    }
    .rbt-card-img {
        height: 180px;
    }
}
</style>
