<script setup lang="ts">
import AppSimulacrosLayout from '@/layouts/AppSimulacrosLayout.vue';
import { computed } from 'vue';
import { Client } from '@/lib/client';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    attempt: any;
    firstFileId?: number;
    remainingAttempts: number | null;
    userPassed: boolean;
}>();

const page = usePage();
const mainDomain = (page.props as any).mainDomain;

const mainUrl = (path: string) => {
    if (mainDomain) {
        return 'https://' + mainDomain + path;
    }
    return path;
};

const percentage = computed(() => {
    if (!props.attempt.total_points) return 0;
    return Math.round((props.attempt.score / props.attempt.total_points) * 100);
});

const exam = computed(() => props.attempt.exam);

const filterOptions = (options: Record<string, string> | null) => {
    const opts = options || {};
    const filtered: Record<string, string> = {};
    for (const [letter, text] of Object.entries(opts)) {
        if (text !== null && text !== undefined && String(text).trim() !== '') {
            filtered[letter] = text;
        }
    }
    return filtered;
};
</script>

<template>
    <AppSimulacrosLayout title="Resultados">
        <div class="results-container">
            <div class="results-card">
                <div class="results-header" :class="attempt.passed ? 'passed' : 'failed'">
                    <div class="result-icon">
                        <i v-if="attempt.passed" class="feather-check-circle"></i>
                        <i v-else class="feather-x-circle"></i>
                    </div>
                    <h1>{{ attempt.passed ? '¡Aprobaste!' : 'No aprobaste' }}</h1>
                    <p class="exam-title-result">{{ exam.title }}</p>
                </div>

                <div class="score-section">
                    <div class="score-circle" :class="attempt.passed ? 'passed' : 'failed'">
                        <span class="score-number">{{ percentage }}%</span>
                        <span class="score-label">Nota mínima: {{ exam.passing_score }}%</span>
                    </div>
                    <div class="score-details">
                        <div class="detail-item">
                            <span class="detail-label">Puntaje</span>
                            <span class="detail-value">{{ attempt.score }} / {{ attempt.total_points }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Preguntas</span>
                            <span class="detail-value">{{ attempt.answers?.length || 0 }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Correctas</span>
                            <span class="detail-value correct">{{ attempt.answers?.filter((a: any) => a.is_correct).length || 0 }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Incorrectas</span>
                            <span class="detail-value incorrect">{{ attempt.answers?.filter((a: any) => a.selected_answer && !a.is_correct).length || 0 }}</span>
                        </div>
                    </div>
                </div>

                <div class="actions-row">
                    <a v-if="firstFileId" :href="mainUrl(`/cursos/modulos/archivos/${firstFileId}`)" class="btn-back">
                        <i class="feather-arrow-left"></i> Regresar al material
                    </a>
                    <a v-if="remainingAttempts === null || remainingAttempts > 0" :href="`${Client.simulacrosUrl()}/examen/${exam.id}`" class="btn-retry">
                        <i class="feather-refresh-cw"></i> Volver a intentar
                    </a>
                    <a :href="mainUrl('/simulacros')" class="btn-home">
                        Ver más simulacros
                    </a>
                </div>
            </div>

            <div class="questions-review">
                <h2>Revisión de preguntas</h2>
                <div
                    v-for="answer in attempt.answers"
                    :key="answer.id"
                    class="review-card"
                    :class="{
                        'review-correct': answer.is_correct,
                        'review-incorrect': answer.selected_answer && !answer.is_correct,
                        'review-empty': !answer.selected_answer,
                    }"
                >
                    <div class="review-header">
                        <span class="review-badge" :class="{
                            'badge-correct': answer.is_correct,
                            'badge-incorrect': answer.selected_answer && !answer.is_correct,
                            'badge-empty': !answer.selected_answer,
                        }">
                            {{ answer.is_correct ? 'Correcta' : (answer.selected_answer ? 'Incorrecta' : 'Sin responder') }}
                        </span>
                    </div>
                    <p class="review-question">{{ answer.question.question_text }}</p>
                    <div class="review-answers">
                        <div
                            v-for="(text, letter) in filterOptions(answer.question.options)"
                            :key="letter"
                            class="review-option"
                            :class="{
                                'opt-correct': letter === answer.question.correct_answer,
                                'opt-selected': letter === answer.selected_answer,
                                'opt-wrong': letter === answer.selected_answer && letter !== answer.question.correct_answer,
                            }"
                        >
                            <span class="opt-letter">{{ letter.toUpperCase() }}</span>
                            <span class="opt-text">{{ text }}</span>
                            <i v-if="letter === answer.question.correct_answer" class="feather-check opt-icon-correct"></i>
                            <i v-if="letter === answer.selected_answer && letter !== answer.question.correct_answer" class="feather-x opt-icon-wrong"></i>
                        </div>
                    </div>
                    <div v-if="answer.question.justification" class="review-justification">
                        <strong>Justificación:</strong> {{ answer.question.justification }}
                    </div>
                </div>
            </div>
        </div>
    </AppSimulacrosLayout>
</template>

<style scoped>
.results-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
}

.results-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    border: 2px solid #e2e8f0;
    margin-bottom: 32px;
}

.results-header {
    text-align: center;
    padding: 40px 20px 30px;
}

.results-header.passed {
    background: linear-gradient(135deg, #c6f6d5, #e6fffa);
}

.results-header.failed {
    background: linear-gradient(135deg, #fed7d7, #fff5f5);
}

.result-icon i {
    font-size: 56px;
}

.results-header.passed .result-icon i {
    color: #38a169;
}

.results-header.failed .result-icon i {
    color: #e53e3e;
}

.results-header h1 {
    font-size: 28px;
    font-weight: 900;
    margin: 12px 0 8px;
}

.results-header.passed h1 { color: #276749; }
.results-header.failed h1 { color: #9b2c2c; }

.exam-title-result {
    font-size: 14px;
    color: #718096;
}

.score-section {
    display: flex;
    align-items: center;
    gap: 40px;
    padding: 32px;
}

.score-circle {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.score-circle.passed {
    background: #c6f6d5;
    border: 4px solid #38a169;
}

.score-circle.failed {
    background: #fed7d7;
    border: 4px solid #e53e3e;
}

.score-number {
    font-size: 36px;
    font-weight: 900;
}

.score-circle.passed .score-number { color: #276749; }
.score-circle.failed .score-number { color: #9b2c2c; }

.score-label {
    font-size: 11px;
    font-weight: 600;
    color: #718096;
    text-align: center;
}

.score-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    flex: 1;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.detail-label {
    font-size: 12px;
    font-weight: 600;
    color: #a0aec0;
    text-transform: uppercase;
}

.detail-value {
    font-size: 20px;
    font-weight: 800;
    color: #2d3748;
}

.detail-value.correct { color: #38a169; }
.detail-value.incorrect { color: #e53e3e; }

.actions-row {
    display: flex;
    gap: 12px;
    padding: 0 32px 32px;
}

.btn-retry, .btn-home, .btn-back {
    flex: 1;
    padding: 14px 20px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-back {
    background: #f7fafc;
    color: #4a5568;
    border: 2px solid #e2e8f0;
}

.btn-back:hover {
    border-color: #cbd5e0;
    background: #edf2f7;
}

.btn-retry {
    background: linear-gradient(135deg, #133a54, #1a5a80);
    color: #fff;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.2);
}

.btn-retry:hover {
    transform: translateY(-2px);
}

.btn-home {
    background: #f7fafc;
    color: #4a5568;
    border: 2px solid #e2e8f0;
}

.btn-home:hover {
    border-color: #cbd5e0;
}

/* Questions Review */
.questions-review h2 {
    font-size: 20px;
    font-weight: 800;
    color: #2d3748;
    margin-bottom: 20px;
}

.review-card {
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 16px;
}

.review-correct { border-left: 4px solid #38a169; }
.review-incorrect { border-left: 4px solid #e53e3e; }
.review-empty { border-left: 4px solid #ecc94b; }

.review-header {
    margin-bottom: 12px;
}

.review-badge {
    font-size: 12px;
    font-weight: 700;
    padding: 3px 12px;
    border-radius: 20px;
}

.badge-correct { background: #c6f6d5; color: #276749; }
.badge-incorrect { background: #fed7d7; color: #9b2c2c; }
.badge-empty { background: #fefcbf; color: #975a16; }

.review-question {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.6;
    margin-bottom: 16px;
}

.review-answers {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.review-option {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 8px;
    background: #f7fafc;
    border: 1px solid #e2e8f0;
}

.opt-correct {
    background: #c6f6d5;
    border-color: #9ae6b4;
}

.opt-wrong {
    background: #fed7d7;
    border-color: #feb2b2;
}

.opt-selected:not(.opt-correct) {
    border-color: #e53e3e;
}

.opt-letter {
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 13px;
    color: #718096;
    flex-shrink: 0;
}

.opt-correct .opt-letter { background: #38a169; color: #fff; }
.opt-wrong .opt-letter { background: #e53e3e; color: #fff; }

.opt-text {
    flex: 1;
    font-size: 14px;
    color: #4a5568;
}

.opt-icon-correct { color: #38a169; font-size: 16px; }
.opt-icon-wrong { color: #e53e3e; font-size: 16px; }

.review-justification {
    margin-top: 14px;
    padding: 14px 16px;
    background: #ebf8ff;
    border-radius: 8px;
    font-size: 14px;
    color: #2c5282;
    line-height: 1.6;
    border: 1px solid #bee3f8;
}

.review-justification strong {
    display: block;
    margin-bottom: 4px;
    color: #2b6cb0;
}
</style>
