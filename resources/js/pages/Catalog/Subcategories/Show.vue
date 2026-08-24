<script setup lang="ts">
import Breadcrumbs, { BreadcrumbItemType } from '@/components/shared/Breadcrumbs.vue';
import AppListCourses from '@/features/courses/components/AppListCourses.vue';
import Pagination, { PageChangeEvent } from '@/components/ui/pagination/Pagination.vue';
import AppWebLayout from '@/layouts/AppWebLayout.vue';
import type { Category, Course, Paginated, Subcategory } from '@/types/project';
import { router, usePage } from '@inertiajs/vue3';
import SeoHead from '@/components/shared/SeoHead.vue';
import { useSeo } from '@/composables/useSeo';
import { computed, onMounted, ref } from 'vue';

interface Props {
    category: Category;
    subcategory: Subcategory;
    parentSubcategory?: Subcategory;
    courses: Paginated<Course>;
}

const props = defineProps<Props>();

const page = usePage();
const { generateStructuredData } = useSeo();
const seoData = computed(() => page.props.seo || {});

const categorySchema = computed(() =>
    generateStructuredData('CollectionPage', {
        name: props.subcategory.title + ' - ' + props.category.title,
        description: props.subcategory.description || 'Cursos de ' + props.subcategory.title,
        url: page.url,
    }),
);

const breadcrumbs: BreadcrumbItemType[] = [];

if (props.parentSubcategory) {
    breadcrumbs.push(
        { title: 'Home', href: '/' },
        { title: props.category.title, href: '/categorias/' + props.category.slug },
        { title: props.parentSubcategory.title, href: '/categorias/' + props.category.slug + '/' + props.parentSubcategory.slug },
        { title: props.subcategory.title, href: '/categorias/' + props.category.slug + '/' + props.parentSubcategory.slug + '/' + props.subcategory.slug },
    );
} else {
    breadcrumbs.push(
        { title: 'Home', href: '/' },
        { title: props.category.title, href: '/categorias/' + props.category.slug },
        { title: props.subcategory.title, href: '/categorias/' + props.category.slug + '/' + props.subcategory.slug },
    );
}

const handlePageChange = (pageChangeEvent: PageChangeEvent) => {
    if (pageChangeEvent.url !== null) router.visit(pageChangeEvent.url);
};

const searchValue = ref('');
const selectedFilter = ref('');

const filterOptionsFlat = computed(() => {
    const raw = props.category.custom_filter_options ?? [];
    return raw.map((group: any) => ({
        label: group.label,
        value: group.label,
    }));
});

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    searchValue.value = urlParams.get('search') || '';
    selectedFilter.value = urlParams.get('filter') || '';
});

const handleSearch = (e: Event) => {
    e.preventDefault();
    const form = e.target as HTMLFormElement;
    const searchInput = form.querySelector('input') as HTMLInputElement;

    const params: Record<string, string> = { search: searchInput.value };
    if (selectedFilter.value) params.filter = selectedFilter.value;

    router.get(window.location.pathname, params, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleFilterChange = () => {
    const params: Record<string, string> = {};
    if (searchValue.value) params.search = searchValue.value;
    if (selectedFilter.value) params.filter = selectedFilter.value;

    router.get(window.location.pathname, params, {
        preserveState: true,
        preserveScroll: true,
    });
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
                                            <i class="feather-folder"></i>
                                            <span>{{ props.category.title }} / Subcategoría</span>
                                        </div>
                                        <h1 class="title-premium mb--0">{{ props.subcategory.title }}</h1>
                                        <p class="subtitle-premium" v-if="subcategory.description">
                                            <i class="feather-info"></i>
                                            {{ subcategory.description }}
                                        </p>
                                    </div>
                                    <div class="stats-wrapper">
                                        <div class="badge-premium">
                                            <div class="badge-icon">
                                                <i class="feather-book-open"></i>
                                            </div>
                                            <div class="badge-content">
                                                <span class="badge-number">{{ courses.total }}</span>
                                                <span class="badge-text">Materiales</span>
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
                                        <span class="info-count"><strong>{{ courses.from }}</strong> de <strong>{{ courses.total }}</strong> materiales de estudio</span>
                                    </div>
                                </div>
                            </div>

                            <div class="filter-right">
                                <div v-if="category.enable_custom_filter && category.custom_filter_options?.length > 0" class="opec-search-title">
                                    <span>BUSCAR POR OPEC:</span>
                                </div>
                                <div class="search-box-premium">
                                    <form @submit="handleSearch" class="search-form-premium">
                                        <div class="search-icon-left">
                                            <i class="feather-search"></i>
                                        </div>
                                        <input v-model="searchValue" type="text" placeholder="Buscar por título, descripción..." />
                                        <button type="submit" class="search-btn-premium">
                                            <span>Buscar</span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div v-if="category.enable_custom_filter && filterOptionsFlat.length > 0" class="filter-selector-row">
                                <label class="filter-label">
                                    <i class="feather-filter"></i>
                                    Selecciona tu OPEC
                                </label>
                                <select v-model="selectedFilter" @change="handleFilterChange" class="custom-filter-select">
                                    <option value="">Todos los materiales</option>
                                    <option v-for="option in filterOptionsFlat" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                                <div class="opec-help-info">
                                    <i class="feather-help-circle"></i>
                                    <span>¿No sabes como buscar tu OPEC? Aquí te explico como:
                                        <a href="https://www.youtube.com/watch?v=i3ZlWfBaUR4&t=1s" target="_blank" rel="noopener noreferrer">VIDEO</a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rbt-section-overlayping-top rbt-section-gapBottom">
            <div class="inner">
                <div class="container">
                    <AppListCourses v-if="courses.data.length > 0" :courses="courses.data" :showCategory="false" />

                    <div v-else class="no-results-container">
                        <h3 class="no-results-title">No hay ningún material</h3>
                        <p class="no-results-text">No se encontraron resultados para tu búsqueda. Intenta con otros términos.</p>
                        <button @click="searchValue = ''" class="btn-clear-search"><i class="feather-x-circle"></i> Limpiar búsqueda</button>
                    </div>

                    <div v-if="courses.data.length > 0" class="row">
                        <div class="col-lg-12 mt--60">
                            <Pagination :paginated="courses" @pageChanged="handlePageChange" :pages="5" />
                        </div>
                    </div>
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
}

.filter-left { flex: 0 0 auto; }

.filter-right {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.results-info-premium {
    display: flex;
    align-items: center;
    gap: 16px;
}

.info-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(26, 90, 128, 0.08) 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.info-icon i { color: #133a54; font-size: 22px; }

.info-text { display: flex; flex-direction: column; gap: 4px; }

.info-count { font-size: 16px; color: #333; font-weight: 500; }

.info-count strong { color: #133a54; font-weight: 800; }

.search-box-premium { flex: 1; min-width: 350px; max-width: 500px; }

.search-form-premium {
    position: relative;
    display: flex;
    align-items: center;
    background: rgba(19, 58, 84, 0.04);
    border: 2px solid rgba(19, 58, 84, 0.15);
    border-radius: 14px;
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

.search-form-premium input::placeholder { color: #999; }

.search-form-premium input:focus { outline: none; }

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
    cursor: pointer;
    box-shadow: -2px 0 8px rgba(19, 58, 84, 0.15);
}

.search-btn-premium:hover {
    background: linear-gradient(135deg, #1a5a80 0%, #133a54 100%);
}

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
    margin-bottom: 30px;
    max-width: 400px;
    line-height: 1.6;
}

.btn-clear-search {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: #ffffff;
    border: none;
    padding: 14px 32px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 15px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(19, 58, 84, 0.25);
}

.btn-clear-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.35);
}

.btn-clear-search i { font-size: 18px; }

.filter-selector-row {
    width: 100%;
    flex-basis: 100%;
    padding-top: 24px;
    border-top: 2px solid rgba(19, 58, 84, 0.08);
    margin-top: 24px;
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

.filter-label i { color: #133a54; font-size: 20px; }

.custom-filter-select {
    width: 100%;
    border: 2px solid rgba(19, 58, 84, 0.15) !important;
    outline: none !important;
    background-color: rgba(19, 58, 84, 0.03);
    background-image: url("data:image/svg+xml,%3Csvg width='16' height='10' viewBox='0 0 16 10' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L8 8.5L15 1.5' stroke='%23f07900' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 16px;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    padding: 16px 48px 16px 20px;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    border-radius: 12px;
}

.custom-filter-select:focus {
    color: #133a54;
    outline: none !important;
    border-color: #133a54 !important;
    background-color: #ffffff;
    box-shadow: 0 0 0 4px rgba(19, 58, 84, 0.1);
}

.opec-search-title {
    display: flex;
    align-items: center;
    margin-bottom: 0;
}

.opec-search-title span {
    font-size: 14px;
    font-weight: 700;
    color: #555;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.opec-help-info {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 16px;
    padding: 14px 18px;
    background: rgba(19, 58, 84, 0.04);
    border-radius: 10px;
    border: 1px solid rgba(19, 58, 84, 0.15);
}

.opec-help-info i { color: #133a54; font-size: 20px; flex-shrink: 0; }

.opec-help-info span { font-size: 14px; color: #555; line-height: 1.5; }

.opec-help-info a {
    color: #133a54;
    font-weight: 700;
    text-decoration: underline;
}

@media (max-width: 768px) {
    .title-premium { font-size: 32px !important; }
    .title-wrapper-premium { flex-direction: column; gap: 24px; }
    .filter-bar-premium { flex-direction: column; padding: 24px; gap: 20px; }
    .filter-right { width: 100%; flex-direction: column; gap: 12px; }
    .search-box-premium { min-width: 100%; max-width: 100%; }
    .search-form-premium input { padding: 14px 120px 14px 50px; }
}
</style>
