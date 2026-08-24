<script setup lang="ts">
import SeoHead from '@/components/shared/SeoHead.vue';
import type { Course, Paginated } from '@/types/project';
import AppWebLayout from '@/layouts/AppWebLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Paginator from 'primevue/paginator';
import { Search } from 'lucide-vue-next';

interface Props {
    courses: Paginated<Course>;
    niveles: string[];
    filters: {
        codigo?: string;
        grado?: string;
        nivel?: string;
        educacion?: string;
        cargo?: string;
        ubicacion?: string;
        salario_min?: string;
        salario_max?: string;
        per_page?: number;
    };
}

const props = defineProps<Props>();
const page = usePage();
const seoImage = (page.props as any).seo?.image;

const codigo = ref(props.filters.codigo || '');
const grado = ref(props.filters.grado || '');
const nivel = ref(props.filters.nivel || '');
const educacion = ref(props.filters.educacion || '');
const cargo = ref(props.filters.cargo || '');
const ubicacion = ref(props.filters.ubicacion || '');
const salarioMinimo = ref(props.filters.salario_min || '');
const salarioMaximo = ref(props.filters.salario_max || '');
const perPage = ref(props.filters.per_page || 10);

const search = () => {
    router.get('/', {
        codigo: codigo.value || undefined,
        grado: grado.value || undefined,
        nivel: nivel.value || undefined,
        educacion: educacion.value || undefined,
        cargo: cargo.value || undefined,
        ubicacion: ubicacion.value || undefined,
        salario_min: salarioMinimo.value || undefined,
        salario_max: salarioMaximo.value || undefined,
        per_page: perPage.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: false,
        onSuccess: () => {
            setTimeout(() => {
                const el = document.getElementById('resultados');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        },
    });
};

const clearFilters = () => {
    codigo.value = '';
    grado.value = '';
    nivel.value = '';
    educacion.value = '';
    cargo.value = '';
    ubicacion.value = '';
    salarioMinimo.value = '';
    salarioMaximo.value = '';
    perPage.value = 10;
    search();
};

const formatPrice = (price: number | string) => {
    const num = typeof price === 'string' ? parseFloat(price) : price;
    return '$' + num.toLocaleString('es-CO');
};

const getInscritos = (course: Course) => {
    const real = course.subscriptions?.length || 0;
    const seed = ((course.id * 2654435761) >>> 0) % 187;
    const random = seed + 13;
    return real + random;
};

const handlePage = (url: string | null) => {
    if (url) {
        router.get(url, {}, { preserveState: true, preserveScroll: false });
    }
};

const onPageChange = (event: { page: number; first: number; rows: number }) => {
    const page = event.page + 1;
    router.get('/', {
        codigo: codigo.value || undefined,
        grado: grado.value || undefined,
        nivel: nivel.value || undefined,
        page,
        per_page: perPage.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: false,
    });
};

const seguro = (page.props as any).auth?.user;

const getDisplayCode = (course: Course) => {
    const searchedCode = props.filters.codigo?.trim();
    if (!searchedCode) return null;
    const codes = (course as any).codes ?? [];
    const allCodes = [course.code, ...codes.map((c: any) => c.code)].filter(Boolean);
    const matched = allCodes.find(c => c.toLowerCase().includes(searchedCode.toLowerCase()));
    return matched || null;
};
</script>

<template>
    <SeoHead
        title="Materiales de estudio - Procuraduría General de la Nación 2026 | Construyendo Méritos con Excelencia"
        description="Materiales de estudio para la Procuraduría General de la Nación 2026 - Construyendo Méritos con Excelencia. Simulacros, guías actualizadas y recursos para tu cargo ideal."
        :url="page.url"
        type="website"
        :image="seoImage"
    />
    <AppWebLayout>
        <div class="app">
            <!-- ===== HERO ===== -->
            <section class="hero">
                <div class="hero__content">
                    <div class="hero__badge">
                        <span class="hero__badge-dot"></span>
                        Material de estudio - Procuraduría General de la Nación 2026
                    </div>
                    <h1 class="hero__title"><span>Construyendo Méritos con Excelencia</span></h1>
                    <p class="hero__subtitle">Prepárate para alcanzar tu cargo público ideal en la Procuraduría.</p>
                    <p class="hero__subtitle hero__subtitle--secondary">Encuentra simulacros, guías de estudio y material actualizado para prepararte de manera efectiva y aumentar tus posibilidades de éxito en el concurso de la Procuraduría General de la Nación.</p>
                </div>
            </section>

            <!-- ===== SEARCH ===== -->
            <section class="search-section">
                <div class="search-container">
                    <div class="search-header">
                        <h2 class="search-title">Buscar Convocatorias</h2>
                        <p class="search-subtitle">Filtre por código, grado o nivel para encontrar el material de estudio de su interes.</p>
                    </div>

                    <div class="search-grid">
                        <div class="search-field-group">
                            <label class="search-label">Código de convocatoria</label>
                            <input
                                v-model="codigo"
                                type="text"
                                class="search-input"
                                placeholder="Ej: 108-2026"
                                @keyup.enter="search"
                            />
                        </div>

                        <div class="search-field-group">
                            <label class="search-label">Código y Grado</label>
                            <input
                                v-model="grado"
                                type="text"
                                class="search-input"
                                placeholder="Ej: 3PU-15"
                                @keyup.enter="search"
                            />
                        </div>

                        <div class="search-field-group">
                            <label class="search-label">Nivel</label>
                            <select v-model="nivel" class="search-input">
                                <option value="">Todos los niveles</option>
                                <option v-for="n in niveles" :key="n" :value="n">{{ n }}</option>
                            </select>
                        </div>

                        <div class="search-actions">
                            <button class="btn btn--primary" @click="search">
                                <Search :size="16" />
                                <span>Buscar material</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== RESULTS ===== -->
            <div class="content-wrapper">
            <section class="results" id="resultados">
                <div class="results-header">
                    <div class="results-header__left">
                        <h2 class="results-title">Resultados</h2>
                        <span class="results-count">{{ courses.total }} convocatorias encontradas</span>
                    </div>
                </div>

                <div v-if="courses.data.length === 0" class="empty-state">
                    <div class="empty-state__icon">
                        <Search :size="48" />
                    </div>
                    <h3 class="empty-state__title">Sin resultados</h3>
                    <p class="empty-state__text">No se encontraron convocatorias con los filtros seleccionados.</p>
                    <button class="btn btn--ghost" @click="clearFilters">Limpiar filtros</button>
                </div>

                <div v-for="course in courses.data" :key="course.id" class="course-card">
                    <div class="course-card__row">
                        <h3 class="course-card__cargo">{{ course.title }}</h3>
                    </div>

                    <div class="course-card__row course-card__row--codes">
                        <div v-if="getDisplayCode(course)" class="course-card__code">
                            <span class="course-card__code-label">Código Convocatoria:</span>
                            <span class="course-card__code-value">{{ getDisplayCode(course) }}</span>
                        </div>
                        <div v-if="getDisplayCode(course)" class="course-card__divider-v"></div>
                        <div class="course-card__code">
                            <span class="course-card__code-label">Código y Grado:</span>
                            <span class="course-card__code-value">{{ course.grado || 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="course-card__row course-card__row--stats">
                        <div class="course-card__stat">
                            <strong>Nº de inscritos</strong>
                            <span>{{ getInscritos(course) }}</span>
                        </div>
                        <div class="course-card__stat">
                            <strong>Nivel</strong>
                            <span>{{ course.category?.title || 'N/A' }}</span>
                        </div>
                        <div class="course-card__stat">
                            <strong>Costo del material de estudio</strong>
                            <span>{{ formatPrice(course.price) }}</span>
                        </div>
                        <div class="course-card__actions">
                            <a :href="`/cursos/${course.slug}`" class="btn-course">Ver material de estudio</a>
                        </div>
                    </div>

                    <div class="course-card__divider"></div>

                    <div class="course-card__row course-card__notice">
                        <span class="course-card__notice-icon">&#9432;</span>
                        <p>Material con guías actualizadas y simulacros en línea para evaluar tu progreso y mejorar tu preparación.</p>
                    </div>
                </div>

                <div v-if="courses.total > 10" class="paginator-wrapper">
                    <Paginator
                        :rows="10"
                        :totalRecords="courses.total"
                        :first="(courses.current_page - 1) * 10"
                        template="FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords}"
                        @page="onPageChange"
                    />
                </div>
            </section>
            </div>
        </div>
    </AppWebLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

/* ===== BASE ===== */
.app {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: #133a54;
    background: #f2f6fa;
    min-height: 100vh;
}

/* ===== HERO ===== */
.hero {
    position: relative;
    min-height: 90vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 1.5rem 8rem;
    overflow: hidden;
    background: url('/assets/images/bg/bg-merito.svg') center center / cover no-repeat;
    background-color: #ffe500;
}

.hero__content {
    position: relative;
    z-index: 10;
    text-align: center;
    max-width: 900px;
    margin-bottom: 3rem;
}

.hero__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    background: rgba(17, 54, 79, 0.12);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(17, 54, 79, 0.2);
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
    color: #11364f;
    margin-bottom: 2rem;
    animation: fadeInDown 0.8s ease-out;
}

.hero__badge-dot {
    width: 8px;
    height: 8px;
    background: #11364f;
    border-radius: 50%;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.5;
        transform: scale(1.2);
    }
}

.hero__title {
    font-size: clamp(2.5rem, 6vw, 4.5rem);
    font-weight: 800;
    line-height: 1.1;
    color: #11364f;
    margin-bottom: 1.5rem;
    letter-spacing: -0.02em;
    animation: fadeInUp 0.8s ease-out 0.1s backwards;
    text-shadow: 0 2px 40px rgba(17, 54, 79, 0.1);
}

.hero__title span {
    background: linear-gradient(135deg, #11364f 0%, #1a5a80 50%, #11364f 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero__subtitle {
    font-size: clamp(1rem, 2.5vw, 1.25rem);
    font-weight: 400;
    color: #11364f;
    max-width: 650px;
    margin: 0 auto;
    line-height: 1.7;
    animation: fadeInUp 0.8s ease-out 0.2s backwards;
}

.hero__subtitle--secondary {
    font-size: clamp(0.85rem, 1.8vw, 1rem);
    color: #11364f;
    max-width: 750px;
    margin-top: 12px;
    animation: fadeInUp 0.8s ease-out 0.3s backwards;
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

/* ===== SEARCH SECTION ===== */
.search-section {
    position: relative;
    z-index: 10;
    display: flex;
    justify-content: center;
    margin-top: -260px;
    padding: 0 24px;
}

.search-container {
    background: #ffffff;
    border-radius: 20px;
    box-shadow:
        0 4px 6px -1px rgba(0, 0, 0, 0.05),
        0 10px 30px -5px rgba(19, 58, 84, 0.12),
        0 20px 60px -10px rgba(10, 33, 53, 0.08);
    padding: 36px 40px;
    width: 100%;
    max-width: 1400px;
    border: 1px solid #e8eff6;
}

.search-header {
    text-align: center;
    margin-bottom: 28px;
}

.search-title {
    font-size: 22px;
    font-weight: 700;
    color: #133a54;
    margin: 0 0 8px;
}

.search-subtitle {
    font-size: 14px;
    color: #64748b;
    margin: 0;
}

.search-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 16px;
    align-items: end;
}

.search-field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.search-label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.search-input {
    padding: 12px 20px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: #133a54;
    background: #ffffff;
    outline: none;
    transition: all 0.25s ease;
    width: 100%;
    box-sizing: border-box;
    height: 44px;
    line-height: 1.4;
}

.search-input::placeholder {
    color: #94a3b8;
}

.search-input:hover {
    border-color: #cbd5e1;
}

.search-input:focus {
    border-color: #133a54;
    box-shadow: 0 0 0 3px rgba(19, 58, 84, 0.08);
}

select.search-input {
    cursor: pointer;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-color: #ffffff;
}

select.search-input option {
    color: #133a54;
    background: #ffffff;
}

.search-actions {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Content Wrapper */
.content-wrapper {
    background: #ffffff;
    padding-top: 60px;
    margin-top: -2px;
    position: relative;
    z-index: 1;
    min-height: 100vh;
}

.content-wrapper::before {
    content: '';
    position: absolute;
    top: -100px;
    left: 0;
    right: 0;
    height: 100px;
    background: linear-gradient(to bottom, transparent, #ffffff);
    pointer-events: none;
}

/* ===== BUTTONS ===== */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    letter-spacing: 0.2px;
}

.btn--ghost {
    background: transparent;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn--ghost:hover {
    background: #f8fafc;
    color: #133a54;
    border-color: #94a3b8;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(10, 33, 53, 0.06);
}

.btn--outline {
    background: transparent;
    color: #133a54;
    border: 2px solid #fef3a0;
}

.btn--outline:hover {
    background: #fef9d6;
    border-color: #f5e42c;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 228, 44, 0.15);
}

.btn--primary {
    background: linear-gradient(135deg, #133a54 0%, #0a2135 100%);
    color: #f5e42c;
    box-shadow:
        0 4px 16px -4px rgba(19, 58, 84, 0.4),
        inset 0 1px 0 rgba(245, 228, 44, 0.15);
}

.btn--primary:hover {
    transform: translateY(-2px);
    box-shadow:
        0 8px 28px -6px rgba(19, 58, 84, 0.5),
        inset 0 1px 0 rgba(245, 228, 44, 0.2);
}

.btn--primary:active {
    transform: translateY(0) scale(0.98);
}

.btn--block {
    width: 100%;
}

/* ===== RESULTS ===== */
.results {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px 80px;
    scroll-margin-top: 100px;
}

.results-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    gap: 16px;
    flex-wrap: wrap;
}

.results-header--bottom {
    margin-top: 32px;
    margin-bottom: 0;
    justify-content: flex-end;
}

.results-header__left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.results-title {
    font-size: 20px;
    font-weight: 700;
    color: #133a54;
    margin: 0;
}

.results-count {
    font-size: 13px;
    color: #64748b;
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 500;
}

.results-header__right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 12px;
}

.pagination-label {
    font-size: 13px;
    color: #94a3b8;
    font-weight: 500;
}

.pagination-select {
    padding: 8px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    background: #ffffff;
    cursor: pointer;
    color: #133a54;
    font-weight: 600;
    white-space: nowrap;
}

.pagination-nav {
    display: flex;
    gap: 6px;
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    background: #ffffff;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
    background: #133a54;
    border-color: #133a54;
    color: #f5e42c;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.25);
}

.pagination-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 24px;
    background: #f8fafc;
    border-radius: 16px;
    border: 1px dashed #e2e8f0;
}

.empty-state__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #133a54;
    margin-bottom: 20px;
}

.empty-state__title {
    font-size: 18px;
    font-weight: 700;
    color: #133a54;
    margin: 0 0 8px;
}

.empty-state__text {
    font-size: 14px;
    color: #64748b;
    margin: 0 0 24px;
}

/* ===== COURSE CARD ===== */
.course-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.course-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 16px rgba(10, 33, 53, 0.06);
}

.course-card__row {
    padding: 14px 20px;
}

.course-card__cargo {
    font-size: 18px;
    font-weight: 700;
    color: #133a54;
    margin: 0;
}

.course-card__row--codes {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-top: 0;
    padding-bottom: 10px;
}

.course-card__code {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
}

.course-card__code-label {
    color: #64748b;
    font-weight: 500;
}

.course-card__code-value {
    color: #133a54;
    font-weight: 700;
}

.course-card__divider-v {
    width: 1px;
    height: 20px;
    background: #e2e8f0;
}

.course-card__row--stats {
    display: flex;
    align-items: flex-start;
    gap: 0;
    padding-top: 12px;
    padding-bottom: 12px;
    border-top: 1px solid #f1f5f9;
}

.course-card__stat {
    flex: 1;
    min-width: 0;
    padding: 0 12px;
    border-right: 1px solid #f1f5f9;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.course-card__stat:first-child {
    padding-left: 0;
}

.course-card__stat:last-of-type {
    border-right: none;
}

.course-card__stat strong {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.course-card__stat span {
    font-size: 14px;
    font-weight: 600;
    color: #133a54;
}

.course-card__actions {
    display: flex;
    align-items: center;
    padding: 0 12px;
    min-width: 160px;
}

.btn-course {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 20px;
    border: 2px solid #133a54;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #133a54;
    background: transparent;
    text-decoration: none;
    transition: all 0.2s ease;
    white-space: nowrap;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.btn-course:hover {
    background: #133a54;
    color: #f5e42c;
}

.course-card__divider {
    height: 1px;
    background: #e2e8f0;
    margin: 0 20px;
}

.course-card__row--bottom {
    padding-top: 12px;
    padding-bottom: 8px;
}

.course-card__ubicacion {
    font-size: 13px;
    color: #475569;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.course-card__ubicacion strong {
    color: #133a54;
    font-weight: 600;
}

.course-card__notice {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: #eff6ff;
    border-top: 1px solid #bfdbfe;
    padding: 12px 20px;
    font-size: 12px;
    color: #1e40af;
    line-height: 1.6;
    margin: 0;
}

.course-card__notice-icon {
    font-size: 18px;
    flex-shrink: 0;
    font-style: normal;
    font-weight: 700;
}

.course-card__notice p {
    margin: 0;
}

.course-card__notice strong {
    font-weight: 700;
    color: #1e3a8a;
}

/* ===== PAGINATION ===== */
.paginator-wrapper {
    padding: 20px 0;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 900px) {
    .search-grid {
        grid-template-columns: 1fr;
    }

    .search-actions {
        flex-direction: row;
        justify-content: flex-start;
    }

    .search-container {
        padding: 20px;
    }

    .course-card__row--stats {
        flex-wrap: wrap;
    }

    .course-card__stat {
        flex: 1 1 45%;
        border-right: none;
        padding: 8px 12px;
    }

    .course-card__actions {
        flex: 1 1 100%;
        justify-content: center;
        padding: 12px 0 0;
    }
}

@media (max-width: 768px) {
    .search-section {
        margin-top: -20px;
        padding: 0 16px;
    }

    .hero {
        min-height: auto;
        padding: 2rem 1rem 4rem;
    }

    .hero__badge {
        display: none;
    }

    .hero__subtitle--secondary {
        display: none;
    }

    .hero__title {
        font-size: 36px;
    }

    .hero__subtitle {
        font-size: 16px;
    }

    .results-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .course-card__row--codes {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }

    .course-card__divider-v {
        display: none;
    }

    .course-card__stat {
        flex: 1 1 100%;
        border-right: none;
        border-bottom: 1px solid #f1f5f9;
    }
}

@media (max-width: 480px) {
    .hero__badge {
        font-size: 0.8rem;
        padding: 0.4rem 1rem;
    }

    .hero__title {
        font-size: 28px;
    }

    .hero__subtitle {
        font-size: 14px;
    }

    .course-card {
        border-radius: 10px;
    }

    .course-card__cargo {
        font-size: 16px;
    }
}
</style>
