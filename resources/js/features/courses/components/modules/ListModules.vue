<script setup lang="ts">
import type { Module } from '@/types/project';
import { router } from '@inertiajs/vue3';

interface Props {
    isSubscribed: boolean;
    modules: Module[];
    currentFileId?: number | string;
}

const props = defineProps<Props>();

const headingID = (id: string | number) => {
    return 'headingModule' + id;
};

const collapseID = (id: string | number) => {
    return 'collapseModule' + id;
};

// Obtener todos los archivos PDF de un módulo
const getModuleFiles = (module: Module) => {
    if (module.files && module.files.length > 0) {
        return module.files;
        return module.files.filter((fileItem) => {
            // Filtrar solo archivos PDF
            return fileItem.file && fileItem.file.url && fileItem.file.url.toLowerCase().includes('.pdf');
        });
    }
    return [];
};

// Contar total de archivos
const getTotalFiles = (module: Module) => {
    return module?.files?.length ?? 0;
    return getModuleFiles(module).length;
};

// Navegar al archivo PDF
const navigateToFile = (fileId: number | string) => {
    console.log('Navegando al archivo:', fileId);
    const url = `/cursos/modulos/archivos/${fileId}`;
    console.log('URL:', url);
    router.visit(url);
};

// Verificar si el módulo contiene el archivo actual
const moduleHasCurrentFile = (module: Module) => {
    if (!props.currentFileId) return false;
    const files = getModuleFiles(module);
    return files.some((file) => file.id == props.currentFileId);
};

// Verificar si es el archivo actual
const isCurrentFile = (fileId: number | string) => {
    return props.currentFileId && fileId == props.currentFileId;
};
</script>

<template>
    <div class="rbt-accordion-style rbt-accordion-02 accordion">
        <div class="accordion" id="accordionListModules">
            <div v-for="module in modules" :key="module.id" class="accordion-item card" :class="{ 'active-module': moduleHasCurrentFile(module) }">
                <h2 class="accordion-header card-header" :id="headingID(module.id)">
                    <button
                        class="accordion-button"
                        :class="{ collapsed: !moduleHasCurrentFile(module) }"
                        type="button"
                        data-bs-toggle="collapse"
                        :data-bs-target="'#' + collapseID(module.id)"
                        :aria-expanded="moduleHasCurrentFile(module) ? 'true' : 'false'"
                        :aria-controls="collapseID(module.id)"
                    >
                        {{ module.title }}
                        <span class="rbt-badge-5 ml--10"> {{ getTotalFiles(module) }} archivos </span>
                    </button>
                </h2>
                <div
                    :id="collapseID(module.id)"
                    class="accordion-collapse collapse"
                    :class="{ show: moduleHasCurrentFile(module) }"
                    :aria-labelledby="headingID(module.id)"
                    data-bs-parent="#accordionListModules"
                >
                    <div class="accordion-body card-body pr--0">
                        <ul class="rbt-course-main-content liststyle">
                            <!-- Todos los PDFs del Módulo -->

                            <li
                                v-for="(fileItem, index) in getModuleFiles(module)"
                                :key="fileItem.id"
                                :class="{ 'active-file': isCurrentFile(fileItem.id) }"
                            >
                                <a
                                    v-if="isSubscribed"
                                    @click.stop.prevent="navigateToFile(fileItem.id)"
                                    class="pdf-viewer-link"
                                    :class="{ 'current-file': isCurrentFile(fileItem.id) }"
                                    style="cursor: pointer"
                                >
                                    <div class="course-content-left">
                                        <i class="feather-file-text"></i>
                                        <span class="text">{{ fileItem.title || `Archivo ${index + 1}` }}</span>
                                        <span class="badge badge-primary ms-2">PDF</span>
                                    </div>
                                </a>
                                <a v-else @click.prevent class="disabled-link">
                                    <div class="course-content-left">
                                        <i class="feather-file-text"></i>
                                        <span class="text">{{ fileItem.title || `Archivo ${index + 1}` }}</span>
                                        <span class="badge badge-primary ms-2">PDF</span>
                                    </div>
                                    <div class="course-content-right">
                                        <span class="course-lock"><i class="feather-lock"></i></span>
                                    </div>
                                </a>
                            </li>

                            <!-- Mensaje cuando no hay archivos -->
                            <li v-if="getModuleFiles(module).length === 0" class="no-files-message">
                                <div class="text-muted">
                                    <i class="feather-folder"></i>
                                    No hay material disponible para este módulo
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.badge {
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 12px;
    background: linear-gradient(135deg, #133a54 0%, #133a54 100%);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
}

.course-content-left {
    display: flex;
    align-items: center;
    gap: 8px;
}

.course-content-left .text {
    flex: 1;
}

@media (max-width: 991px) {
    .course-content-left .text {
        font-size: 14px;
    }
}

.rbt-course-main-content.liststyle {
    margin-bottom: 10px;
}

.rbt-course-main-content.liststyle:last-child {
    margin-bottom: 0;
}

.debug-info {
    padding: 10px;
    background: rgba(19, 58, 84, 0.1);
    border-radius: 8px;
    margin: 10px 0;
    border-left: 3px solid #133a54;
}

.debug-info .text-muted {
    color: #666;
    font-size: 12px;
}

.no-files-message {
    padding: 20px;
    text-align: center;
    color: #666;
}

.no-files-message i {
    margin-right: 8px;
    opacity: 0.6;
}

.disabled-link {
    cursor: not-allowed;
    opacity: 0.7;
}

.disabled-link:hover {
    text-decoration: none;
}

/* Resaltar módulo activo */
.accordion-item.active-module {
    border-left: 4px solid #133a54;
}

.accordion-item.active-module .accordion-button {
    background-color: rgba(19, 58, 84, 0.05);
    font-weight: 600;
}

/* Resaltar archivo actual */
.active-file .pdf-viewer-link.current-file {
    background: linear-gradient(90deg, rgba(19, 58, 84, 0.15) 0%, rgba(19, 58, 84, 0.05) 100%);
    border-left: 4px solid #133a54;
    font-weight: 600;
    padding-left: 12px;
}

.active-file .pdf-viewer-link.current-file .course-content-left {
    color: #133a54;
}

.active-file .pdf-viewer-link.current-file .feather-file-text {
    color: #133a54;
}
</style>
