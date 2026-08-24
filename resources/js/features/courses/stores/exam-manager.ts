import Toast from '@/composables/toast';
import { Client } from '@/lib/client';
import type { Ref } from 'vue';

export interface Exam {
    id?: number;
    module_id: number;
    title: string;
    description: string | null;
    time_limit: number | null;
    max_attempts: number | null;
    passing_score: number;
    active: boolean;
    questions?: ExamQuestion[];
}

export interface ExamQuestion {
    id?: number;
    exam_id: number;
    question_text: string;
    options: { a: string; b: string; c: string; d: string };
    correct_answer: string;
    justification: string | null;
    points: number;
    order_no: number;
}

export class ExamState {
    exam = null as Exam | null;
    loadingExam = false;
    savingExam = false;
    showExamSheet = false;
    examQuestionType: 'create' | 'update' = 'create';
    editingQuestion: ExamQuestion | null = null;
    showQuestionSheet = false;
    showCopyModal = false;
    targetModuleId: number | null = null;
    availableModules: any[] = [];
    loadingModules = false;
    copyingExam = false;
    importingFile: File | null = null;
    importingQuestions = false;
    showImportSheet = false;

    async loadExam(moduleId: number | string) {
        this.loadingExam = true;
        try {
            const response = await Client.get(`${Client.ADMIN_EXAMS}/${moduleId}/examen`);
            this.exam = response.data.exam;
        } catch {
            this.exam = null;
        }
        this.loadingExam = false;
    }

    openExamSheet() {
        this.showExamSheet = true;
    }

    async saveExam(moduleId: number | string, data: Partial<Exam>, keepOpen = false) {
        this.savingExam = true;
        try {
            let response;
            if (this.exam?.id) {
                response = await Client.put(`${Client.ADMIN_EXAMS}/${moduleId}/examen/${this.exam.id}`, data);
            } else {
                response = await Client.post(`${Client.ADMIN_EXAMS}/${moduleId}/examen`, { ...data, module_id: moduleId });
            }
            this.exam = response.data.exam;
            if (!keepOpen) this.showExamSheet = false;
            if (!keepOpen) Toast.success('Simulacro guardado exitosamente');
        } catch (e: any) {
            Toast.error(e?.response?.data?.message || 'Error al guardar el simulacro');
        }
        this.savingExam = false;
    }

    async deleteExam(moduleId: number | string) {
        if (!this.exam?.id) return;
        this.savingExam = true;
        try {
            await Client.delete(`${Client.ADMIN_EXAMS}/${moduleId}/examen/${this.exam.id}`);
            this.exam = null;
            this.showExamSheet = false;
            Toast.success('Simulacro eliminado');
        } catch (e: any) {
            Toast.error(e?.response?.data?.message || 'Error al eliminar el simulacro');
        }
        this.savingExam = false;
    }

    openQuestionCreate() {
        this.examQuestionType = 'create';
        this.editingQuestion = null;
        this.showQuestionSheet = true;
    }

    openQuestionEdit(question: ExamQuestion) {
        this.examQuestionType = 'update';
        this.editingQuestion = { ...question };
        this.showQuestionSheet = true;
    }

    async saveQuestion(data: any) {
        try {
            let response;
            if (this.examQuestionType === 'update' && this.editingQuestion?.id) {
                response = await Client.put(`${Client.ADMIN_EXAMS}/examen/preguntas/${this.editingQuestion.id}`, data);
            } else {
                response = await Client.post(`${Client.ADMIN_EXAMS}/examen/preguntas`, { ...data, exam_id: this.exam?.id });
            }
            // El backend recalcula los puntos y devuelve todas las preguntas
            if (this.exam && response.data.questions) {
                this.exam.questions = response.data.questions;
            }
            this.showQuestionSheet = false;
            Toast.success('Pregunta guardada exitosamente');
        } catch (e: any) {
            Toast.error(e?.response?.data?.message || 'Error al guardar la pregunta');
        }
    }

    async deleteQuestion(questionId: number) {
        try {
            const response = await Client.delete(`${Client.ADMIN_EXAMS}/examen/preguntas/${questionId}`);
            if (this.exam && response.data.questions) {
                this.exam.questions = response.data.questions;
            }
            Toast.success('Pregunta eliminada');
        } catch {
            Toast.error('Error al eliminar la pregunta');
        }
    }

    async clearAllQuestions() {
        if (!this.exam?.id) return;
        try {
            await Client.post(`${Client.ADMIN_EXAMS}/examen/preguntas/limpiar`, { exam_id: this.exam.id });
            if (this.exam) {
                this.exam.questions = [];
            }
            Toast.success('Todas las preguntas fueron eliminadas');
        } catch {
            Toast.error('Error al limpiar las preguntas');
        }
    }

    openCopyModal() {
        this.showCopyModal = true;
        this.targetModuleId = null;
        this.availableModules = [];
    }

    async loadModulesForCourse(courseId: number) {
        this.loadingModules = true;
        this.targetModuleId = null;
        try {
            const response = await Client.get(`${Client.ADMIN_EXAMS}/examen/modulos-disponibles`, { course_id: courseId });
            this.availableModules = response.data.modules || [];
        } catch {
            this.availableModules = [];
        }
        this.loadingModules = false;
    }

    async copyExam() {
        if (!this.exam?.id || !this.targetModuleId) return;
        this.copyingExam = true;
        try {
            await Client.post(`${Client.ADMIN_EXAMS}/examen/copy`, {
                exam_id: this.exam.id,
                module_id: this.targetModuleId,
            });
            Toast.success('Simulacro copiado exitosamente');
            this.showCopyModal = false;
        } catch (e: any) {
            Toast.error(e?.response?.data?.message || 'Error al copiar el simulacro');
        }
        this.copyingExam = false;
    }

    downloadTemplate() {
        window.open(`${Client.ADMIN_EXAMS}/examen/plantilla`, '_blank');
    }

    async importQuestions(file: File) {
        if (!this.exam?.id) return;
        this.importingQuestions = true;
        try {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('exam_id', String(this.exam.id));
            const response = await Client.post(`${Client.ADMIN_EXAMS}/examen/importar`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            if (this.exam) {
                this.exam.questions = response.data.questions;
            }
            Toast.success(response.data.message || 'Preguntas importadas');
            this.showImportSheet = false;
        } catch (e: any) {
            Toast.error(e?.response?.data?.message || 'Error al importar preguntas');
        }
        this.importingQuestions = false;
    }
}
