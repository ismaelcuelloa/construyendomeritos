<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface Props {
    expiresAt: string;
}

const props = defineProps<Props>();

const timeLeft = ref({
    hours: 0,
    minutes: 0,
    seconds: 0,
    total: 0
});

const isExpired = ref(false);
const showExpiredModal = ref(false);
let interval: number | null = null;

const updateCountdown = () => {
    const now = new Date().getTime();
    const expireTime = new Date(props.expiresAt).getTime();
    const diff = expireTime - now;

    if (diff <= 0) {
        timeLeft.value = { hours: 0, minutes: 0, seconds: 0, total: 0 };
        if (!isExpired.value) {
            isExpired.value = true;
            // Mostrar modal premium cuando el demo expire
            showExpiredModal.value = true;
        }
        if (interval) {
            clearInterval(interval);
            interval = null;
        }
        return;
    }

    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    timeLeft.value = {
        hours,
        minutes,
        seconds,
        total: diff
    };
};

const percentageLeft = computed(() => {
    const thirtyMinutesInMs = 30 * 60 * 1000; // 30 minutos
    return Math.max(0, Math.min(100, (timeLeft.value.total / thirtyMinutesInMs) * 100));
});

const urgencyClass = computed(() => {
    if (timeLeft.value.total <= 10 * 60 * 1000) return 'urgent'; // Últimos 10 minutos
    if (timeLeft.value.total <= 15 * 60 * 1000) return 'warning'; // Últimos 15 minutos
    return 'normal';
});

const closeModal = () => {
    showExpiredModal.value = false;
};

const contactWhatsApp = () => {
    // Puedes personalizar el número y mensaje de WhatsApp aquí
    const phoneNumber = '573236871881'; // Reemplazar con tu número
    const message = encodeURIComponent('Hola, mi demo ha expirado y deseo obtener acceso completo al material de estudio.');
    window.open(`https://wa.me/${phoneNumber}?text=${message}`, '_blank');
    closeModal();
};

onMounted(() => {
    updateCountdown();
    interval = window.setInterval(updateCountdown, 1000);
});

onUnmounted(() => {
    if (interval) {
        clearInterval(interval);
    }
});
</script>

<template>
    <div class="demo-countdown-compact">
        <div v-if="!isExpired" class="countdown-wrapper">
            <div class="demo-label">
                <span class="demo-text">Tiempo de prueba DEMO</span>
            </div>
            
            <div class="countdown-card" :class="urgencyClass">
                <div class="timer-display">
                    <div class="time-block">
                        <span class="time-value">{{ String(timeLeft.hours).padStart(2, '0') }}</span>
                        <span class="time-unit">h</span>
                    </div>
                    <span class="time-divider">:</span>
                    <div class="time-block">
                        <span class="time-value">{{ String(timeLeft.minutes).padStart(2, '0') }}</span>
                        <span class="time-unit">m</span>
                    </div>
                    <span class="time-divider">:</span>
                    <div class="time-block">
                        <span class="time-value">{{ String(timeLeft.seconds).padStart(2, '0') }}</span>
                        <span class="time-unit">s</span>
                    </div>
                </div>
                
                <!-- Barra de progreso integrada -->
                <div class="progress-container">
                    <div 
                        class="progress-fill" 
                        :style="{ width: percentageLeft + '%' }"
                    ></div>
                </div>
                
                <div v-if="urgencyClass !== 'normal'" class="status-badge">
                    <span v-if="urgencyClass === 'urgent'">Últimos minutos</span>
                    <span v-else>Quedan pocos minutos</span>
                </div>
            </div>
        </div>

        <div v-else class="expired-card">
            <span class="expired-icon">⏰</span>
            <span class="expired-text">Demo expirado</span>
            <span class="expired-action">Contáctanos por WhatsApp</span>
        </div>

        <!-- Modal Premium de Expiración -->
        <Teleport to="body">
            <Transition name="modal-fade">
                <div v-if="showExpiredModal" class="modal-overlay" @click="closeModal">
                    <div class="modal-container" @click.stop>
                        <div class="modal-content">
                            <!-- Icono animado -->
                            <div class="modal-icon-wrapper">
                                <div class="icon-circle">
                                    <svg class="icon-clock" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Título -->
                            <h2 class="modal-title">Tu Demo ha Expirado</h2>
                            
                            <!-- Descripción -->
                            <p class="modal-description">
                                Tu tiempo de acceso al material de estudio ha finalizado.
                            </p>

                            <!-- Feature list -->
                            <div class="modal-features">
                                <div class="feature-item">
                                    <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Acceso ilimitado a todo el contenido</span>
                                </div>
                                <div class="feature-item">
                                    <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Material actualizado constantemente</span>
                                </div>
                                <div class="feature-item">
                                    <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Soporte prioritario</span>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="modal-actions">
                                <button @click="contactWhatsApp" class="btn-whatsapp">
                                    <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                    Contactar por WhatsApp
                                </button>
                                <button @click="closeModal" class="btn-close-text">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.demo-countdown-compact {
    display: flex;
    flex-direction: column;
    margin-bottom: 12px;
}

.countdown-wrapper {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* Label superior */
.demo-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 4px 0;
}

.demo-icon {
    font-size: 12px;
}

.demo-text {
    font-size: 10px;
    font-weight: 700;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Card principal con efecto glassmorphism */
.countdown-card {
    position: relative;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(249, 250, 251, 0.95) 100%);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 12px 16px;
    box-shadow: 
        0 2px 8px rgba(0, 0, 0, 0.04),
        0 1px 2px rgba(0, 0, 0, 0.06),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(229, 231, 235, 0.8);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.countdown-card.urgent {
    background: linear-gradient(135deg, rgba(254, 242, 242, 0.98) 0%, rgba(254, 226, 226, 0.98) 100%);
    border-color: rgba(239, 68, 68, 0.2);
}

.countdown-card.warning {
    background: linear-gradient(135deg, rgba(255, 251, 235, 0.98) 0%, rgba(254, 243, 199, 0.98) 100%);
    border-color: rgba(245, 158, 11, 0.2);
}

/* Timer display */
.timer-display {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: 8px;
}

.time-block {
    display: flex;
    align-items: baseline;
    gap: 2px;
}

.time-value {
    font-size: 20px;
    font-weight: 700;
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.5px;
    font-variant-numeric: tabular-nums;
}

.countdown-card.urgent .time-value {
    background: linear-gradient(135deg, #DC2626 0%, #EF4444 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.countdown-card.warning .time-value {
    background: linear-gradient(135deg, #D97706 0%, #F59E0B 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.time-unit {
    font-size: 9px;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.time-divider {
    font-size: 16px;
    font-weight: 600;
    color: #9CA3AF;
    margin: 0 2px;
}

/* Barra de progreso premium */
.progress-container {
    position: relative;
    width: 100%;
    height: 4px;
    background: rgba(229, 231, 235, 0.5);
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 6px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10B981 0%, #34D399 100%);
    border-radius: 2px;
    transition: width 1s linear, background 0.3s ease;
    position: relative;
    box-shadow: 0 0 8px rgba(16, 185, 129, 0.3);
}

.countdown-card.warning .progress-fill {
    background: linear-gradient(90deg, #F59E0B 0%, #FBBF24 100%);
    box-shadow: 0 0 8px rgba(245, 158, 11, 0.3);
}

.countdown-card.urgent .progress-fill {
    background: linear-gradient(90deg, #EF4444 0%, #DC2626 100%);
    box-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
    animation: pulse-glow 2s ease-in-out infinite;
}

.progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 20px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3));
    animation: shimmer 2s infinite;
}

/* Badge de estado */
.status-badge {
    text-align: center;
    font-size: 9px;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.countdown-card.urgent .status-badge {
    color: #DC2626;
    animation: pulse-text 2s ease-in-out infinite;
}

.countdown-card.warning .status-badge {
    color: #D97706;
}

/* Card expirado */
.expired-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    background: linear-gradient(135deg, rgba(254, 242, 242, 0.98) 0%, rgba(254, 226, 226, 0.98) 100%);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 12px 16px;
    border: 1px solid rgba(239, 68, 68, 0.2);
    box-shadow: 
        0 2px 8px rgba(239, 68, 68, 0.08),
        0 1px 2px rgba(239, 68, 68, 0.12),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
}

.expired-icon {
    font-size: 16px;
    margin-bottom: 2px;
}

.expired-text {
    font-size: 12px;
    font-weight: 700;
    color: #DC2626;
    letter-spacing: -0.2px;
}

.expired-action {
    font-size: 9px;
    font-weight: 600;
    color: #EF4444;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Animaciones */
@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 8px rgba(239, 68, 68, 0.4);
    }
    50% {
        box-shadow: 0 0 12px rgba(239, 68, 68, 0.6);
    }
}

@keyframes pulse-text {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(200%);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .demo-label {
        gap: 4px;
        padding: 3px 0;
    }
    
    .demo-icon {
        font-size: 11px;
    }
    
    .demo-text {
        font-size: 9px;
        letter-spacing: 0.8px;
    }
    
    .countdown-card {
        padding: 10px 14px;
    }
    
    .time-value {
        font-size: 18px;
    }
    
    .time-divider {
        font-size: 14px;
    }
    
    .time-unit {
        font-size: 8px;
    }
    
    .progress-container {
        height: 3px;
    }
    
    .status-badge {
        font-size: 8px;
    }
}

/* Dark mode support (opcional) */
@media (prefers-color-scheme: dark) {
    .countdown-card {
        background: linear-gradient(135deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 24, 39, 0.95) 100%);
        border-color: rgba(75, 85, 99, 0.3);
    }
    
    .time-value {
        background: linear-gradient(135deg, #F9FAFB 0%, #E5E7EB 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .progress-container {
        background: rgba(55, 65, 81, 0.5);
    }
}

/* ============================================ */
/* MODAL PREMIUM STYLES */
/* ============================================ */

/* Overlay del modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

/* Container del modal */
.modal-container {
    position: relative;
    max-width: 480px;
    width: 100%;
    animation: modal-slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Contenido del modal */
.modal-content {
    background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
    border-radius: 24px;
    padding: 40px 32px;
    box-shadow: 
        0 20px 60px rgba(0, 0, 0, 0.3),
        0 4px 12px rgba(0, 0, 0, 0.15),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.5);
    position: relative;
    overflow: hidden;
}

/* Efecto de brillo sutil en el fondo */
.modal-content::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(239, 68, 68, 0.05) 0%, transparent 70%);
    animation: rotate-glow 20s linear infinite;
    pointer-events: none;
}

/* Icono principal */
.modal-icon-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 24px;
}

.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 
        0 8px 24px rgba(239, 68, 68, 0.2),
        inset 0 1px 2px rgba(255, 255, 255, 0.5);
    animation: icon-pulse 2s ease-in-out infinite;
    border: 3px solid rgba(255, 255, 255, 0.8);
}

.icon-clock {
    width: 40px;
    height: 40px;
    color: #DC2626;
    filter: drop-shadow(0 2px 4px rgba(220, 38, 38, 0.2));
}

/* Título */
.modal-title {
    font-size: 28px;
    font-weight: 800;
    color: #111827;
    text-align: center;
    margin: 0 0 12px 0;
    letter-spacing: -0.5px;
    line-height: 1.2;
}

/* Descripción */
.modal-description {
    font-size: 16px;
    color: #6B7280;
    text-align: center;
    margin: 0 0 32px 0;
    line-height: 1.6;
}

/* Lista de características */
.modal-features {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 32px;
    padding: 24px;
    background: rgba(249, 250, 251, 0.8);
    border-radius: 16px;
    border: 1px solid rgba(229, 231, 235, 0.8);
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.feature-icon {
    width: 24px;
    height: 24px;
    color: #10B981;
    flex-shrink: 0;
}

.feature-item span {
    font-size: 14px;
    color: #374151;
    font-weight: 500;
    line-height: 1.5;
}

/* Botones de acción */
.modal-actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn-whatsapp {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 16px 24px;
    background: linear-gradient(135deg, #25D366 0%, #20BA5A 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
        0 4px 12px rgba(37, 211, 102, 0.3),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

.btn-whatsapp::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-whatsapp:hover::before {
    left: 100%;
}

.btn-whatsapp:hover {
    transform: translateY(-2px);
    box-shadow: 
        0 8px 20px rgba(37, 211, 102, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.btn-whatsapp:active {
    transform: translateY(0);
}

.btn-icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.btn-close-text {
    width: 100%;
    padding: 14px 24px;
    background: transparent;
    color: #6B7280;
    border: 1px solid #E5E7EB;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-close-text:hover {
    background: #F9FAFB;
    color: #374151;
    border-color: #D1D5DB;
}

/* Animaciones del modal */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-active .modal-container,
.modal-fade-leave-active .modal-container {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.modal-fade-enter-from .modal-container,
.modal-fade-leave-to .modal-container {
    transform: translateY(20px) scale(0.95);
    opacity: 0;
}

@keyframes modal-slide-up {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes icon-pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

@keyframes rotate-glow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Responsive para el modal */
@media (max-width: 768px) {
    .modal-overlay {
        padding: 16px;
    }

    .modal-content {
        padding: 32px 24px;
        border-radius: 20px;
    }

    .icon-circle {
        width: 70px;
        height: 70px;
    }

    .icon-clock {
        width: 36px;
        height: 36px;
    }

    .modal-title {
        font-size: 24px;
    }

    .modal-description {
        font-size: 15px;
        margin-bottom: 24px;
    }

    .modal-features {
        padding: 20px;
        gap: 14px;
        margin-bottom: 24px;
    }

    .feature-item span {
        font-size: 13px;
    }

    .btn-whatsapp {
        padding: 14px 20px;
        font-size: 15px;
    }

    .btn-close-text {
        padding: 12px 20px;
        font-size: 14px;
    }
}
</style>
