<script setup lang="ts">
import AppSimulacrosLayout from '@/layouts/AppSimulacrosLayout.vue';
import { Client } from '@/lib/client';
import Toast from '@/composables/toast';
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    exam: any;
    questions: any[];
    remainingAttempts: number | null;
    previousAttempts: any[];
    inProgress: { id: number; started_at: string; time_limit: number | null; last_question_index?: number } | null;
    savedAnswers: Record<string, string | null>;
    userPassed: boolean;
}>();

const currentQuestion = ref(0);
const answers = ref<Record<number, string | null>>({});
const showQuestionsMenu = ref(false);
const timeLeft = ref(props.exam.time_limit ? props.exam.time_limit * 60 : 0);
const started = ref(false);
const attemptId = ref<number | null>(props.inProgress?.id ?? null);
const submitting = ref(false);
const timerInterval = ref<any>(null);
let saveTimer: any = null;

const totalQuestions = computed(() => props.questions.length);
const answeredCount = computed(() => Object.values(answers.value).filter(a => a !== null && a !== undefined).length);

const question = computed(() => props.questions[currentQuestion.value]);
const currentOptions = computed(() => {
    const opts = question.value?.options || {};
    const filtered: Record<string, string> = {};
    for (const [letter, text] of Object.entries(opts)) {
        if (text !== null && text !== undefined && String(text).trim() !== '') {
            filtered[letter] = text;
        }
    }
    return filtered;
});
const selectedAnswer = computed({
    get: () => answers.value[question.value?.id] ?? null,
    set: (val) => {
        if (question.value) {
            answers.value[question.value.id] = val;
        }
    },
});

const progressPercent = computed(() => {
    if (totalQuestions.value === 0) return 0;
    return Math.round((answeredCount.value / totalQuestions.value) * 100);
});

const canAttempt = computed(() => props.remainingAttempts === null || props.remainingAttempts > 0);

const formattedTime = computed(() => {
    const mins = Math.floor(timeLeft.value / 60);
    const secs = timeLeft.value % 60;
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
});

const timeWarning = computed(() => timeLeft.value <= 60);

const startTimer = () => {
    timerInterval.value = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timerInterval.value);
            submitExam();
        }
    }, 1000);
};

const startExam = async () => {
    try {
        const response = await Client.post(`${Client.simulacrosUrl()}/iniciar`, { exam_id: props.exam.id });
        attemptId.value = response.data.attempt.id;
        started.value = true;

        if (response.data.savedAnswers) {
            for (const [qid, ans] of Object.entries(response.data.savedAnswers)) {
                answers.value[parseInt(qid)] = ans as string | null;
            }
        }

        if (props.exam.time_limit) {
            const startedAt = new Date(response.data.attempt.started_at).getTime();
            const elapsed = Math.max(0, Math.floor((Date.now() - startedAt) / 1000));
            timeLeft.value = Math.max(0, props.exam.time_limit * 60 - elapsed);
            if (timeLeft.value <= 0) {
                submitExam();
                return;
            }
            startTimer();
        }
    } catch (e: any) {
        Toast.error(e?.response?.data?.message || 'Error al iniciar el simulacro');
    }
};

const submitExam = async () => {
    if (submitting.value) return;
    submitting.value = true;
    clearInterval(timerInterval.value);

    try {
        const payload = Object.entries(answers.value).map(([question_id, selected_answer]) => ({
            question_id: parseInt(question_id),
            selected_answer,
        }));

        const response = await Client.post(`${Client.simulacrosUrl()}/enviar`, {
            attempt_id: attemptId.value,
            answers: payload,
        });

        router.visit(`${Client.simulacrosUrl()}/resultados/${response.data.attempt.id}`);
    } catch (e: any) {
        Toast.error(e?.response?.data?.message || 'Error al enviar el simulacro');
        submitting.value = false;
    }
};

const goToQuestion = (index: number) => {
    currentQuestion.value = index;
    showQuestionsMenu.value = false;
};

const nextQuestion = () => {
    if (currentQuestion.value < totalQuestions.value - 1) {
        currentQuestion.value++;
    }
};

const prevQuestion = () => {
    if (currentQuestion.value > 0) {
        currentQuestion.value--;
    }
};

const selectOption = (letter: string) => {
    selectedAnswer.value = letter;
    scheduleSave();
};

const scheduleSave = () => {
    if (saveTimer) clearTimeout(saveTimer);
    saveTimer = setTimeout(() => {
        saveProgress();
    }, 1500);
};

const saveProgress = async () => {
    if (!attemptId.value) return;
    const payload = Object.entries(answers.value).map(([question_id, selected_answer]) => ({
        question_id: parseInt(question_id),
        selected_answer,
    }));
    try {
        await Client.post(`${Client.simulacrosUrl()}/guardar-progreso`, {
            attempt_id: attemptId.value,
            answers: payload,
        });
    } catch {
        // Silencioso: el auto-guardado no debe molestar al usuario
    }
};

// Guardar la pregunta actual inmediatamente (sin debounce) para evitar condición de carrera
const saveQuestionIndex = () => {
    if (!attemptId.value) return;
    try {
        const payload = {
            attempt_id: attemptId.value,
            current_question: currentQuestion.value,
        };
        const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
        navigator.sendBeacon(`${Client.simulacrosUrl()}/guardar-progreso`, blob);
    } catch {
        // ignorar
    }
};

watch(currentQuestion, () => {
    if (started.value && attemptId.value) {
        saveQuestionIndex();
    }
});

onMounted(() => {
    answers.value = {};
    props.questions.forEach(q => {
        answers.value[q.id] = props.savedAnswers?.[q.id] ?? null;
    });

    if (props.inProgress) {
        attemptId.value = props.inProgress.id;
        // Restaurar la pregunta donde quedó el usuario (desde la BD)
        const savedIndex = props.inProgress.last_question_index ?? 0;
        if (savedIndex >= 0 && savedIndex < props.questions.length) {
            currentQuestion.value = savedIndex;
        }
    }
});

onBeforeUnmount(() => {
    clearInterval(timerInterval.value);
    if (saveTimer) clearTimeout(saveTimer);

    if (attemptId.value) {
        // Guardado confiable al salir (sendBeacon completa aunque se cierre/navegue la página)
        const payload = {
            attempt_id: attemptId.value,
            answers: Object.entries(answers.value).map(([question_id, selected_answer]) => ({
                question_id: parseInt(question_id),
                selected_answer,
            })),
            current_question: currentQuestion.value,
        };
        try {
            const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
            navigator.sendBeacon(`${Client.simulacrosUrl()}/guardar-progreso`, blob);
        } catch {
            saveProgress();
        }
    }
});
</script>

<template>
    <AppSimulacrosLayout :title="exam.title">
        <div v-if="!started" class="exam-intro">
            <div class="intro-card">
                <div class="intro-breadcrumb">
                    <a :href="`/cursos/${exam.module.course.slug}`">{{ exam.module.course.title }}</a>
                    <span>/</span>
                    <span>{{ exam.module.title }}</span>
                </div>

                <h1>{{ exam.title }}</h1>
                <p v-if="exam.description" class="intro-desc">{{ exam.description }}</p>

                <div class="intro-meta">
                    <div class="meta-item"><i class="feather-help-circle"></i><span>{{ totalQuestions }} preguntas</span></div>
                    <div v-if="exam.time_limit" class="meta-item"><i class="feather-clock"></i><span>{{ exam.time_limit }} min</span></div>
                    <div class="meta-item"><i class="feather-award"></i><span>Nota mín: {{ exam.passing_score }}%</span></div>
                </div>

                <div class="intro-bottom">
                    <div v-if="canAttempt || inProgress" class="start-area">
                        <button @click="startExam" class="btn-start">
                            <i :class="inProgress ? 'feather-refresh-cw' : (previousAttempts.length > 0 ? 'feather-refresh-cw' : 'feather-play')"></i>
                            {{ inProgress ? 'Continuar Simulacro' : (previousAttempts.length > 0 ? 'Repetir Simulacro' : 'Iniciar Simulacro') }}
                        </button>
                        <span v-if="userPassed && !inProgress" class="passed-badge-inline">
                            <i class="feather-check-circle"></i> Aprobado
                        </span>
                    </div>
                    <div v-else-if="userPassed" class="passed-box">
                        <i class="feather-check-circle"></i>
                        <span>Simulacro aprobado</span>
                    </div>
                    <div v-else class="no-attempts-box">
                        <i class="feather-info"></i>
                        <span>Has alcanzado el límite de intentos</span>
                    </div>

                    <div v-if="previousAttempts.length > 0" class="previous-attempts">
                        <span class="attempts-label">Historial</span>
                        <div class="attempts-list">
                            <div v-for="att in previousAttempts.slice(0, 5)" :key="att.id" class="attempt-row">
                                <span :class="att.passed ? 'badge-pass' : 'badge-fail'">{{ att.passed ? 'Aprobado' : 'No aprobado' }}</span>
                                <span class="attempt-score">{{ att.score }}/{{ att.total_points }}</span>
                                <span class="attempt-date">{{ new Date(att.finished_at).toLocaleDateString() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="exam-container">
            <div class="exam-header">
                <div class="exam-header-left">
                    <span class="question-counter">Pregunta {{ currentQuestion + 1 }} de {{ totalQuestions }}</span>
                    <span class="answered-badge">{{ answeredCount }} respondidas</span>
                </div>
                <div v-if="exam.time_limit" class="timer" :class="{ 'timer-warning': timeWarning }">
                    <i class="feather-clock"></i> {{ formattedTime }}
                </div>
            </div>

            <div class="progress-bar">
                <div class="progress-fill" :style="{ width: progressPercent + '%' }"></div>
            </div>

            <button class="questions-menu-toggle" @click="showQuestionsMenu = !showQuestionsMenu">
                <i class="feather-grid"></i>
                Preguntas ({{ currentQuestion + 1 }}/{{ totalQuestions }})
                <i :class="showQuestionsMenu ? 'feather-chevron-up' : 'feather-chevron-down'"></i>
            </button>

            <div class="exam-body">
                <div class="exam-sidebar" :class="{ 'sidebar-open': showQuestionsMenu }">
                    <button
                        v-for="(q, index) in questions"
                        :key="q.id"
                        @click="goToQuestion(index)"
                        class="question-dot"
                        :class="{
                            active: index === currentQuestion,
                            answered: answers[q.id] !== null && answers[q.id] !== undefined,
                        }"
                    >
                        {{ index + 1 }}
                    </button>
                </div>

                <div class="exam-content">
                    <div class="question-card">
                        <h3 class="question-text">{{ question.question_text }}</h3>
                        <span class="question-points">{{ question.points }} punto{{ question.points !== 1 ? 's' : '' }}</span>

                        <div class="options-list">
                            <button
                                v-for="(text, letter) in currentOptions"
                                :key="letter"
                                @click="selectOption(letter as string)"
                                class="option-btn"
                                :class="{ selected: selectedAnswer === letter }"
                            >
                                <span class="option-letter">{{ letter.toUpperCase() }}</span>
                                <span class="option-text">{{ text }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="exam-nav">
                        <button @click="prevQuestion" :disabled="currentQuestion === 0" class="btn-nav">
                            <i class="feather-chevron-left"></i> Anterior
                        </button>
                        <button
                            v-if="currentQuestion < totalQuestions - 1"
                            @click="nextQuestion"
                            class="btn-nav"
                        >
                            Siguiente <i class="feather-chevron-right"></i>
                        </button>
                        <button
                            v-else
                            @click="submitExam"
                            :disabled="submitting"
                            class="btn-submit"
                        >
                            <span v-if="submitting" class="spinner-small"></span>
                            {{ submitting ? 'Enviando...' : 'Finalizar Simulacro' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppSimulacrosLayout>
</template>

<style scoped>
/* Intro */
.exam-intro {
    max-width: 720px;
    margin: 32px auto 60px;
    padding: 0 20px;
}

.intro-card {
    background: #fff;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.intro-breadcrumb {
    font-size: 13px;
    color: #a0aec0;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #edf2f7;
}
.intro-breadcrumb a { color: #133a54; text-decoration: none; font-weight: 600; }
.intro-breadcrumb span { margin: 0 6px; }

.intro-card h1 {
    font-size: 26px;
    font-weight: 900;
    color: #1a1a1a;
    margin-bottom: 12px;
    line-height: 1.3;
}

.intro-desc {
    color: #718096;
    line-height: 1.7;
    margin-bottom: 24px;
    font-size: 15px;
}

.intro-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 32px;
}
.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #4a5568;
    background: #f7fafc;
    padding: 10px 18px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}
.meta-item i { color: #133a54; font-size: 16px; }

.intro-bottom {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding-top: 24px;
    border-top: 1px solid #edf2f7;
}

.start-area {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.passed-badge-inline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    color: #166534;
    background: #d1fae5;
    padding: 6px 14px;
    border-radius: 20px;
}

.btn-start {
    background: linear-gradient(135deg, #133a54, #1a5a80);
    color: #fff;
    border: none;
    padding: 16px 48px;
    font-size: 17px;
    font-weight: 800;
    border-radius: 12px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 20px rgba(19, 58, 84, 0.3);
    transition: all 0.3s;
}
.btn-start:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(19, 58, 84, 0.4); }

.no-attempts-box {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px;
    background: #fff5f5;
    border: 1px solid #feb2b2;
    border-radius: 10px;
    color: #9b2c2c;
    font-weight: 600;
    font-size: 14px;
}
.no-attempts-box i { color: #e53e3e; }

.passed-box {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 18px;
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 10px;
    color: #166534;
    font-weight: 700;
    font-size: 16px;
}
.passed-box i { color: #16a34a; font-size: 22px; }

.previous-attempts {
    background: #f7fafc;
    border-radius: 10px;
    padding: 16px;
    border: 1px solid #e2e8f0;
}
.attempts-label {
    font-size: 12px;
    font-weight: 700;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.attempts-list {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.attempt-row {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    padding: 6px 8px;
    border-radius: 6px;
}
.attempt-row:hover { background: #edf2f7; }
.attempt-score { font-weight: 700; color: #2d3748; margin-left: auto; }
.attempt-date { color: #a0aec0; font-size: 11px; }
.badge-pass { background: #c6f6d5; color: #276749; padding: 3px 10px; border-radius: 20px; font-weight: 700; font-size: 11px; }
.badge-fail { background: #fed7d7; color: #9b2c2c; padding: 3px 10px; border-radius: 20px; font-weight: 700; font-size: 11px; }
.badge-fail { background: #fed7d7; color: #9b2c2c; padding: 2px 8px; border-radius: 20px; font-weight: 700; font-size: 10px; white-space: nowrap; }

/* Exam Container */
.exam-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 20px;
}

.exam-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.exam-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.question-counter {
    font-weight: 700;
    font-size: 15px;
    color: #2d3748;
}

.answered-badge {
    font-size: 12px;
    font-weight: 600;
    color: #133a54;
    background: rgba(19, 58, 84, 0.08);
    padding: 3px 10px;
    border-radius: 20px;
}

.timer {
    font-size: 22px;
    font-weight: 800;
    color: #2d3748;
    background: #f7fafc;
    padding: 8px 18px;
    border-radius: 10px;
    border: 2px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 6px;
}

.timer-warning {
    color: #e53e3e;
    border-color: #fed7d7;
    background: #fff5f5;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.progress-bar {
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
    margin-bottom: 24px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #133a54, #1a5a80);
    border-radius: 2px;
    transition: width 0.3s ease;
}

.exam-body {
    display: flex;
    gap: 24px;
}

.exam-sidebar {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-content: flex-start;
    width: 220px;
    flex-shrink: 0;
}

.question-dot {
    width: 36px;
    height: 36px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    font-weight: 700;
    font-size: 13px;
    color: #a0aec0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.question-dot.active {
    border-color: #133a54;
    color: #133a54;
    background: rgba(19, 58, 84, 0.05);
}

.question-dot.answered {
    background: #133a54;
    color: #fff;
    border-color: #133a54;
}

.question-dot.answered.active {
    background: #133a54;
    color: #fff;
    box-shadow: 0 0 0 3px rgba(19, 58, 84, 0.2);
}

.exam-content {
    flex: 1;
    min-width: 0;
}

.question-card {
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 20px;
}

.question-text {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.6;
    margin-bottom: 8px;
}

.question-points {
    font-size: 13px;
    font-weight: 600;
    color: #133a54;
    margin-bottom: 24px;
    display: inline-block;
    background: rgba(19, 58, 84, 0.06);
    padding: 3px 10px;
    border-radius: 20px;
}

.options-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.option-btn {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    background: #fff;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    text-align: left;
    transition: all 0.2s;
    font-size: 15px;
}

.option-btn:hover {
    border-color: #cbd5e0;
    background: #f7fafc;
}

.option-btn.selected {
    border-color: #133a54;
    background: rgba(19, 58, 84, 0.05);
}

.option-letter {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f7fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 14px;
    color: #718096;
    flex-shrink: 0;
    border: 1px solid #e2e8f0;
}

.option-btn.selected .option-letter {
    background: #133a54;
    color: #fff;
    border-color: #133a54;
}

.option-text {
    color: #2d3748;
    font-weight: 500;
    line-height: 1.5;
}

.exam-nav {
    display: flex;
    justify-content: space-between;
}

.btn-nav {
    padding: 12px 24px;
    border: 2px solid #e2e8f0;
    background: #fff;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    color: #4a5568;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-nav:hover:not(:disabled) {
    border-color: #133a54;
    color: #133a54;
}

.btn-nav:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.btn-submit {
    padding: 12px 28px;
    background: linear-gradient(135deg, #133a54, #1a5a80);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 800;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(19, 58, 84, 0.25);
    transition: all 0.3s;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.35);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.spinner-small {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.5s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Selector móvil de preguntas */
.questions-menu-toggle {
    display: none;
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    color: #2d3748;
    background: #fff;
    cursor: pointer;
    margin-bottom: 16px;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.questions-menu-toggle i {
    color: #133a54;
    font-size: 16px;
}

@media (max-width: 768px) {
    .exam-body {
        flex-direction: column;
    }
    .questions-menu-toggle {
        display: flex;
    }
    .exam-sidebar {
        display: none;
        width: 100%;
    }
    .exam-sidebar.sidebar-open {
        display: flex;
    }
    .question-counter {
        display: none;
    }
}
</style>
