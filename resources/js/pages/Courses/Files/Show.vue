<script setup lang="ts">
import Link from '@/components/ui/link/Link.vue';
import ListModules from '@/features/courses/components/modules/ListModules.vue';
import { Course, ModuleFile } from '@/types/project';
import * as pdfjsLib from 'pdfjs-dist';
import { onMounted, onUnmounted, ref } from 'vue';

interface Props {
    moduleFile: ModuleFile;
    course: Course;
    pdf: string;
}

const props = defineProps<Props>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
const currentPage = ref(1);
const totalPages = ref(0);
const loading = ref(true);
const error = ref<string | null>(null);
let pdfInstance: any = null;
const workerPath = '/assets/js/pdf.worker.min.js';

const isSidebarHidden = ref(false);
const scale = ref(1.0); // Escala inicial
const baseScale = ref(1.0); // Guardamos la escala base inicial
const isOverflowing = ref(false);
const protectionsActive = ref(false);

// Variables para el drag/pan del PDF
const isDragging = ref(false);
const startX = ref(0);
const startY = ref(0);
const scrollLeft = ref(0);
const scrollTop = ref(0);
const pdfContainerRef = ref<HTMLElement | null>(null);

// Detectar si estamos en responsive y ocultar sidebar automáticamente
const checkResponsive = () => {
    if (window.innerWidth <= 991) {
        isSidebarHidden.value = true;
    }
};

const toggleSidebar = () => {
    isSidebarHidden.value = !isSidebarHidden.value;
};

// Content Protection Functions
const disableProtections = () => {
    protectionsActive.value = true;

    // Disable PrintScreen key
    const handleKeyDown = (e: KeyboardEvent) => {
        // PrintScreen
        if (e.key === 'PrintScreen') {
            navigator.clipboard.writeText('');
            alert('Las capturas de pantalla están deshabilitadas en este material.');
        }
        // Ctrl+P (Print)
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            alert('La impresión está deshabilitada para este material.');
            return false;
        }
        // Ctrl+S (Save)
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            alert('No se puede guardar este material.');
            return false;
        }
        // Ctrl+C (Copy)
        if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
            e.preventDefault();
            alert('No se puede copiar el contenido de este material.');
            return false;
        }
        // Ctrl+X (Cut)
        if ((e.ctrlKey || e.metaKey) && e.key === 'x') {
            e.preventDefault();
            alert('No se puede cortar el contenido de este material.');
            return false;
        }
        // Ctrl+A (Select All)
        if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
            e.preventDefault();
            return false;
        }
    };

    // Disable copy/cut/paste
    const handleCopy = (e: ClipboardEvent) => {
        e.preventDefault();
        navigator.clipboard.writeText('');
        alert('No se puede copiar el contenido de este material.');
        return false;
    };

    const handleCut = (e: ClipboardEvent) => {
        e.preventDefault();
        alert('No se puede cortar el contenido de este material.');
        return false;
    };

    const handleSelectStart = (e: Event) => {
        e.preventDefault();
        return false;
    };

    // Disable right click
    const handleContextMenu = (e: MouseEvent) => {
        e.preventDefault();
        return false;
    };

    // Disable drag
    const handleDragStart = (e: DragEvent) => {
        e.preventDefault();
        return false;
    };

    // Disable print function
    const disablePrint = () => {
        window.print = function () {
            alert('La impresión está deshabilitada para este material.');
            return false;
        };
    };

    // Detect DevTools
    const detectDevTools = () => {
        const threshold = 160;
        if (window.outerWidth - window.innerWidth > threshold || window.outerHeight - window.innerHeight > threshold) {
            console.warn('⚠️ Este material está protegido contra copias.');
        }
    };

    document.addEventListener('keydown', handleKeyDown);
    document.addEventListener('contextmenu', handleContextMenu);
    document.addEventListener('dragstart', handleDragStart);
    document.addEventListener('copy', handleCopy);
    document.addEventListener('cut', handleCut);
    document.addEventListener('selectstart', handleSelectStart);
    disablePrint();

    const devToolsInterval = setInterval(detectDevTools, 1000);

    // Cleanup function
    return () => {
        document.removeEventListener('keydown', handleKeyDown);
        document.removeEventListener('contextmenu', handleContextMenu);
        document.removeEventListener('dragstart', handleDragStart);
        document.removeEventListener('copy', handleCopy);
        document.removeEventListener('cut', handleCut);
        document.removeEventListener('selectstart', handleSelectStart);
        clearInterval(devToolsInterval);
    };
};

const goToCurse = (slug: string) => {
    return `/cursos/${slug}`;
};

// Obtener todos los archivos del curso en orden
const getAllFiles = () => {
    const files: ModuleFile[] = [];
    if (props.course.modules) {
        props.course.modules.forEach((module) => {
            if (module.files) {
                files.push(...module.files);
            }
        });
    }
    return files;
};

// Encontrar el índice del archivo actual
const getCurrentFileIndex = () => {
    const allFiles = getAllFiles();
    return allFiles.findIndex((file) => file.id === props.moduleFile.id);
};

// Ir al siguiente archivo
const goToNextFile = () => {
    const allFiles = getAllFiles();
    const currentIndex = getCurrentFileIndex();
    if (currentIndex < allFiles.length - 1) {
        const nextFile = allFiles[currentIndex + 1];
        window.location.href = `/cursos/modulos/archivos/${nextFile.id}`;
    }
};

// Ir al archivo anterior
const goToPrevFile = () => {
    const allFiles = getAllFiles();
    const currentIndex = getCurrentFileIndex();
    if (currentIndex > 0) {
        const prevFile = allFiles[currentIndex - 1];
        window.location.href = `/cursos/modulos/archivos/${prevFile.id}`;
    }
};

// Verificar si hay siguiente/anterior
const hasNextFile = () => {
    const currentIndex = getCurrentFileIndex();
    const allFiles = getAllFiles();
    return currentIndex < allFiles.length - 1;
};

const hasPrevFile = () => {
    const currentIndex = getCurrentFileIndex();
    return currentIndex > 0;
};

const checkOverflow = () => {
    if (!canvasRef.value) return;
    const container = canvasRef.value.parentElement;
    if (!container) return;

    isOverflowing.value = container.scrollWidth > container.clientWidth || container.scrollHeight > container.clientHeight;
};

// Actualizar la función renderPage para llamar a checkOverflow
const renderPage = async (pageNum: number) => {
    if (!canvasRef.value || !pdfInstance) return;

    try {
        loading.value = true;

        // Obtener la página
        const page = await pdfInstance.getPage(pageNum);

        // Si es la primera vez que se renderiza, establecemos la escala inicial
        if (scale.value === 1.0 && currentPage.value === 1) {
            scale.value = calculateInitialScale(page);
        }

        const canvas = canvasRef.value;
        const context = canvas.getContext('2d');

        // Usar la escala actual
        const viewport = page.getViewport({ scale: scale.value });

        // Configurar el canvas
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        // Renderizar la página
        const renderContext = {
            canvasContext: context,
            viewport: viewport,
        };

        await page.render(renderContext).promise;
        currentPage.value = pageNum;
        checkOverflow(); // Añadir esta línea
    } catch (err) {
        console.error('Error al renderizar la página:', err);
        error.value = 'Error al renderizar la página';
    } finally {
        loading.value = false;
    }
};

const zoomIn = () => {
    scale.value = Math.min(scale.value + baseScale.value * 0.2, baseScale.value * 5.0); // Aumentamos el límite máximo a 500%
    renderPage(currentPage.value);
};

const zoomOut = () => {
    scale.value = Math.max(scale.value - baseScale.value * 0.2, baseScale.value * 0.3); // Reducimos el mínimo a 30%
    renderPage(currentPage.value);
};

// Funciones para arrastrar el PDF
const startDragging = (e: MouseEvent) => {
    if (!pdfContainerRef.value) return;

    isDragging.value = true;
    startX.value = e.pageX - pdfContainerRef.value.offsetLeft;
    startY.value = e.pageY - pdfContainerRef.value.offsetTop;
    scrollLeft.value = pdfContainerRef.value.scrollLeft;
    scrollTop.value = pdfContainerRef.value.scrollTop;

    pdfContainerRef.value.style.cursor = 'grabbing';
};

const stopDragging = () => {
    isDragging.value = false;
    if (pdfContainerRef.value) {
        pdfContainerRef.value.style.cursor = 'grab';
    }
};

const drag = (e: MouseEvent) => {
    if (!isDragging.value || !pdfContainerRef.value) return;

    e.preventDefault();

    const x = e.pageX - pdfContainerRef.value.offsetLeft;
    const y = e.pageY - pdfContainerRef.value.offsetTop;
    const walkX = (x - startX.value) * 1.5; // Multiplicador para hacer el arrastre más sensible
    const walkY = (y - startY.value) * 1.5;

    pdfContainerRef.value.scrollLeft = scrollLeft.value - walkX;
    pdfContainerRef.value.scrollTop = scrollTop.value - walkY;
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        renderPage(currentPage.value + 1);
    }
};

const prevPage = () => {
    if (currentPage.value > 1) {
        renderPage(currentPage.value - 1);
    }
};

// Modificamos la función calculateInitialScale para guardar la escala base
const calculateInitialScale = (page: any): number => {
    if (!canvasRef.value) return 1.0;

    const container = canvasRef.value.parentElement;
    if (!container) return 1.0;

    const containerWidth = container.clientWidth - 2;
    const viewport = page.getViewport({ scale: 1.0 });

    const initialScale = containerWidth / viewport.width;
    baseScale.value = initialScale; // Guardamos la escala base
    return initialScale;
};

const handleResize = () => {
    if (pdfInstance) {
        pdfInstance.getPage(currentPage.value).then((page: any) => {
            scale.value = calculateInitialScale(page);
            renderPage(currentPage.value);
        });
    }
};

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);

    // Limpiar event listeners de arrastre
    if (pdfContainerRef.value) {
        pdfContainerRef.value.removeEventListener('mousedown', startDragging);
        pdfContainerRef.value.removeEventListener('mouseleave', stopDragging);
        pdfContainerRef.value.removeEventListener('mouseup', stopDragging);
        pdfContainerRef.value.removeEventListener('mousemove', drag);
    }

    // Cleanup protections
    if (cleanupProtections) {
        cleanupProtections();
    }
});

let cleanupProtections: (() => void) | null = null;

onMounted(async () => {
    // Enable content protections
    cleanupProtections = disableProtections();

    // Verificar responsive al cargar
    checkResponsive();

    // Agregar listener para cambios de tamaño
    window.addEventListener('resize', handleResize);
    window.addEventListener('resize', checkResponsive);

    // Agregar event listeners para arrastrar el PDF
    if (pdfContainerRef.value) {
        pdfContainerRef.value.addEventListener('mousedown', startDragging);
        pdfContainerRef.value.addEventListener('mouseleave', stopDragging);
        pdfContainerRef.value.addEventListener('mouseup', stopDragging);
        pdfContainerRef.value.addEventListener('mousemove', drag);
    }

    if (!canvasRef.value) return;

    try {
        loading.value = true;

        // Configurar el worker
        pdfjsLib.GlobalWorkerOptions.workerSrc = workerPath;

        // Procesar el base64
        const pdfData = atob(props.pdf.replace(/^data:application\/pdf;base64,/, ''));
        const pdfBytes = new Uint8Array(pdfData.length);
        for (let i = 0; i < pdfData.length; i++) {
            pdfBytes[i] = pdfData.charCodeAt(i);
        }

        // Cargar el PDF
        pdfInstance = await pdfjsLib.getDocument({ data: pdfBytes }).promise;
        totalPages.value = pdfInstance.numPages;

        // Renderizar la primera página
        await renderPage(1);
    } catch (err) {
        console.error('Error al cargar el PDF:', err);
        error.value = 'Error al cargar el PDF';
    }
});
</script>

<template>
    <div class="rbt-lesson-area bg-color-white" :class="{ 'sidebar-hidden': isSidebarHidden }">
        <div class="rbt-lesson-content-wrapper">
            <div class="rbt-lesson-leftsidebar sticky-sidebar" :class="{ 'sibebar-none': isSidebarHidden }">
                <div class="rbt-course-feature-inner rbt-search-activation">
                    <div class="section-title sticky-header">
                        <div class="sidebar-header-content">
                            <Link href="/" class="sidebar-logo">
                                <img src="/assets/images/logo/logo-color.png" alt="Construyendo Méritos con Excelencia" />
                            </Link>
                            <button @click="toggleSidebar" class="sidebar-close-btn" title="Cerrar menú">
                                <i class="feather-x"></i>
                            </button>
                        </div>
                        <h4 class="rbt-title-style-3 sidebar-title">Material de Estudio</h4>
                        <div class="hoja-respuesta-btn-container" style="margin: 1rem 0; text-align: left">
                            <a href="/assets/images/hojaderespuestas.pdf" download target="_blank" class="material-nav-btn hoja-respuesta-btn">
                                <i class="feather-download"></i>
                                <span class="material-nav-text"><span class="download-text">Descargar </span>hoja de respuestas</span>
                            </a>
                        </div>
                    </div>

                    <div class="rbt-accordion-style rbt-accordion-02 for-right-content accordion scrollable-content">
                        <ListModules :is-subscribed="true" :modules="course.modules ?? []" :current-file-id="moduleFile.id" />
                    </div>
                </div>
            </div>

            <div class="rbt-lesson-rightsidebar lesson-video overflow-hidden" :class="{ 'full-width': isSidebarHidden }">
                <div class="lesson-top-bar modern-header">
                    <div class="lesson-top-left">
                        <div class="rbt-lesson-toggle" v-if="isSidebarHidden">
                            <button @click="toggleSidebar" class="lesson-toggle-active modern-toggle-btn" title="Ver Materiales">
                                <i class="feather-menu"></i>
                                <span class="toggle-text">Ver Materiales</span>
                            </button>
                        </div>
                        <div class="lesson-info">
                            <span class="lesson-badge">Lección</span>
                            <h5 class="lesson-title">{{ moduleFile.title }}</h5>
                        </div>
                    </div>
                    <div class="lesson-top-right">
                        <div class="rbt-btn-close">
                            <Link as="link" variant="default" :href="goToCurse(course.slug)" title="Volver al Curso" class="rbt-round-btn btn-back"
                                ><i class="feather-x"></i
                            ></Link>
                        </div>
                    </div>
                </div>
                <div class="inner">
                    <div class="content">
                        <div class="pdf-viewer-wrapper">
                            <!-- Botones de navegación entre materiales -->
                            <div class="material-navigation-container">
                                <button
                                    @click="goToPrevFile"
                                    :disabled="!hasPrevFile()"
                                    class="material-nav-btn prev-material"
                                    title="Documento anterior"
                                >
                                    <i class="feather-chevron-left"></i>
                                    <span class="material-nav-text">Documento anterior</span>
                                </button>
                                <button
                                    @click="goToNextFile"
                                    :disabled="!hasNextFile()"
                                    class="material-nav-btn next-material"
                                    title="Siguiente documento"
                                >
                                    <span class="material-nav-text">Siguiente documento</span>
                                    <i class="feather-chevron-right"></i>
                                </button>
                            </div>

                            <div ref="pdfContainerRef" class="pdf modern-pdf-container">
                                <div v-if="loading" class="modern-loader text-center">
                                    <div class="loading-animation">
                                        <div class="circle"></div>
                                        <div class="circle"></div>
                                        <div class="circle"></div>
                                    </div>
                                    <p class="loading-text">Cargando PDF...</p>
                                </div>

                                <canvas v-show="!loading" ref="canvasRef" class="pdf-canvas modern-canvas"></canvas>
                            </div>

                            <!-- Navegación flotante para páginas y zoom -->
                            <div v-if="!loading" class="pdf-navigation modern-navigation">
                                <div class="nav-group pages-group">
                                    <button
                                        class="material-nav-btn page-nav-btn"
                                        @click="prevPage"
                                        :disabled="currentPage === 1"
                                        title="Página anterior"
                                    >
                                        <i class="feather-chevron-left"></i>
                                        <span class="material-nav-text">Página anterior</span>
                                    </button>

                                    <div class="page-info modern-page-info">
                                        <span class="page-current">{{ currentPage }}</span>
                                        <span class="page-divider">/</span>
                                        <span class="page-total">{{ totalPages }}</span>
                                    </div>

                                    <button
                                        class="material-nav-btn page-nav-btn"
                                        @click="nextPage"
                                        :disabled="currentPage === totalPages"
                                        title="Página siguiente"
                                    >
                                        <span class="material-nav-text">Página siguiente</span>
                                        <i class="feather-chevron-right"></i>
                                    </button>
                                </div>

                                <div class="nav-separator-vertical"></div>

                                <div class="nav-group zoom-group">
                                    <button class="material-nav-btn zoom-nav-btn" @click="zoomOut" title="Alejar">
                                        <i class="feather-zoom-out"></i>
                                        <span class="material-nav-text">Alejar</span>
                                    </button>

                                    <div class="zoom-info">{{ Math.round(scale * 100) }}%</div>

                                    <button class="material-nav-btn zoom-nav-btn" @click="zoomIn" title="Acercar">
                                        <i class="feather-zoom-in"></i>
                                        <span class="material-nav-text">Acercar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- .pdf-viewer-wrapper -->
                    </div>
                    <!-- .content -->
                </div>
                <!-- .inner -->
            </div>
            <!-- .rbt-lesson-rightsidebar -->
        </div>
        <!-- .rbt-lesson-content-wrapper -->
    </div>
    <!-- .rbt-lesson-area -->
</template>

<style scoped>
/* ===========================
   PROTECCIÓN DE CONTENIDO
   =========================== */
.rbt-lesson-area {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* ===========================
   SIDEBAR STICKY
   =========================== */
.sticky-sidebar {
    position: sticky !important;
    top: 0;
    height: 100vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.sticky-sidebar.sibebar-none {
    transform: translateX(-100%);
    opacity: 0;
    width: 0;
    min-width: 0;
    flex: 0;
}

/* Ajustar contenido cuando sidebar está oculto */
.sidebar-hidden .rbt-lesson-rightsidebar {
    width: 100% !important;
    max-width: 100% !important;
    flex: 1 !important;
}

.rbt-lesson-rightsidebar.full-width {
    margin-left: 0 !important;
}

/* Centrar contenido del PDF cuando sidebar está oculto */
.sidebar-hidden .modern-pdf-container {
    max-width: 1200px;
    margin: 0 auto;
}

.sidebar-hidden .content {
    max-width: 1400px;
    margin: 0 auto;
}

.sticky-sidebar .rbt-course-feature-inner {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
}

.sticky-sidebar .sticky-header {
    flex-shrink: 0;
    padding: 1.5rem;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-bottom: 2px solid #ff6b35;
    z-index: 10;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.sidebar-header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.sidebar-logo,
a.sidebar-logo,
Link.sidebar-logo {
    display: inline-block;
    transition: transform 0.3s ease;
    text-decoration: none !important;
}

.sidebar-logo:hover,
a.sidebar-logo:hover,
Link.sidebar-logo:hover {
    text-decoration: none !important;
    transform: scale(1.05);
    border-bottom: none !important;
    box-shadow: none !important;
}

.sidebar-logo img,
a.sidebar-logo img,
Link.sidebar-logo img {
    height: 45px;
    width: auto;
    object-fit: contain;
}

.sidebar-close-btn {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    color: white;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
}

.sidebar-close-btn:hover {
    transform: rotate(90deg) scale(1.1);
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.5);
}

.sidebar-close-btn i {
    font-size: 1.2rem;
}

.sidebar-title {
    margin: 0;
    color: #2d3748;
    font-size: 1.1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.sticky-sidebar .scrollable-content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 1rem;
}

.sticky-sidebar .scrollable-content::-webkit-scrollbar {
    width: 6px;
}

.sticky-sidebar .scrollable-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.sticky-sidebar .scrollable-content::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    border-radius: 10px;
}

.sticky-sidebar .scrollable-content::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #f7931e 0%, #ff6b35 100%);
}

.active-dark-mode .sticky-sidebar .sticky-header {
    background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
    border-bottom-color: #ff6b35;
}

.active-dark-mode .sidebar-title {
    color: #e2e8f0;
}

.active-dark-mode .sticky-sidebar .scrollable-content::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

/* ===========================
   HEADER MODERNO
   =========================== */
.modern-header {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    padding: 1.25rem 2rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.lesson-top-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex: 1;
    min-width: 0;
}

.lesson-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 0;
}

.lesson-badge {
    background: rgba(255, 255, 255, 0.25);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    width: fit-content;
    backdrop-filter: blur(10px);
}

.lesson-title {
    color: white !important;
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.modern-toggle-btn {
    background: rgba(255, 255, 255, 0.25) !important;
    color: white !important;
    border: 2px solid rgba(255, 255, 255, 0.3) !important;
    height: 42px;
    border-radius: 12px !important;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(10px);
    flex-shrink: 0;
    padding: 0 1rem;
    white-space: nowrap;
}

.modern-toggle-btn:hover {
    background: rgba(255, 255, 255, 0.35) !important;
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.modern-toggle-btn i {
    font-size: 1.3rem;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.toggle-text {
    font-weight: 600;
    font-size: 0.9rem;
    display: none;
}

@media (max-width: 991px) {
    .modern-toggle-btn {
        padding: 0;
        width: 42px;
        height: 42px;
    }

    .download-text {
        display: none;
    }
}

.btn-back {
    background: rgba(255, 255, 255, 0.25) !important;
    color: white !important;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    width: 42px;
    height: 42px;
    border-radius: 12px !important;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-back:hover {
    background: rgba(255, 255, 255, 0.35) !important;
    transform: scale(1.05);
}

/* ===========================
   NAVEGACIÓN ENTRE MATERIALES
   =========================== */
.material-navigation-container {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.material-nav-btn {
    flex: 1;
    min-width: 200px;
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    color: white !important;
    border: none;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
}

.material-nav-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
    background: linear-gradient(135deg, #f7931e 0%, #ff6b35 100%);
}

.material-nav-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none;
}

.material-nav-btn i {
    font-size: 1.25rem;
    color: white !important;
}

.material-nav-text {
    font-weight: 600;
    color: white !important;
}

/* ===========================
   SECCIÓN "ACERCA DE" MODERNA
   =========================== */
.modern-about {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.about-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.about-header i {
    font-size: 1.5rem;
    color: #ff6b35;
}

.about-header h4 {
    margin: 0;
    color: #2d3748;
    font-weight: 700;
}

.modern-about p {
    color: #4a5568;
    line-height: 1.6;
    margin: 0;
}

/* ===========================
   CONTENEDOR PDF MODERNO
   =========================== */
.pdf-viewer-wrapper {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
}

.modern-pdf-container {
    width: 100%;
    position: relative;
    overflow: auto;
    min-height: 500px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 12px 48px rgba(0, 0, 0, 0.12);
    margin-bottom: 10rem;
    cursor: grab;
    user-select: none;
}

.modern-pdf-container:active {
    cursor: grabbing;
}

.modern-canvas {
    max-width: none;
    width: auto !important;
    height: auto !important;
    border-radius: 12px;
    display: block;
    margin: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    pointer-events: none;
    background: white;
}

/* Scrollbar personalizado para el contenedor PDF */
.modern-pdf-container::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}

.modern-pdf-container::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    margin: 10px;
}

.modern-pdf-container::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    border-radius: 10px;
    border: 2px solid rgba(255, 255, 255, 0.5);
}

.modern-pdf-container::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #f7931e 0%, #ff6b35 100%);
}

/* ===========================
   NAVEGACIÓN FLOTANTE MODERNA
   =========================== */
.modern-navigation {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 255, 255, 0.98);
    border-radius: 20px;
    padding: 1.5rem 2rem;
    display: flex;
    align-items: center;
    gap: 2rem;
    box-shadow: 0 12px 48px rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(30px);
    border: 2px solid rgba(255, 255, 255, 0.8);
    z-index: 1000;
    transition: all 0.3s ease;
    flex-wrap: wrap;
    max-width: 95%;
}

.modern-navigation:hover {
    box-shadow: 0 16px 60px rgba(0, 0, 0, 0.25);
}

.active-dark-mode .modern-navigation {
    background: rgba(30, 30, 30, 0.98);
    border: 2px solid rgba(255, 255, 255, 0.15);
}

.nav-group {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.page-nav-btn,
.zoom-nav-btn {
    min-width: auto !important;
    padding: 0.75rem 1.25rem !important;
    border-radius: 10px !important;
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3) !important;
    white-space: nowrap;
}

.page-nav-btn:hover:not(:disabled),
.zoom-nav-btn:hover:not(:disabled) {
    transform: translateY(-2px) !important;
}

.page-nav-btn:disabled,
.zoom-nav-btn:disabled {
    transform: none !important;
}

.nav-separator-vertical {
    width: 2px;
    height: 32px;
    background: linear-gradient(to bottom, transparent, rgba(0, 0, 0, 0.1), transparent);
    border-radius: 2px;
}

.modern-page-info {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-weight: 700;
    font-size: 1rem;
    color: #2d3748;
    padding: 0 0.5rem;
    user-select: none;
}

.page-current {
    color: #ff6b35;
    font-size: 1.25rem;
    font-weight: 800;
}

.page-divider {
    color: #cbd5e0;
    margin: 0 0.25rem;
    font-weight: 500;
}

.page-total {
    color: #718096;
    font-size: 1rem;
}

.zoom-info {
    font-weight: 700;
    font-size: 0.9rem;
    color: #11998e;
    padding: 0.35rem 0.75rem;
    min-width: 60px;
    text-align: center;
    background: rgba(17, 153, 142, 0.1);
    border-radius: 20px;
}

.active-dark-mode .modern-page-info {
    color: #e2e8f0;
}

.active-dark-mode .page-total {
    color: #a0aec0;
}

/* ===========================
   LOADER MODERNO
   =========================== */
.modern-loader {
    padding: 4rem 0;
}

.loading-animation {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.loading-animation .circle {
    width: 16px;
    height: 16px;
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    border-radius: 50%;
    animation: bounce 1.4s infinite ease-in-out both;
}

.loading-animation .circle:nth-child(1) {
    animation-delay: -0.32s;
}

.loading-animation .circle:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes bounce {
    0%,
    80%,
    100% {
        transform: scale(0);
    }
    40% {
        transform: scale(1);
    }
}

.loading-text {
    color: #ff6b35;
    font-weight: 600;
    font-size: 1.1rem;
}

/* ===========================
   ERROR MODERNO
   =========================== */
.modern-error {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: linear-gradient(135deg, #fc5c7d 0%, #6a82fb 100%);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 1.5rem;
    font-weight: 600;
}

.modern-error i {
    font-size: 1.5rem;
}

/* ===========================
   NAVEGACIÓN FLOTANTE MODERNA
   =========================== */
.modern-navigation {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 255, 255, 0.95);
    border-radius: 50px;
    padding: 0.75rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    z-index: 1000;
}

.active-dark-mode .modern-navigation {
    background: rgba(30, 30, 30, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modern-nav-btn {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    border: none;
    padding: 0.6rem;
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    color: white !important;
    min-width: 40px;
    min-height: 40px;
}

.modern-nav-btn:hover:not(:disabled) {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
}

.modern-nav-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #cbd5e0;
}

.nav-separator {
    width: 1px;
    height: 24px;
    background: linear-gradient(to bottom, transparent, #e2e8f0, transparent);
    margin: 0 0.25rem;
}

.modern-page-info {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-weight: 600;
    font-size: 1rem;
    color: #2d3748;
    padding: 0 0.5rem;
}

.page-current {
    color: #ff6b35;
    font-size: 1.1rem;
}

.page-divider {
    color: #cbd5e0;
    margin: 0 0.25rem;
}

.page-total {
    color: #718096;
}

.zoom-info {
    font-weight: 600;
    font-size: 0.875rem;
    color: #ff6b35;
    padding: 0 0.5rem;
    min-width: 50px;
    text-align: center;
}

/* ===========================
   NAVEGACIÓN FLOTANTE MODERNA
   =========================== */
.modern-navigation {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(255, 255, 255, 0.95);
    border-radius: 50px;
    padding: 0.75rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    z-index: 1000;
}

.active-dark-mode .modern-navigation {
    background: rgba(30, 30, 30, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.modern-nav-btn {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    border: none;
    padding: 0.6rem;
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    color: white !important;
    min-width: 40px;
    min-height: 40px;
}

.modern-nav-btn:hover:not(:disabled) {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
}

.modern-nav-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #cbd5e0;
}

.nav-separator {
    width: 1px;
    height: 24px;
    background: linear-gradient(to bottom, transparent, #e2e8f0, transparent);
    margin: 0 0.25rem;
}

.modern-page-info {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-weight: 600;
    font-size: 1rem;
    color: #2d3748;
    padding: 0 0.5rem;
}

.page-current {
    color: #ff6b35;
    font-size: 1.1rem;
}

.page-divider {
    color: #cbd5e0;
    margin: 0 0.25rem;
}

.page-total {
    color: #718096;
}

.zoom-info {
    font-weight: 600;
    font-size: 0.875rem;
    color: #ff6b35;
    padding: 0 0.5rem;
    min-width: 50px;
    text-align: center;
}

/* ===========================
   RESPONSIVE
   =========================== */
@media (max-width: 991px) {
    .sticky-sidebar {
        position: fixed !important;
        left: 0;
        top: 0;
        z-index: 9999;
        width: 320px;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease;
    }

    .sticky-sidebar.sibebar-none {
        transform: translateX(-100%);
    }

    .rbt-lesson-rightsidebar {
        width: 100% !important;
        max-width: 100% !important;
    }

    /* Overlay cuando el sidebar está abierto */
    .sidebar-hidden::before {
        display: none;
    }

    .rbt-lesson-area:not(.sidebar-hidden)::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9998;
        backdrop-filter: blur(2px);
    }

    .modern-header {
        padding: 1rem;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .lesson-top-left {
        width: 100%;
    }

    .lesson-top-right {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }

    .lesson-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.65rem;
    }

    .lesson-title {
        font-size: 1.1rem;
        line-height: 1.4;
    }
}

@media (max-width: 768px) {
    .modern-header {
        padding: 0.875rem;
        gap: 0.5rem;
    }

    .lesson-title {
        font-size: 1rem;
    }

    .material-navigation-container {
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .material-nav-btn {
        min-width: 100%;
        padding: 0.875rem 1.25rem;
        font-size: 0.9rem;
    }

    .material-nav-btn i {
        font-size: 1.1rem;
    }

    .modern-about {
        padding: 1.25rem;
        margin-bottom: 1.25rem;
        border-radius: 14px;
    }

    .about-header h4 {
        font-size: 0.95rem;
    }

    .about-header i {
        font-size: 1.3rem;
    }

    .modern-pdf-container {
        padding: 1rem;
        border-radius: 14px;
        min-height: calc(100vh - 400px);
        margin-bottom: 10rem;
    }

    .pdf-viewer-wrapper {
        max-width: 100%;
    }

    .modern-navigation {
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        padding: 1rem 1.5rem;
        gap: 1rem;
        flex-wrap: nowrap;
        border-radius: 50px;
        max-width: calc(100% - 2rem);
    }

    .nav-group {
        gap: 0.75rem;
    }

    .pages-group,
    .zoom-group {
        flex-direction: row;
    }

    .page-nav-btn,
    .zoom-nav-btn {
        min-width: 44px !important;
        min-height: 44px !important;
        width: 44px !important;
        height: 44px !important;
        padding: 0.75rem !important;
        border-radius: 50% !important;
        justify-content: center !important;
    }

    .page-nav-btn .material-nav-text,
    .zoom-nav-btn .material-nav-text {
        display: none;
    }

    .page-nav-btn i,
    .zoom-nav-btn i {
        font-size: 1.1rem !important;
        margin: 0 !important;
    }

    .modern-page-info {
        font-size: 0.9rem;
        padding: 0 0.35rem;
        background: transparent;
        border-radius: 0;
        width: auto;
    }

    .page-current {
        font-size: 1rem;
    }

    .zoom-info {
        font-size: 0.85rem;
        min-width: 60px;
        padding: 0.35rem 0.75rem;
        background: rgba(17, 153, 142, 0.1);
        border-radius: 20px;
    }

    .nav-separator-vertical {
        display: block;
        height: 28px;
        width: 2px;
    }

    /* Scrollbar más pequeño en móviles */
    .modern-pdf-container::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .modern-pdf-container::-webkit-scrollbar-thumb {
        border-width: 1px;
    }
}

@media (max-width: 480px) {
    .sticky-sidebar {
        width: 280px;
    }

    .modern-header {
        padding: 0.75rem;
    }

    .lesson-title {
        font-size: 0.95rem;
    }

    .lesson-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
    }

    .lesson-info {
        gap: 0.5rem;
    }

    .material-nav-btn {
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
    }

    .material-nav-text {
        font-size: 0.85rem;
    }

    .modern-about {
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 12px;
    }

    .about-header h4 {
        font-size: 0.9rem;
    }

    .about-header i {
        font-size: 1.2rem;
    }

    .modern-pdf-container {
        padding: 0.75rem;
        border-radius: 12px;
        min-height: calc(100vh - 350px);
        margin-bottom: 5.5rem;
    }

    .modern-navigation {
        gap: 0.75rem;
        padding: 0.75rem 1.25rem;
    }

    .nav-group {
        gap: 0.5rem;
    }

    .page-nav-btn,
    .zoom-nav-btn {
        min-width: 40px !important;
        min-height: 40px !important;
        width: 40px !important;
        height: 40px !important;
        padding: 0.65rem !important;
    }

    .page-nav-btn i,
    .zoom-nav-btn i {
        font-size: 1rem !important;
    }

    .modern-page-info {
        font-size: 0.85rem;
        gap: 0.2rem;
        padding: 0 0.25rem;
    }

    .page-current {
        font-size: 0.95rem;
    }

    .zoom-info {
        font-size: 0.75rem;
        padding: 0.3rem 0.6rem;
        min-width: 52px;
    }

    .nav-separator-vertical {
        height: 24px;
    }
}

/* ===========================
   DARK MODE
   =========================== */
.active-dark-mode .modern-about {
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
}

.active-dark-mode .about-header h4,
.active-dark-mode .modern-about p {
    color: #e2e8f0;
}

.active-dark-mode .modern-pdf-container {
    background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
}

.active-dark-mode .modern-canvas {
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
}
</style>
