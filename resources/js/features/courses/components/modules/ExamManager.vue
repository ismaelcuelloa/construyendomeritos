<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import TextArea from '@/components/ui/text-area/TextArea.vue';
import { Checkbox } from '@/components/ui/checkbox';
import Modal from '@/components/ui/modal/Modal.vue';
import { watch, ref, onMounted, reactive, computed } from 'vue';
import { ExamState } from '@/features/courses/stores/exam-manager';
import { Client } from '@/lib/client';

const props = defineProps<{ moduleId: number | string }>();
const state = reactive(new ExamState());

const form = ref({ title: '', description: '', time_limit: null as number | null, max_attempts: null as number | null, passing_score: 60, active: true });
const questionForm = ref({ question_text: '', options: { a: '', b: '', c: '', d: '' }, correct_answer: 'a', justification: '', points: 1 });
const wizardStep = ref(1);
const validationErrors = ref<string[]>([]);
const showQuestionForm = ref(false);

const steps = ['Información', 'Preguntas', 'Vista previa'];

const availableAnswerOptions = computed(() => {
    const result: { value: string; label: string }[] = [];
    const opts = questionForm.value.options;
    if (opts.a && opts.a.trim() !== '') result.push({ value: 'a', label: 'A' });
    if (opts.b && opts.b.trim() !== '') result.push({ value: 'b', label: 'B' });
    if (opts.c && opts.c.trim() !== '') result.push({ value: 'c', label: 'C' });
    if (opts.d && opts.d.trim() !== '') result.push({ value: 'd', label: 'D' });
    return result;
});

const targetCategoryId = ref<number | null>(null);
const targetCourseId = ref<number | null>(null);
const availableCourses = ref<any[]>([]);
const loadingTargetCourses = ref(false);

const flattenCourses = (categories: any[]) => {
    const result: any[] = [];
    for (const cat of categories) {
        for (const course of (cat.courses || [])) {
            result.push({ ...course, _category: cat.title, _categoryId: cat.id });
        }
        for (const sub of (cat.subcategories || [])) {
            for (const course of (sub.courses || [])) {
                result.push({ ...course, _category: cat.title, _categoryId: cat.id, _subcategory: sub.title });
            }
        }
    }
    return result;
};

const categoryOptions = computed(() => {
    const seen = new Set<number>();
    const result: { id: number; title: string }[] = [];
    for (const course of availableCourses.value) {
        if (!seen.has(course._categoryId)) {
            seen.add(course._categoryId);
            result.push({ id: course._categoryId, title: course._category });
        }
    }
    return result;
});

const filteredCourses = computed(() => {
    if (!targetCategoryId.value) return { direct: [], subs: {}, hasSubs: false };
    const courses = availableCourses.value.filter((c) => c._categoryId === targetCategoryId.value);
    const direct = courses.filter((c) => !c._subcategory);
    const subs: Record<string, any[]> = {};
    for (const c of courses) {
        if (c._subcategory) {
            if (!subs[c._subcategory]) subs[c._subcategory] = [];
            subs[c._subcategory].push(c);
        }
    }
    const hasSubs = Object.keys(subs).length > 0;
    return { direct, subs, hasSubs };
});

const handleOpenCopyModal = async () => {
    state.openCopyModal();
    targetCategoryId.value = null;
    targetCourseId.value = null;
    loadingTargetCourses.value = true;
    try {
        const response = await Client.post(Client.ADMIN_CATEGORIES + '/courses-tree', { exclude_user_id: '' });
        availableCourses.value = flattenCourses(response.data);
    } catch {
        availableCourses.value = [];
    }
    loadingTargetCourses.value = false;
};

watch(targetCategoryId, () => {
    targetCourseId.value = null;
    state.targetModuleId = null;
});

watch(targetCourseId, () => {
    state.targetModuleId = null;
    if (targetCourseId.value) {
        state.loadModulesForCourse(targetCourseId.value);
    } else {
        state.availableModules = [];
    }
});

const validateStep = (step: number): boolean => {
    validationErrors.value = [];
    if (step === 1) {
        if (!form.value.title.trim()) validationErrors.value.push('El título es obligatorio');
        if (!form.value.passing_score && form.value.passing_score !== 0) validationErrors.value.push('La nota mínima es obligatoria');
    }
    if (step === 2) {
        if (!state.exam?.questions?.length) validationErrors.value.push('Debe agregar al menos una pregunta');
    }
    return validationErrors.value.length === 0;
};

const nextStep = async () => {
    if (validateStep(wizardStep.value)) {
        if (wizardStep.value === 1 && !state.exam) {
            await state.saveExam(props.moduleId, form.value, true);
            if (!state.exam) return;
        }
        wizardStep.value++;
        validationErrors.value = [];
    }
};

const resetForm = () => {
    if (state.exam) {
        form.value = { title: state.exam.title, description: state.exam.description, time_limit: state.exam.time_limit, max_attempts: state.exam.max_attempts, passing_score: state.exam.passing_score, active: state.exam.active };
    } else {
        form.value = { title: '', description: '', time_limit: null, max_attempts: null, passing_score: 60, active: true };
    }
};

const resetQuestionForm = () => {
    if (state.examQuestionType === 'update' && state.editingQuestion) {
        const q = state.editingQuestion;
        questionForm.value = { question_text: q.question_text, options: { ...q.options }, correct_answer: q.correct_answer, justification: q.justification || '', points: q.points };
    } else {
        questionForm.value = { question_text: '', options: { a: '', b: '', c: '', d: '' }, correct_answer: 'a', justification: '', points: 1 };
    }
};

watch(() => state.showExamSheet, (val) => { if (val) { resetForm(); wizardStep.value = 1; showQuestionForm.value = false; } });

watch(() => questionForm.value.options, (opts) => {
    const cur = questionForm.value.correct_answer;
    const curVal = (opts as any)[cur];
    if (!curVal || String(curVal).trim() === '') {
        const first = availableAnswerOptions.value[0];
        if (first) questionForm.value.correct_answer = first.value;
    }
}, { deep: true });

const confirmClearQuestions = () => {
    if (window.confirm('¿Estás seguro de eliminar TODAS las preguntas del simulacro? Esta acción no se puede deshacer.')) {
        state.clearAllQuestions();
    }
};

onMounted(() => { state.loadExam(props.moduleId); });
</script>

<template>
    <template v-if="state.loadingExam">
        <div class="file-item-premium" style="width:100%"><div class="file-info"><i class="feather-loader spin"></i><h6 class="file-title">Cargando...</h6></div></div>
    </template>

    <template v-else-if="state.exam">
        <a :href="`${Client.simulacrosUrl()}/examen/${state.exam.id}`" rel="noopener" class="file-item-premium exam-file-item" style="width:100%">
            <div class="file-info">
                <i class="feather-target"></i>
                <div>
                    <h6 class="file-title exam-title">{{ state.exam.title }}</h6>
                    <span class="exam-subtitle">{{ state.exam.questions?.length || 0 }} preguntas<template v-if="state.exam.time_limit"> · {{ state.exam.time_limit }} min</template> · Nota mín: {{ state.exam.passing_score }}%</span>
                </div>
            </div>
            <div class="file-actions">
                <Button @click.prevent="state.openExamSheet()" size="sm" class="wiz-btn"><i class="feather-edit"></i> Editar</Button>
                <Button @click.prevent="handleOpenCopyModal" size="sm" class="wiz-btn"><i class="feather-copy"></i> Copiar</Button>
            </div>
        </a>
    </template>

    <Button v-else @click="state.openExamSheet()" class="btn-exam-create" size="sm">
        <i class="feather-target"></i> Crear Simulacro
    </Button>

    <Teleport to="body">
    <Modal :show="state.showExamSheet" @update:show="(val) => state.showExamSheet = val" :title="state.exam ? 'Editar Simulacro' : 'Crear Simulacro'" size="lg">
        <!-- Step indicator -->
        <div class="wizard-steps">
            <div v-for="(s, i) in steps" :key="i" class="wizard-step" :class="{ active: wizardStep === i + 1, done: wizardStep > i + 1 }">
                <div class="step-circle">{{ wizardStep > i + 1 ? '✓' : i + 1 }}</div>
                <span class="step-label">{{ s }}</span>
            </div>
        </div>

        <!-- Step 1: Info -->
        <div v-if="wizardStep === 1" class="wizard-body">
            <Input title="Título *" v-model="form.title" />
            <TextArea title="Descripción" v-model="form.description" :rows="3" />
            <div class="form-row">
                <Input title="Tiempo límite (min)" type="number" v-model="form.time_limit" placeholder="Sin límite" />
                <Input title="Máx. intentos" type="number" v-model="form.max_attempts" placeholder="Ilimitado" />
            </div>
            <Input title="Nota mínima (%) *" type="number" v-model="form.passing_score" />
            <Checkbox v-model="form.active" title="Activo" />
        </div>

        <!-- Step 2: Questions - List view -->
        <div v-else-if="wizardStep === 2 && !showQuestionForm">
            <div class="step-actions-bar">
                <Button @click="state.openQuestionCreate(); questionForm = { question_text: '', options: { a: '', b: '', c: '', d: '' }, correct_answer: 'a', justification: '', points: 1 }; showQuestionForm = true" size="sm" class="wiz-btn"><i class="feather-plus"></i> Agregar</Button>
                <Button @click="state.downloadTemplate()" size="sm" class="wiz-btn"><i class="feather-download"></i> Plantilla</Button>
                <Button @click="($refs.fileInput as HTMLInputElement).click()" size="sm" class="wiz-btn"><i class="feather-upload"></i> Importar</Button>
                <Button v-if="state.exam?.questions?.length" @click="confirmClearQuestions" size="sm" class="wiz-btn-danger"><i class="feather-trash-2"></i> Limpiar</Button>
                <input ref="fileInput" type="file" accept=".xlsx,.xls" hidden @change="(e: any) => { const f = e.target?.files?.[0]; if (f) state.importQuestions(f); e.target.value = ''; }" />
            </div>

            <div v-if="state.exam?.questions?.length" class="questions-list-wizard">
                <div v-for="(q, index) in state.exam!.questions!" :key="q.id" class="question-card-wizard">
                    <div class="qc-header">
                        <span class="qc-number">{{ index + 1 }}</span>
                        <span class="qc-text">{{ q.question_text }}</span>
                        <span class="qc-answer">{{ q.correct_answer.toUpperCase() }}</span>
                        <span class="qc-points">{{ q.points }}pts</span>
                    </div>
                    <div class="qc-actions">
                        <Button @click="state.openQuestionEdit(q); questionForm = { question_text: q.question_text, options: { ...q.options }, correct_answer: q.correct_answer, justification: q.justification || '', points: q.points }; showQuestionForm = true" variant="ghost" size="icon" title="Editar"><i class="feather-edit-2"></i></Button>
                        <Button @click="state.deleteQuestion(q.id!)" variant="ghost" size="icon" class="btn-danger-icon" title="Eliminar"><i class="feather-trash-2"></i></Button>
                    </div>
                </div>
            </div>
            <p v-else class="no-questions-wizard">No hay preguntas todavía. Agrega preguntas manualmente o importa desde un archivo Excel.</p>
        </div>

        <!-- Step 2: Questions - Form view -->
        <div v-else-if="wizardStep === 2 && showQuestionForm" class="wizard-body">
            <TextArea title="Pregunta" v-model="questionForm.question_text" :rows="3" />
            <div class="options-grid">
                <Input title="Opción A *" v-model="questionForm.options.a" />
                <Input title="Opción B *" v-model="questionForm.options.b" />
                <Input title="Opción C (opcional)" v-model="questionForm.options.c" />
                <Input title="Opción D (opcional)" v-model="questionForm.options.d" />
            </div>
            <div class="form-row">
                <div class="form-field">
                    <label class="field-label">Respuesta correcta</label>
                    <select v-model="questionForm.correct_answer" class="field-select">
                        <option v-for="opt in availableAnswerOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                </div>
                <div class="form-field">
                    <label class="field-label">Puntos</label>
                    <span class="points-auto">Automático (100 ÷ nº de preguntas)</span>
                </div>
            </div>
            <TextArea title="Justificación" v-model="questionForm.justification" :rows="3" />
            <div class="wizard-footer" style="margin-top:12px">
                <Button @click="showQuestionForm = false" size="sm" class="wiz-btn">Volver</Button>
                <Button @click="state.saveQuestion(questionForm); showQuestionForm = false" size="sm" class="wiz-btn">Guardar Pregunta</Button>
            </div>
        </div>

        <!-- Step 3: Preview -->
        <div v-else-if="wizardStep === 3" class="preview-box">
            <div class="preview-item"><strong>Título:</strong> {{ form.title }}</div>
            <div v-if="form.description" class="preview-item"><strong>Descripción:</strong> {{ form.description }}</div>
            <div class="preview-item"><strong>Tiempo límite:</strong> {{ form.time_limit ? form.time_limit + ' min' : 'Sin límite' }}</div>
            <div class="preview-item"><strong>Intentos:</strong> {{ form.max_attempts ? form.max_attempts : 'Ilimitados' }}</div>
            <div class="preview-item"><strong>Nota mínima:</strong> {{ form.passing_score }}%</div>
            <div class="preview-item"><strong>Estado:</strong> {{ form.active ? 'Activo' : 'Inactivo' }}</div>
            <div class="preview-item"><strong>Preguntas:</strong> {{ state.exam?.questions?.length || 0 }}</div>
            <div class="preview-note">Así se verá el simulacro para los estudiantes.</div>
        </div>

        <div v-if="validationErrors.length" class="validation-errors">
            <div v-for="err in validationErrors" :key="err" class="error-item">
                <i class="feather-alert-circle"></i> {{ err }}
            </div>
        </div>

        <template #footer>
            <div v-if="wizardStep === 2 && showQuestionForm"></div>
            <div v-else class="wizard-footer">
                <div class="wizard-footer-left">
                    <Button v-if="state.exam && wizardStep === 1" @click="state.deleteExam(props.moduleId)" variant="destructive" size="sm"><i class="feather-trash-2"></i> Eliminar</Button>
                    <Button @click="state.showExamSheet = false" size="sm" class="wiz-btn">Cancelar</Button>
                </div>
                <div class="wizard-footer-right">
                    <Button v-if="wizardStep > 1" @click="wizardStep--" size="sm" class="wiz-btn"><i class="feather-arrow-left"></i> Atrás</Button>
                    <Button v-if="wizardStep < 3" @click="nextStep" size="sm" class="wiz-btn">Siguiente <i class="feather-arrow-right"></i></Button>
                    <Button v-if="wizardStep === 3" :loading="state.savingExam" @click="state.saveExam(props.moduleId, form)" size="sm" class="wiz-btn">Confirmar y Guardar</Button>
                </div>
            </div>
        </template>
    </Modal>
    </Teleport>

    <Teleport to="body">
    <Modal :show="state.showCopyModal" @update:show="(val) => state.showCopyModal = val" title="Copiar Simulacro" size="md">
        <div class="copy-modal-body">
            <p class="copy-modal-info">Vas a copiar <strong>{{ state.exam?.title }}</strong> con sus {{ state.exam?.questions?.length || 0 }} preguntas a otro módulo.</p>

            <div v-if="loadingTargetCourses" class="loading-hint-exam"><span class="spinner-small-exam"></span> Cargando cursos...</div>
            <template v-else>
                <div class="copy-module-field">
                    <label class="copy-module-label">Categoría</label>
                    <select v-model="targetCategoryId" class="copy-module-select">
                        <option :value="null" disabled>-- Selecciona una categoría --</option>
                        <option v-for="cat in categoryOptions" :key="cat.id" :value="cat.id">{{ cat.title }}</option>
                    </select>
                </div>

                <div class="copy-module-field">
                    <label class="copy-module-label">Curso destino</label>
                    <select v-model="targetCourseId" class="copy-module-select" :disabled="!targetCategoryId">
                        <option :value="null" disabled>-- Selecciona un curso --</option>
                        <template v-if="filteredCourses.hasSubs">
                            <option v-for="course in filteredCourses.direct" :key="course.id" :value="course.id">{{ course.title }}</option>
                            <optgroup v-for="(courses, sub) in filteredCourses.subs" :key="sub" :label="sub">
                                <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.title }}</option>
                            </optgroup>
                        </template>
                        <template v-else>
                            <option v-for="course in filteredCourses.direct" :key="course.id" :value="course.id">{{ course.title }}</option>
                        </template>
                    </select>
                </div>

                <div class="copy-module-field">
                    <label class="copy-module-label">Módulo destino</label>
                    <div v-if="state.loadingModules" class="loading-hint-exam"><span class="spinner-small-exam"></span> Cargando módulos...</div>
                    <select v-else v-model="state.targetModuleId" class="copy-module-select" :disabled="!targetCourseId">
                        <option :value="null" disabled>-- Selecciona un módulo --</option>
                        <option v-for="mod in state.availableModules" :key="mod.id" :value="mod.id">{{ mod.title }}</option>
                    </select>
                </div>
            </template>
        </div>
        <template #footer>
            <div class="wizard-footer">
                <div></div>
                <div class="wizard-footer-right">
                    <Button @click="state.showCopyModal = false" size="sm" class="wiz-btn">Cancelar</Button>
                    <Button :disabled="!state.targetModuleId" :loading="state.copyingExam" @click="state.copyExam()" size="sm" class="wiz-btn">Copiar</Button>
                </div>
            </div>
        </template>
    </Modal>
    </Teleport>
</template>

<style scoped>
.exam-manager { display: inline-flex; align-items: center; }
.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.exam-file-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 14px 16px; margin-bottom: 10px;
    background: linear-gradient(90deg, rgba(16, 185, 129, 0.04), rgba(52, 211, 153, 0.02));
    border: 1px solid rgba(16, 185, 129, 0.15);
    border-radius: 10px; text-decoration: none !important;
    transition: all 0.3s ease;
}
.exam-file-item:hover {
    background: linear-gradient(90deg, rgba(16, 185, 129, 0.08), rgba(52, 211, 153, 0.04));
    border-color: rgba(16, 185, 129, 0.35);
    transform: translateX(4px);
    box-shadow: 0 2px 12px rgba(16, 185, 129, 0.1);
}
.exam-file-item .feather-target { font-size: 20px; color: #10b981; flex-shrink: 0; }
.exam-title { color: #1a1a1a !important; font-size: 14px; font-weight: 700; margin: 0; }
.exam-subtitle { font-size: 12px; font-weight: 500; color: #718096; margin-top: 2px; }

.btn-exam-create {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    padding: 8px 16px !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    border: 2px solid transparent !important;
    box-shadow: 0 3px 10px rgba(19, 58, 84, 0.2) !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
}
.btn-exam-create:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(19, 58, 84, 0.3) !important;
}
.btn-danger-icon { color: #e53e3e !important; }

/* Wizard */
.wizard-steps { display: flex; justify-content: center; gap: 32px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid #e2e8f0; }
.wizard-step { display: flex; flex-direction: column; align-items: center; gap: 6px; opacity: 0.4; transition: opacity 0.3s; }
.wizard-step.active, .wizard-step.done { opacity: 1; }
.step-circle { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; background: #e2e8f0; color: #718096; transition: all 0.3s; }
.wizard-step.active .step-circle { background: linear-gradient(135deg, #133a54, #1a5a80); color: #fff; }
.wizard-step.done .step-circle { background: #10b981; color: #fff; }
.step-label { font-size: 12px; font-weight: 600; color: #4a5568; }

/* Preview */
.preview-box { background: #f8fafc; border-radius: 10px; padding: 20px; border: 1px solid #e2e8f0; }
.wizard-body { display: flex; flex-direction: column; gap: 4px; }

.validation-errors {
    margin-top: 12px;
    padding: 10px 14px;
    background: #fff5f5;
    border: 1px solid #feb2b2;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.error-item {
    font-size: 13px;
    color: #c53030;
    display: flex;
    align-items: center;
    gap: 6px;
}
.error-item i { font-size: 14px; }
.preview-item { padding: 8px 0; border-bottom: 1px solid #edf2f7; font-size: 14px; color: #4a5568; }
.preview-item:last-child { border-bottom: none; }
.preview-item strong { color: #2d3748; margin-right: 6px; }
.preview-note { margin-top: 12px; padding: 10px 14px; background: #ebf8ff; border-radius: 8px; font-size: 13px; color: #2b6cb0; text-align: center; border: 1px solid #bee3f8; }
.form-row { display: flex; gap: 12px; }
.form-row > * { flex: 1; }
.options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.form-field { display: flex; flex-direction: column; gap: 4px; }
.field-label { font-size: 12px; font-weight: 700; color: #4a5568; }
.field-select { padding: 6px 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-weight: 600; color: #2d3748; background: #fff; outline: none; height: 36px; }
.field-select:focus { border-color: #133a54; }

.copy-modal-body { display: flex; flex-direction: column; gap: 16px; }
.copy-modal-info { font-size: 15px; color: #4a5568; line-height: 1.6; margin: 0; padding: 16px 20px; background: rgba(19, 58, 84, 0.04); border-left: 4px solid #133a54; border-radius: 8px; }
.copy-modal-info strong { color: #1a1a1a; }

.copy-module-label {
    display: block;
    font-weight: 700;
    font-size: 14px;
    color: #2d3748;
    margin-bottom: 6px;
}

.copy-module-field {
    display: flex;
    flex-direction: column;
    margin-bottom: 16px;
}

.copy-module-select {
    width: 100%;
    padding: 12px 40px 12px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #2d3748;
    background: #fff;
    cursor: pointer;
    transition: border-color 0.2s;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1.5L6 6.5L11 1.5' stroke='%23f07900' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    outline: none;
}

.copy-module-select:focus {
    border-color: #133a54;
    box-shadow: none;
}

.copy-module-select:disabled {
    background: #f5f5f5;
    cursor: not-allowed;
    opacity: 0.7;
}

.copy-module-select optgroup {
    font-weight: 700;
    font-size: 13px;
    color: #133a54;
}

.copy-module-select option {
    font-weight: 600;
    color: #2d3748;
}
.points-auto {
    display: inline-block;
    font-size: 12px;
    font-weight: 600;
    color: #10b981;
    background: #d1fae5;
    padding: 8px 12px;
    border-radius: 6px;
}
.wizard-footer { display: flex; justify-content: space-between; align-items: center; width: 100%; }
.wizard-footer-left { display: flex; gap: 8px; }
.wizard-footer-right { display: flex; gap: 8px; }
.footer-buttons-modal { display: flex; gap: 10px; justify-content: flex-end; width: 100%; }
:deep(.modal-backdrop) { background: rgba(0, 0, 0, 0.5) !important; }
:deep([data-slot="button"]) { border-radius: 0.5rem !important; }

.wiz-btn {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    border: 2px solid transparent !important;
    box-shadow: 0 3px 10px rgba(19, 58, 84, 0.2) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 8px 16px !important;
}
.wiz-btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 18px rgba(19, 58, 84, 0.3) !important;
}

.wiz-btn-danger {
    background: linear-gradient(135deg, #e53e3e 0%, #f56565 100%) !important;
    color: #ffffff !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    border: 2px solid transparent !important;
    box-shadow: 0 3px 10px rgba(229, 62, 62, 0.2) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 8px 16px !important;
}
.wiz-btn-danger:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 18px rgba(229, 62, 62, 0.3) !important;
}

.step-actions-bar {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}
.import-label { cursor: pointer !important; }

.wiz-btn-upload {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    border: 2px solid transparent !important;
    box-shadow: 0 3px 10px rgba(19, 58, 84, 0.2) !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 8px 16px !important;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}
.wiz-btn-upload:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 18px rgba(19, 58, 84, 0.3) !important;
}

.wiz-btn-outline {
    background: #fff !important;
    color: #133a54 !important;
    border: 2px solid #133a54 !important;
    border-radius: 10px !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    padding: 7px 14px !important;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    text-decoration: none;
}
.wiz-btn-outline:hover {
    background: #133a54 !important;
    color: #fff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.2);
}

.questions-list-wizard {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 12px;
    max-height: 320px;
    overflow-y: auto;
}

.question-card-wizard {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    transition: all 0.2s;
}
.question-card-wizard:hover { border-color: #133a54; box-shadow: 0 2px 8px rgba(19,58,84,0.08); }

.qc-header {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}
.qc-number {
    width: 28px; height: 28px;
    border-radius: 8px;
    background: rgba(19,58,84,0.08);
    color: #133a54;
    font-weight: 800;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.qc-text {
    flex: 1;
    font-size: 13px;
    font-weight: 500;
    color: #2d3748;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.4;
}
.qc-answer {
    font-size: 11px;
    font-weight: 800;
    color: #10b981;
    background: #d1fae5;
    padding: 2px 8px;
    border-radius: 4px;
    text-transform: uppercase;
}
.qc-points {
    font-size: 11px;
    font-weight: 700;
    color: #718096;
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 4px;
}
.qc-actions {
    display: flex;
    gap: 2px;
    margin-left: 8px;
}

.no-questions-wizard {
    text-align: center;
    color: #a0aec0;
    font-size: 14px;
    padding: 32px 16px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px dashed #e2e8f0;
}

.loading-hint-exam {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 0; font-size: 13px; color: #718096;
}
.spinner-small-exam {
    width: 14px; height: 14px;
    border: 2px solid rgba(19, 58, 84, 0.2);
    border-top-color: #133a54;
    border-radius: 50%;
    animation: spin 0.5s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.file-actions { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }
</style>
