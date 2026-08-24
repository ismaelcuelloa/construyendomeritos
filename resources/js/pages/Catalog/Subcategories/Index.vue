<script setup lang="ts">
import Breadcrumbs, { BreadcrumbItemType } from '@/components/shared/Breadcrumbs.vue';
import Pagination, { PageChangeEvent } from '@/components/ui/pagination/Pagination.vue';
import AppListCourses from '@/features/courses/components/AppListCourses.vue';
import AppWebLayout from '@/layouts/AppWebLayout.vue';
import type { Category, Course, Paginated, Subcategory } from '@/types/project';
import { usePage, router, Link } from '@inertiajs/vue3';
import SeoHead from '@/components/shared/SeoHead.vue';
import { useSeo } from '@/composables/useSeo';
import { computed, onMounted, ref } from 'vue';

interface Props {
    category: any;
    subcategories: Paginated<Subcategory>;
    parentCategory?: Category;
    courses?: Paginated<Course> | null;
}

const props = defineProps<Props>();

const page = usePage();
const { generateStructuredData } = useSeo();
const seoData = computed(() => page.props.seo || {});

const categorySchema = computed(() =>
    generateStructuredData('CollectionPage', {
        name: 'Subcategorías de ' + props.category.title,
        description: props.category.description || 'Subcategorías de ' + props.category.title,
        url: page.url,
    }),
);

const breadcrumbs: BreadcrumbItemType[] = [];

if (props.parentCategory) {
    // Nested subcategories
    breadcrumbs.push(
        { title: 'Home', href: '/' },
        { title: props.parentCategory.title, href: '/categorias/' + props.parentCategory.slug },
        { title: props.category.title, href: '/categorias/' + props.parentCategory.slug + '/' + props.category.slug },
    );
} else if (props.category.slug) {
    // Top-level subcategories (category is a Category)
    breadcrumbs.push(
        { title: 'Home', href: '/' },
        { title: props.category.title, href: '/categorias/' + props.category.slug },
    );
}

const handlePageChange = (pageChangeEvent: PageChangeEvent) => {
    if (pageChangeEvent.url !== null) router.visit(pageChangeEvent.url);
};

const image_default = '/assets/images/others/thumbnail-placeholder.svg';

const subcategoryUrl = (subcatSlug: string) => {
    if (props.parentCategory) {
        return '/categorias/' + props.parentCategory.slug + '/' + props.category.slug + '/' + subcatSlug;
    }
    return '/categorias/' + props.category.slug + '/' + subcatSlug;
};

// ===== Filtro personalizado (OPEC) =====
const selectedFilter = ref('');

const hasCustomFilter = computed(
    () => props.category.enable_custom_filter && (props.category.custom_filter_options?.length ?? 0) > 0,
);

const filterOptionsFlat = computed(() =>
    (props.category.custom_filter_options ?? []).map((group: any) => ({
        label: group.label,
        value: group.label,
    })),
);

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    selectedFilter.value = urlParams.get('filter') || '';
});

const handleFilterChange = () => {
    const params: Record<string, string> = {};
    if (selectedFilter.value) {
        params.filter = selectedFilter.value;
    }
    router.get(window.location.pathname, params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilter = () => {
    selectedFilter.value = '';
    handleFilterChange();
};
</script>

<template>
    <SeoHead
        :title="seoData.title"
        :description="seoData.description"
        :image="seoData.image"
        :url="seoData.url"
        :keywords="seoData.keywords"
        :structured-data="categorySchema"
    />
    <AppWebLayout>
        <div class="rbt-page-banner-wrapper premium-category-banner">
            <div class="banner-animated-bg">
                <div class="floating-shape shape-1"></div>
                <div class="floating-shape shape-2"></div>
                <div class="floating-shape shape-3"></div>
            </div>

            <div class="rbt-banner-content" style="z-index: 1">
                <div class="rbt-banner-content-top">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <Breadcrumbs :breadcrumbs="breadcrumbs" />

                                <div class="title-wrapper-premium">
                                    <div class="title-content">
                                        <div class="welcome-tag">
                                            <i class="feather-grid"></i>
                                            <span>Subcategorías</span>
                                        </div>
                                        <h1 class="title-premium mb--0">{{ props.category.title }}</h1>
                                        <p class="subtitle-premium" v-if="category.description">
                                            <i class="feather-info"></i>
                                            {{ category.description }}
                                        </p>
                                    </div>
                                    <div class="stats-wrapper">
                                        <div class="badge-premium">
                                            <div class="badge-icon">
                                                <i class="feather-layers"></i>
                                            </div>
                                            <div class="badge-content">
                                                <span class="badge-number">{{ subcategories.total }}</span>
                                                <span class="badge-text">Subcategorías</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selector OPEC flotando sobre el banner (solo nivel categoria raiz) -->
                <div v-if="hasCustomFilter && !parentCategory" class="rbt-course-top-wrapper-premium opec-top-wrapper">
                    <div class="container">
                        <div class="opec-filter-card">
                            <label class="filter-label">
                                <i class="feather-filter"></i>
                                Selecciona tu OPEC
                            </label>
                            <select v-model="selectedFilter" class="custom-filter-select" @change="handleFilterChange">
                                <option value="">Todos los materiales</option>
                                <option v-for="option in filterOptionsFlat" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <div class="opec-help-info">
                                <i class="feather-help-circle"></i>
                                <span
                                    >¿No sabes como buscar tu OPEC? Aquí te explico como:
                                    <a href="https://www.youtube.com/watch?v=i3ZlWfBaUR4&t=1s" target="_blank" rel="noopener noreferrer"
                                        >VIDEO</a
                                    ></span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rbt-section-overlayping-top rbt-section-gapBottom">
            <div class="inner">
                <div class="container">
                    <!-- Resultados filtrados por OPEC -->
                    <template v-if="courses">
                        <AppListCourses v-if="courses.data.length > 0" :courses="courses.data" :showCategory="false" />

                        <div v-else class="no-results-container">
                            <h3 class="no-results-title">Sin resultados</h3>
                            <p class="no-results-text">No se encontraron materiales para el OPEC seleccionado.</p>
                            <button type="button" class="btn-back-subcats" @click="clearFilter">
                                <i class="feather-x-circle"></i> Limpiar filtro
                            </button>
                        </div>

                        <div v-if="courses.data.length > 0" class="row">
                            <div class="col-lg-12 mt--60">
                                <Pagination :paginated="courses" @pageChanged="handlePageChange" :pages="5" />
                            </div>
                        </div>
                    </template>

                    <!-- Listado de subcategorias (sin filtro activo) -->
                    <template v-else>
                        <div v-if="subcategories.data.length > 0" class="row g-5">
                        <div v-for="subcat in subcategories.data" :key="subcat.id" class="col-lg-4 col-md-6 col-12">
                            <div class="rbt-card variation-01 rbt-hover subcategory-card-premium">
                                <div class="rbt-card-img">
                                    <Link :href="subcategoryUrl(subcat.slug)">
                                        <img :src="subcat.image?.url ?? image_default" alt="Subcategory image" />
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
                                        <span>{{ subcat.courses_count ?? subcat.courses?.length ?? 0 }} Materiales</span>
                                    </div>

                                    <h4 class="rbt-card-title">
                                        <Link :href="subcategoryUrl(subcat.slug)">
                                            {{ subcat.title }}
                                        </Link>
                                    </h4>

                                    <p class="rbt-card-text">{{ subcat.description }}</p>

                                    <div class="card-footer-premium">
                                        <Link class="btn-explore" :href="subcategoryUrl(subcat.slug)">
                                            <span>Explorar</span>
                                            <i class="feather-arrow-right"></i>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="no-results-container">
                            <h3 class="no-results-title">No hay subcategorías</h3>
                            <p class="no-results-text">No se encontraron subcategorías para esta categoría.</p>
                        </div>

                        <div v-if="subcategories.data.length > 0" class="row">
                            <div class="col-lg-12 mt--60">
                                <Pagination :paginated="subcategories" @pageChanged="handlePageChange" :pages="5" />
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AppWebLayout>
</template>

<style scoped>
.premium-category-banner :deep(.page-list) {
    margin: 0 !important;
}

.premium-category-banner :deep(.page-list .rbt-breadcrumb-item) {
    color: #ffffff !important;
}

.premium-category-banner :deep(.page-list .rbt-breadcrumb-item a) {
    color: rgba(255, 255, 255, 0.9) !important;
    transition: color 0.3s ease;
}

.premium-category-banner :deep(.page-list .rbt-breadcrumb-item a:hover) {
    color: #ffffff !important;
}

.premium-category-banner :deep(.page-list .rbt-breadcrumb-item.active) {
    color: #ffffff !important;
    font-weight: 600;
}

.premium-category-banner :deep(.page-list .icon-right i) {
    color: rgba(255, 255, 255, 0.7) !important;
}

.premium-category-banner {
    padding-bottom: 160px !important;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
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

.floating-shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
    animation: float 20s infinite ease-in-out;
}

.floating-shape.shape-1 {
    width: 300px;
    height: 300px;
    top: -100px;
    right: 10%;
    animation-delay: 0s;
}

.floating-shape.shape-2 {
    width: 200px;
    height: 200px;
    bottom: -50px;
    left: 15%;
    animation-delay: 5s;
}

.floating-shape.shape-3 {
    width: 150px;
    height: 150px;
    top: 40%;
    right: 20%;
    animation-delay: 10s;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(20px, -20px) scale(1.1); }
    50% { transform: translate(-20px, 20px) scale(0.9); }
    75% { transform: translate(20px, 20px) scale(1.05); }
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

.title-content { flex: 1; }

.welcome-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 10px 20px;
    border-radius: 50px;
    margin-bottom: 20px;
}

.welcome-tag i {
    color: #ffffff;
    font-size: 18px;
}

.welcome-tag span {
    color: #ffffff;
    font-weight: 600;
    font-size: 14px;
    letter-spacing: 0.5px;
}

.title-premium {
    font-size: 48px !important;
    font-weight: 900 !important;
    color: #ffffff !important;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    letter-spacing: -1.5px;
    margin-bottom: 16px !important;
    line-height: 1.1 !important;
}

.subtitle-premium {
    font-size: 19px;
    color: rgba(255, 255, 255, 0.95);
    margin: 0;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

.subtitle-premium i { font-size: 20px; }

.stats-wrapper {
    display: flex;
    gap: 16px;
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
    min-width: 140px;
}

.badge-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(26, 90, 128, 0.15) 100%);
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
}

.badge-number {
    font-size: 18px;
    font-weight: 900;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1;
}

.badge-text {
    font-size: 18px;
    font-weight: 700;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1;
}

/* Subcategory Cards */
.subcategory-card-premium {
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

.subcategory-card-premium:hover {
    box-shadow: 0 20px 60px rgba(19, 58, 84, 0.2);
    transform: translateY(-8px);
}

.rbt-card-img {
    position: relative;
    overflow: hidden;
    height: 200px;
}

.rbt-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.subcategory-card-premium:hover .rbt-card-img img {
    transform: scale(1.08);
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.6) 100%);
    opacity: 0;
    transition: opacity 0.4s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.subcategory-card-premium:hover .image-overlay {
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

.subcategory-card-premium:hover .overlay-badge {
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
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(26, 90, 128, 0.05) 100%);
    color: #133a54;
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 16px;
    width: fit-content;
    border: 1px solid rgba(19, 58, 84, 0.2);
}

.rbt-card-title {
    font-size: 1.25rem;
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

.subcategory-card-premium:hover .rbt-card-title a {
    background: linear-gradient(90deg, #133a54 0%, #1a5a80 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.rbt-card-text {
    color: #666;
    font-size: 14px;
    line-height: 1.7;
    margin-bottom: 20px;
    flex-grow: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.card-footer-premium {
    margin-top: auto;
    padding-top: 16px;
    border-top: 1px solid rgba(19, 58, 84, 0.1);
}

.btn-explore {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
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
    padding: 200px 20px 80px;
    text-align: center;
}

.no-results-title {
    font-size: 28px;
    font-weight: 800;
    color: #333;
    margin-bottom: 12px;
}

.no-results-text {
    font-size: 16px;
    color: #666;
    max-width: 400px;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .title-premium {
        font-size: 32px !important;
    }

    .title-wrapper-premium {
        flex-direction: column;
        gap: 24px;
    }

    .rbt-card-img {
        height: 180px;
    }
}

/* ===== Selector OPEC ===== */
.opec-top-wrapper {
    margin-top: -12px;
    position: relative;
    z-index: 10;
    padding-top: 16px;
}

.opec-filter-card {
    background: #fff;
    padding: 32px 40px;
    border-radius: 20px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
    border: 2px solid rgba(19, 58, 84, 0.08);
    transition: all 0.3s ease;
}

.opec-filter-card:hover {
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.14);
}

.filter-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 700;
    color: #333;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-label i {
    color: #133a54;
    font-size: 20px;
}

.custom-filter-select {
    width: 100%;
    border: 2px solid rgba(19, 58, 84, 0.2) !important;
    outline: none !important;
    background-color: rgba(19, 58, 84, 0.03);
    font-size: 16px;
    font-weight: 600;
    color: #333;
    padding: 14px 48px 14px 20px;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    border-radius: 12px;
    transition:
        border-color 0.3s ease,
        box-shadow 0.3s ease;
}

.custom-filter-select:hover,
.custom-filter-select:focus {
    border-color: #133a54 !important;
}

.custom-filter-select:focus {
    box-shadow: 0 0 0 4px rgba(19, 58, 84, 0.12);
}

.opec-help-info {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 14px;
    font-size: 13px;
    color: #777;
}

.opec-help-info i {
    color: #133a54;
}

.opec-help-info a {
    color: #133a54;
    font-weight: 700;
    text-decoration: underline;
}

.btn-back-subcats {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: white;
    color: #133a54;
    border: 2px solid #133a54;
    font-weight: 700;
    padding: 10px 22px;
    border-radius: 50px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-back-subcats:hover {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: #fff;
}
</style>
