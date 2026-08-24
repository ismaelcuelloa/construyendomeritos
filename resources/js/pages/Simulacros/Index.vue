<script setup lang="ts">
import AppSimulacrosLayout from '@/layouts/AppSimulacrosLayout.vue';
import { Client } from '@/lib/client';
import { ref, nextTick } from 'vue';

defineProps<{
    courses: Array<{
        course: any;
        modules: any[];
    }>;
}>();

const scrollRefs = ref<Record<string, HTMLElement | null>>({});

const scroll = (courseId: number, direction: 'left' | 'right') => {
    const el = scrollRefs.value[`course-${courseId}`];
    if (!el) return;
    const scrollAmount = 360;
    el.scrollBy({ left: direction === 'left' ? -scrollAmount : scrollAmount, behavior: 'smooth' });
};
</script>

<template>
    <AppSimulacrosLayout title="Simulacros">
        <div class="simulacros-container">
            <div class="hero-section">
                <h1>Simulacros de Estudio</h1>
                <p>Pon a prueba tus conocimientos con simulacros por módulo. Cada examen está diseñado para ayudarte a prepararte mejor.</p>
            </div>

            <div v-if="courses.length === 0" class="empty-state">
                <i class="feather-book-open"></i>
                <h3>No hay simulacros disponibles</h3>
                <p>Aún no se han creado simulacros. Vuelve pronto.</p>
            </div>

            <div v-for="group in courses" :key="group.course.id" class="course-section">
                <h2 class="section-title">{{ group.course.title }}</h2>
                <div class="slider-wrapper">
                    <button class="slider-arrow slider-left" @click="scroll(group.course.id, 'left')">
                        <i class="feather-chevron-left"></i>
                    </button>
                    <div class="modules-grid" :ref="el => scrollRefs['course-' + group.course.id] = el as any">
                    <div v-for="mod in group.modules" :key="mod.id" class="exam-card">
                        <div class="exam-card-image">
                            <img
                                :src="group.course.metadata?.banner ? '/' + group.course.metadata.banner : '/assets/images/others/thumbnail-placeholder.svg'"
                                :alt="mod.title"
                            />
                            <a v-if="!(mod.exam.remaining_attempts === 0 && mod.exam.max_attempts !== null)" :href="`${Client.simulacrosUrl()}/examen/${mod.exam.id}`" class="exam-overlay">
                                <div class="overlay-content">
                                    <i class="feather-play-circle"></i>
                                    <span>Iniciar Simulacro</span>
                                </div>
                            </a>
                            <div v-else class="exam-overlay no-attempts-overlay" style="opacity:1; background: rgba(0,0,0,0.6);">
                                <div class="overlay-content">
                                    <i class="feather-alert-circle"></i>
                                    <span>Sin intentos</span>
                                </div>
                            </div>
                        </div>
                        <div class="exam-card-content">
                            <div class="exam-category-tag">
                                <i class="feather-target"></i>
                                <span>{{ mod.title }}</span>
                            </div>
                            <h4 class="exam-card-title">
                                <a :href="`${Client.simulacrosUrl()}/examen/${mod.exam.id}`">{{ mod.exam.title }}</a>
                            </h4>
                            <p v-if="mod.exam.description" class="exam-card-desc">{{ mod.exam.description }}</p>
                            <div class="exam-meta-info">
                                <div class="meta-item">
                                    <i class="feather-help-circle"></i>
                                    <span>{{ mod.exam.questions_count || 0 }} preguntas</span>
                                </div>
                                <div v-if="mod.exam.time_limit" class="meta-item">
                                    <i class="feather-clock"></i>
                                    <span>{{ mod.exam.time_limit }} min</span>
                                </div>
                                <div class="meta-item">
                                    <i class="feather-award"></i>
                                    <span>Nota mín: {{ mod.exam.passing_score }}%</span>
                                </div>
                            </div>
                            <div v-if="mod.exam.user_passed" class="exam-card-footer passed-footer">
                                <span class="passed-badge"><i class="feather-check-circle"></i> Aprobado</span>
                            </div>
                            <div v-else-if="mod.exam.remaining_attempts === 0 && mod.exam.max_attempts !== null" class="exam-card-footer no-attempts-footer">
                                <span class="no-attempts-badge"><i class="feather-alert-circle"></i> Sin intentos disponibles</span>
                            </div>
                            <div v-else class="exam-card-footer">
                                <a :href="`${Client.simulacrosUrl()}/examen/${mod.exam.id}`" class="btn-exam-start">
                                    <i class="feather-play-circle"></i>
                                    <span>Iniciar Simulacro</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    </div>
                    <button class="slider-arrow slider-right" @click="scroll(group.course.id, 'right')">
                        <i class="feather-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </AppSimulacrosLayout>
</template>

<style scoped>
.simulacros-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px 20px 60px;
}

.hero-section {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 20px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.05), rgba(26, 90, 128, 0.02));
    border-radius: 16px;
    border: 1px solid rgba(19, 58, 84, 0.08);
}
.hero-section h1 { font-size: 28px; font-weight: 900; color: #1a1a1a; margin-bottom: 8px; }
.hero-section p { font-size: 15px; color: #718096; max-width: 560px; margin: 0 auto; line-height: 1.6; }

.empty-state { text-align: center; padding: 60px 20px; color: #a0aec0; }
.empty-state i { font-size: 48px; color: rgba(19, 58, 84, 0.3); margin-bottom: 16px; }
.empty-state h3 { font-size: 18px; font-weight: 700; color: #4a5568; margin-bottom: 8px; }

.course-section { margin-bottom: 48px; }
.section-title {
    font-size: 22px; font-weight: 800; color: #1a1a1a; margin-bottom: 16px;
    padding-left: 16px; border-left: 4px solid #133a54;
}

.slider-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.slider-wrapper::before,
.slider-wrapper::after {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 60px;
    z-index: 5;
    pointer-events: none;
}
.slider-wrapper::before {
    left: 0;
    background: linear-gradient(to right, rgba(248,250,252,1), rgba(248,250,252,0));
}
.slider-wrapper::after {
    right: 0;
    background: linear-gradient(to left, rgba(248,250,252,1), rgba(248,250,252,0));
}

.slider-arrow {
    position: absolute;
    z-index: 10;
    width: 44px; height: 44px;
    border: none;
    border-radius: 50%;
    background: #fff;
    color: #133a54;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}
.slider-arrow:hover {
    background: linear-gradient(135deg, #133a54, #1a5a80);
    color: #fff;
    box-shadow: 0 6px 24px rgba(19, 58, 84, 0.3);
    transform: scale(1.05);
}
.slider-arrow i { font-size: 20px; }
.slider-left { left: -20px; }
.slider-right { right: -20px; }

.modules-grid {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 8px;
}
.modules-grid::-webkit-scrollbar { height: 6px; }
.modules-grid::-webkit-scrollbar-track { background: transparent; }
.modules-grid::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 3px; }

.exam-card {
    min-width: 340px;
    max-width: 340px;
    scroll-snap-align: start;
    flex-shrink: 0;
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid #e2e8f0;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    display: flex;
    flex-direction: column;
}
.exam-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(19, 58, 84, 0.12);
    border-color: rgba(19, 58, 84, 0.2);
}

.exam-card-image {
    position: relative;
    height: 180px;
    overflow: hidden;
}
.exam-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.exam-card:hover .exam-card-image img {
    transform: scale(1.05);
}

.exam-overlay {
    position: absolute;
    inset: 0;
    background: rgba(19, 58, 84, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    text-decoration: none;
}
.exam-card:hover .exam-overlay {
    opacity: 1;
}
.overlay-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #fff;
}
.overlay-content i { font-size: 36px; }
.overlay-content span { font-size: 15px; font-weight: 700; }

.exam-card-content {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1;
}

.exam-category-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #133a54;
    background: rgba(19, 58, 84, 0.08);
    padding: 4px 12px;
    border-radius: 20px;
    align-self: flex-start;
}
.exam-category-tag i { font-size: 13px; }

.exam-card-title {
    font-size: 16px;
    font-weight: 800;
    color: #1a1a1a;
    line-height: 1.4;
    margin: 0;
}
.exam-card-title a { color: inherit; text-decoration: none; }
.exam-card-title a:hover { color: #133a54; }

.exam-card-desc {
    font-size: 13px;
    color: #718096;
    line-height: 1.5;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.exam-meta-info {
    display: flex;
    gap: 16px;
    margin-top: auto;
}
.meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    color: #718096;
}
.meta-item i { color: #133a54; font-size: 14px; }

.exam-card-footer {
    margin-top: 8px;
    padding-top: 12px;
    border-top: 1px solid #edf2f7;
}

.btn-exam-start {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(135deg, #133a54, #1a5a80);
    color: #fff;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    box-shadow: 0 3px 12px rgba(19, 58, 84, 0.2);
    transition: all 0.3s;
}
.btn-exam-start:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.3);
}

.no-attempts-footer {
    text-align: center;
    padding-top: 8px;
}
.no-attempts-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #9b2c2c;
    background: #fed7d7;
    padding: 8px 16px;
    border-radius: 8px;
}

.passed-footer {
    text-align: center;
    padding-top: 8px;
}
.passed-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    color: #276749;
    background: #c6f6d5;
    padding: 8px 20px;
    border-radius: 8px;
}
.no-attempts-overlay {
    cursor: default;
}

@media (max-width: 992px) {
    .modules-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .modules-grid { grid-template-columns: 1fr; }
}
</style>
