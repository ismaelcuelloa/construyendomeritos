<script setup lang="ts">
import InputError from '@/components/shared/InputError.vue';
import SeoHead from '@/components/shared/SeoHead.vue';
import TextLink from '@/components/shared/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { AlertTriangle, LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    forceLogout?: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
    force_logout: false,
});

const showActiveSessionDialog = ref(false);
const activeSessionInfo = ref({
    device_info: '',
    current_device: '',
    last_activity: '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            // Solo resetear si no hay un error de sesión activa
            if (!showActiveSessionDialog.value) {
                form.reset('password');
            }
        },
        onError: (errors) => {
            // @ts-expect-error - errors puede contener active_session
            if (errors.active_session) {
                showActiveSessionDialog.value = true;
                // @ts-expect-error - errors contiene propiedades de sesión activa
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
    showActiveSessionDialog.value = false;

    // Crear datos del formulario con force_logout
    const formData = {
        email: form.email,
        password: form.password,
        remember: form.remember || false,
        force_logout: true,
    };

    console.log('Login: Continuing with new session', formData);

    // Hacer el submit con los datos actualizados
    form.transform(() => formData).post(route('login'), {
        onSuccess: () => {
            console.log('Login: Login successful with force logout');
            form.reset('password');
        },
        onError: (errors) => {
            console.error('Login: Error on force logout login:', JSON.stringify(errors, null, 2));
            console.error('Login: Full error object:', errors);
        },
    });
};

const cancelLogin = () => {
    showActiveSessionDialog.value = false;
    form.clearErrors();
    form.reset('password');
    form.force_logout = false;
};
</script>

<template>
    <SeoHead
        title="Iniciar Sesión - Construyendo Méritos con Excelencia"
        description="Accede a tu cuenta de Construyendo Méritos con Excelencia. Continúa tu aprendizaje con nuestros cursos y simulacros."
        :noindex="true"
    />
    <AuthBase title="Bienvenido de nuevo" description="Ingresa tus credenciales para continuar">
        <Head title="Iniciar Sesión" />

        <div v-if="status" class="status-message">
            {{ status }}
        </div>

        <!-- Diálogo de Sesión Activa usando Teleport -->
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

        <form @submit.prevent="submit" class="login-form">
            <div class="form-fields">
                <div class="field-group">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        v-model="form.email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="field-group">
                    <Label for="password">Contraseña</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        v-model="form.password"
                        placeholder="Contraseña"
                    />
                    <InputError :message="form.errors.password" />
                    <TextLink v-if="canResetPassword" :href="route('password.request')" class="forgot-link-below" :tabindex="8">
                        ¿Olvidaste tu contraseña?
                    </TextLink>
                </div>

                <div class="remember-section">
                    <Label for="remember" class="remember-label">
                        <Checkbox id="remember" v-model="form.remember" :tabindex="3" />
                        <span>Recuérdame</span>
                    </Label>
                </div>

                <!-- ...eliminado: sección de términos y tratamiento de datos personales... -->

                <Button type="submit" class="submit-btn" :tabindex="6" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="loader-icon" />
                    <span v-if="!form.processing">INGRESAR</span>
                    <span v-else>Ingresando...</span>
                </Button>
            </div>

            <div class="register-section">
                <span class="register-text">¿No tienes una cuenta?</span>
                <TextLink :href="route('register')" class="register-link" :tabindex="9">Registrarse</TextLink>
            </div>
        </form>
    </AuthBase>
</template>

<style scoped>
/* ============================================
   STATUS MESSAGE
   ============================================ */
.status-message {
    margin-bottom: 1.5rem;
    text-align: center;
    font-size: 14px;
    font-weight: 500;
    color: #059669;
    background: #d1fae5;
    padding: 12px;
    border-radius: 8px;
}

/* ============================================
   FORM CONTAINER
   ============================================ */
.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-fields {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

/* ============================================
   LABELS
   ============================================ */
:deep(label) {
    color: #133a54;
    font-weight: 600;
    font-size: 14px;
    display: block;
}

.forgot-link-below {
    font-size: 13px;
    color: #133a54;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
    margin-top: 4px;
    display: inline-block;
}

.forgot-link-below:hover {
    color: #0a2135;
}

/* ============================================
   INPUTS PREMIUM
   ============================================ */
:deep(input[type='email']),
:deep(input[type='password']) {
    border: 2px solid #e8e8e8 !important;
    border-radius: 12px !important;
    padding: 12px 16px !important;
    font-size: 15px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    background: #fafafa !important;
    width: 100%;
}

:deep(input[type='email']:focus),
:deep(input[type='password']:focus) {
    border-color: #f5e42c !important;
    background: #ffffff !important;
    box-shadow: 0 0 0 4px rgba(245, 228, 44, 0.08) !important;
    outline: none !important;
    transform: translateY(-1px);
}

:deep(input::placeholder) {
    color: #aaa;
}

/* ============================================
   REMEMBER ME SECTION
   ============================================ */
.remember-section {
    display: flex;
    align-items: center;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: 500 !important;
    margin: 0 !important;
}

/* Toggle Switch Premium - Solo para Remember Me */
.remember-section :deep(input[type='checkbox']) {
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

.remember-section :deep(input[type='checkbox']::before) {
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

.remember-section :deep(input[type='checkbox']:hover) {
    background: #b8bcc4 !important;
}

.remember-section :deep(input[type='checkbox']:checked) {
    background: linear-gradient(135deg, #f5e42c 0%, #ffe566 100%) !important;
}

.remember-section :deep(input[type='checkbox']:checked::before) {
    left: 27px !important;
}

/* ============================================
   TERMS SECTION
   ============================================ */
.terms-section {
    margin: 1rem 0;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.terms-checkbox-group {
    display: flex;
    flex-direction: column;
}

.terms-label {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    cursor: pointer;
    font-size: 11px;
    line-height: 1.4;
    color: #666;
    font-weight: 500 !important;
}

.terms-label span {
    flex: 1;
}

/* Resetear y establecer estilos específicos para checkboxes de términos */
.terms-section input[type='checkbox'] {
    all: unset;
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    width: 16px !important;
    height: 16px !important;
    min-width: 16px !important;
    max-width: 16px !important;
    min-height: 16px !important;
    max-height: 16px !important;
    border: 2px solid #d1d5db !important;
    border-radius: 3px !important;
    cursor: pointer !important;
    position: relative !important;
    transition: all 0.3s ease !important;
    flex-shrink: 0 !important;
    margin: 1px 0 0 0 !important;
    padding: 0 !important;
    background-color: white !important;
    display: inline-block !important;
    vertical-align: top !important;
    box-sizing: border-box !important;
}

.terms-section input[type='checkbox']:hover {
    border-color: #f5e42c !important;
}

.terms-section input[type='checkbox']:checked {
    background: linear-gradient(135deg, #f5e42c 0%, #ffe566 100%) !important;
    border-color: #f5e42c !important;
}

.terms-section input[type='checkbox']:checked::after {
    content: '✓' !important;
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    color: white !important;
    font-size: 11px !important;
    font-weight: bold !important;
    line-height: 1 !important;
    display: block !important;
}

.terms-section input[type='checkbox']::before {
    display: none !important;
}

.terms-link {
    color: #133a54;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.terms-link:hover {
    color: #1a5a80;
    text-decoration: underline;
}

/* ============================================
   SUBMIT BUTTON PREMIUM
   ============================================ */
.submit-btn {
    background: linear-gradient(135deg, #f5e42c 0%, #ffe566 100%) !important;
    color: #ffffff !important;
    border: none !important;
    font-weight: 700 !important;
    padding: 12px 32px !important;
    border-radius: 12px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    box-shadow: 0 10px 25px rgba(245, 228, 44, 0.3) !important;
    position: relative;
    overflow: hidden;
    font-size: 15px !important;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    cursor: pointer;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 48px;
}

.submit-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.6s ease;
}

.submit-btn:hover::before {
    left: 100%;
}

.submit-btn:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px rgba(245, 228, 44, 0.4) !important;
}

.submit-btn:active:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(245, 228, 44, 0.3) !important;
}

.submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.loader-icon {
    width: 18px;
    height: 18px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* ============================================
   REGISTER SECTION
   ============================================ */
.register-section {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding-top: 1.25rem;
    border-top: 1px solid #f0f0f0;
}

.register-text {
    color: #133a54;
    font-size: 14px;
    font-weight: 500;
}

.register-link {
    color: #133a54;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}

.register-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #133a54, #1a5a80);
    transition: width 0.3s ease;
}

.register-link:hover {
    color: #1a5a80;
}

.register-link:hover::after {
    width: 100%;
}

/* ============================================
   ERROR MESSAGES
   ============================================ */
:deep(.text-red-600) {
    color: #dc2626;
    font-size: 13px;
    margin-top: 4px;
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
    z-index: 9999;
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
    color: #f5e42c;
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
    background: linear-gradient(135deg, #f5e42c 0%, #ffe566 100%) !important;
    color: white !important;
    border: none !important;
    font-weight: 700 !important;
    padding: 12px 24px !important;
    border-radius: 10px !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 15px rgba(245, 228, 44, 0.3) !important;
}

.continue-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 228, 44, 0.4) !important;
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 576px) {
    .form-fields {
        gap: 1rem;
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
</style>
