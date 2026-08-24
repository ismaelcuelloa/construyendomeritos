<script setup lang="ts">
import FormLogin from '@/features/auth/components/FormLogin.vue';
import { Button } from '@/components/ui/button';
import Link from '@/components/ui/link/Link.vue';
import { Modal } from '@/components/ui/modal';
import { InertiaForm } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    show: boolean;
    static?: boolean;
}

interface Emits {
    'update:show': [value: boolean];
}

const emits = defineEmits<Emits>();

const props = withDefaults(defineProps<Props>(), {
    static: false,
});

const showActiveSessionDialog = ref(false);
const activeSessionInfo = ref({
    device_info: '',
    current_device: '',
    last_activity: '',
});
const pendingForm = ref<InertiaForm<any> | null>(null);

const submit = (form: InertiaForm<any>) => {
    form.post(route('login'), {
        onFinish: () => {
            if (!showActiveSessionDialog.value) {
                form.reset('password');
            }
        },
        onError: (errors: any) => {
            if (errors.active_session) {
                showActiveSessionDialog.value = true;
                pendingForm.value = form;
                activeSessionInfo.value = {
                    device_info: errors.device_info || 'Dispositivo desconocido',
                    current_device: errors.current_device || 'Este dispositivo',
                    last_activity: errors.last_activity || 'Recientemente',
                };
            }
        },
    });
};

const continueWithNewSession = () => {
    if (pendingForm.value) {
        showActiveSessionDialog.value = false;

        // Crear datos del formulario con force_logout
        const formData = {
            email: (pendingForm.value as any).email,
            password: (pendingForm.value as any).password,
            remember: (pendingForm.value as any).remember || false,
            force_logout: true,
        };

        console.log('AppLogin: Continuing with new session', formData);

        // Hacer el submit con los datos actualizados
        (pendingForm.value as any)
            .transform(() => formData)
            .post(route('login'), {
                onSuccess: () => {
                    console.log('AppLogin: Login successful with force logout');
                    pendingForm.value?.reset('password');
                    pendingForm.value = null;
                    emits('update:show', false);
                },
                onError: (errors: any) => {
                    console.error('AppLogin: Error on force logout login:', JSON.stringify(errors, null, 2));
                    console.error('AppLogin: Full error object:', errors);
                },
            });
    }
};

const cancelLogin = () => {
    showActiveSessionDialog.value = false;
    if (pendingForm.value) {
        pendingForm.value.clearErrors();
        pendingForm.value.reset('password');
        (pendingForm.value as any).force_logout = false;
    }
    pendingForm.value = null;
};
</script>

<template>
    <Modal :show="props.show" align="center" :static="static" title="" @close="emits('update:show', false)" :canClose="true">
        <div class="login-header">
            <h2 class="login-title">Bienvenido de nuevo</h2>
            <p class="login-subtitle">Ingresa tus credenciales para continuar</p>
        </div>

        <FormLogin :can-reset-password="true" class="mt-4" @submit="submit"> </FormLogin>

        <template #footer>
            <div class="login-footer">
                <span class="footer-text">¿No tienes una cuenta?</span>
                <Link :href="route('register')" :tabindex="5" class="footer-link">Registrarse</Link>
            </div>
        </template>
    </Modal>

    <Teleport to="body">
        <div v-if="showActiveSessionDialog" class="active-session-overlay">
            <div class="active-session-dialog">
                <div class="dialog-icon">
                    <AlertTriangle :size="48" />
                </div>
                <h3 class="dialog-title">Sesión Activa Detectada</h3>
                <p class="dialog-message">Ya existe una sesión activa con esta cuenta en otro dispositivo.</p>
                <div class="session-details">
                    <div class="detail-item">
                        <strong>Dispositivo actual:</strong>
                        <span>{{ activeSessionInfo.device_info }}</span>
                    </div>
                    <div class="detail-item">
                        <strong>Última actividad:</strong>
                        <span>{{ activeSessionInfo.last_activity }}</span>
                    </div>
                </div>
                <p class="dialog-warning">¿Deseas cerrar la sesión anterior y continuar con este dispositivo?</p>
                <div class="dialog-actions">
                    <Button @click="cancelLogin" variant="outline" class="cancel-btn"> Cancelar </Button>
                    <Button @click="continueWithNewSession" class="continue-btn"> Sí, cerrar sesión anterior </Button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
/* ============================================
   MODAL PREMIUM - Header y Estructura Base
   ============================================ */

:deep(.modal-content) {
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    border-radius: 20px;
    overflow: hidden;
    background: #ffffff;
}

:deep(.modal-header) {
    border-bottom: none;
    padding: 0;
}

:deep(.modal-body) {
    padding: 2rem 2.5rem 1.5rem;
}

:deep(.modal-close-btn) {
    position: absolute;
    top: 20px;
    right: 20px;
    z-index: 10;
    color: #999;
    transition: all 0.3s ease;
    width: 32px;
    height: 32px;
    background: rgba(0, 0, 0, 0.05);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

:deep(.modal-close-btn:hover) {
    background: #133a54;
    color: #ffffff;
    transform: rotate(90deg) scale(1.1);
}

/* ============================================
   HEADER DECORATIVO DEL LOGIN
   ============================================ */

.login-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.login-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #151515;
    margin-bottom: 0.5rem;
}

.login-subtitle {
    font-size: 0.95rem;
    color: #666666;
    margin-bottom: 0;
}

/* ============================================
   INPUTS PREMIUM
   ============================================ */

:deep(.mt-4 input) {
    border: 2px solid #e8e8e8 !important;
    border-radius: 12px !important;
    padding: 14px 18px !important;
    font-size: 15px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    background: #fafafa !important;
}

:deep(.mt-4 input:focus) {
    border-color: #133a54 !important;
    background: #ffffff !important;
    box-shadow: 0 0 0 4px rgba(19, 58, 84, 0.08) !important;
    outline: none !important;
    transform: translateY(-1px);
}

:deep(.mt-4 input::placeholder) {
    color: #aaa;
}

:deep(.mt-4 label) {
    color: #333333;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
    display: block;
}

/* ============================================
   TOGGLE SWITCH PREMIUM (Recuérdame)
   ============================================ */

:deep(.mt-4 input[type='checkbox']) {
    appearance: none !important;
    -webkit-appearance: none !important;
    width: 52px !important;
    height: 28px !important;
    min-width: 52px !important;
    cursor: pointer !important;
    border: none !important;
    border-radius: 14px !important;
    background: #d1d5db !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    position: relative !important;
    flex-shrink: 0 !important;
}

:deep(.mt-4 input[type='checkbox']::before) {
    content: '' !important;
    position: absolute !important;
    width: 22px !important;
    height: 22px !important;
    border-radius: 50% !important;
    background: #ffffff !important;
    top: 3px !important;
    left: 3px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2) !important;
}

:deep(.mt-4 input[type='checkbox']:hover) {
    background: #b8bcc4 !important;
}

:deep(.mt-4 input[type='checkbox']:checked) {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
}

:deep(.mt-4 input[type='checkbox']:checked::before) {
    left: 27px !important;
}

:deep(.mt-4 .flex label) {
    color: #333333;
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
    margin: 0 !important;
}

/* ============================================
   BOTÓN PREMIUM
   ============================================ */

:deep(.mt-4 button) {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    border: none !important;
    font-weight: 700 !important;
    padding: 14px 32px !important;
    border-radius: 12px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    box-shadow: 0 10px 25px rgba(19, 58, 84, 0.3) !important;
    position: relative;
    overflow: hidden;
    font-size: 15px !important;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    cursor: pointer;
    width: 100% !important;
}

:deep(.mt-4 button::before) {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.6s ease;
}

:deep(.mt-4 button:hover::before) {
    left: 100%;
}

:deep(.mt-4 button:hover:not(:disabled)) {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(19, 58, 84, 0.4) !important;
}

:deep(.mt-4 button:active:not(:disabled)) {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.3) !important;
}

:deep(.mt-4 button:disabled) {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ============================================
   FOOTER PREMIUM
   ============================================ */

:deep(.modal-footer) {
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
    padding: 1.5rem 2.5rem;
}

.login-footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
}

.footer-text {
    color: #666666;
    font-size: 14px;
    font-weight: 500;
}

.footer-link {
    color: #133a54;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}

.footer-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #133a54, #1a5a80);
    transition: width 0.3s ease;
}

.footer-link:hover {
    color: #1a5a80;
}

.footer-link:hover::after {
    width: 100%;
}

/* ============================================
   ESPACIADO Y GAP
   ============================================ */

:deep(.mt-4 .grid) {
    gap: 1.25rem;
}

:deep(.mt-4 .flex) {
    gap: 10px !important;
}

/* ============================================
   RESPONSIVE
   ============================================ */

@media (max-width: 576px) {
    :deep(.modal-body) {
        padding: 1.5rem 1.5rem 1rem;
    }

    :deep(.modal-footer) {
        padding: 1.25rem 1.5rem;
    }

    .login-title {
        font-size: 1.5rem;
    }

    .active-session-dialog {
        padding: 24px;
        width: 95%;
    }

    .dialog-actions {
        flex-direction: column;
    }

    .cancel-btn,
    .continue-btn {
        width: 100%;
    }
}

/* ============================================
   ACTIVE SESSION DIALOG
   ============================================ */
.active-session-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    animation: fadeIn 0.3s ease;
    padding: 20px;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        backdrop-filter: blur(0px);
        -webkit-backdrop-filter: blur(0px);
    }
    to {
        opacity: 1;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
}

.active-session-dialog {
    background: white;
    border-radius: 16px;
    padding: 32px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.dialog-icon {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
    color: #133a54;
}

.dialog-title {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    text-align: center;
    margin-bottom: 12px;
}

.dialog-message {
    font-size: 15px;
    color: #666;
    text-align: center;
    margin-bottom: 20px;
    line-height: 1.5;
}

.session-details {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 12px;
}

.detail-item:last-child {
    margin-bottom: 0;
}

.detail-item strong {
    font-size: 13px;
    color: #666;
    font-weight: 600;
}

.detail-item span {
    font-size: 14px;
    color: #333;
}

.dialog-warning {
    font-size: 15px;
    color: #333;
    text-align: center;
    margin-bottom: 24px;
    font-weight: 600;
}

.dialog-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.cancel-btn {
    flex: 1;
    border: 2px solid #e5e7eb !important;
    background: white !important;
    color: #666 !important;
    font-weight: 600 !important;
    padding: 12px 24px !important;
    border-radius: 10px !important;
    transition: all 0.3s ease !important;
}

.cancel-btn:hover {
    background: #f3f4f6 !important;
    border-color: #d1d5db !important;
}

.continue-btn {
    flex: 1;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: white !important;
    border: none !important;
    font-weight: 700 !important;
    padding: 12px 24px !important;
    border-radius: 10px !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 15px rgba(19, 58, 84, 0.3) !important;
}

.continue-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.4) !important;
}
</style>
