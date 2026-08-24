<script setup lang="ts">
import InputError from '@/components/shared/InputError.vue';
import TextLink from '@/components/shared/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout title="Recuperar contraseña" description="Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña">
        <Head title="Recuperar contraseña" />

        <div v-if="status" class="status-message">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="forgot-form">
            <div class="form-fields">
                <div class="field-group">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" name="email" autocomplete="off" v-model="form.email" autofocus placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <Button type="submit" class="submit-btn" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="loader-icon" />
                    <span v-if="!form.processing">ENVIAR ENLACE</span>
                    <span v-else>Enviando...</span>
                </Button>
            </div>

            <div class="login-section">
                <span class="login-text">¿Recordaste tu contraseña?</span>
                <TextLink :href="route('login')" class="login-link">Iniciar sesión</TextLink>
            </div>
        </form>
    </AuthLayout>
</template>

<style scoped>
/* ============================================
   STATUS MESSAGE
   ============================================ */
.status-message {
    margin-bottom: 1rem;
    text-align: center;
    font-size: 13px;
    font-weight: 500;
    color: #059669;
    background: #d1fae5;
    padding: 10px;
    border-radius: 8px;
}

/* ============================================
   FORM CONTAINER
   ============================================ */
.forgot-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-fields {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.field-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

/* ============================================
   LABELS
   ============================================ */
:deep(label) {
    color: #333333;
    font-weight: 600;
    font-size: 12px;
    display: block;
}

/* ============================================
   INPUTS PREMIUM
   ============================================ */
:deep(input[type='email']) {
    border: 2px solid #e8e8e8 !important;
    border-radius: 10px !important;
    padding: 9px 12px !important;
    font-size: 13px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    background: #fafafa !important;
    width: 100%;
}

:deep(input[type='email']:focus) {
    border-color: #133a54 !important;
    background: #ffffff !important;
    box-shadow: 0 0 0 4px rgba(19, 58, 84, 0.08) !important;
    outline: none !important;
    transform: translateY(-1px);
}

:deep(input::placeholder) {
    color: #aaa;
}

/* ============================================
   SUBMIT BUTTON PREMIUM
   ============================================ */
.submit-btn {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    border: none !important;
    font-weight: 700 !important;
    padding: 10px 28px !important;
    border-radius: 10px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    box-shadow: 0 10px 25px rgba(19, 58, 84, 0.3) !important;
    position: relative;
    overflow: hidden;
    font-size: 13px !important;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    cursor: pointer;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 40px;
    margin-top: 0.25rem;
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
    box-shadow: 0 15px 35px rgba(19, 58, 84, 0.4) !important;
}

.submit-btn:active:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.3) !important;
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
   LOGIN SECTION
   ============================================ */
.login-section {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding-top: 0.75rem;
    border-top: 1px solid #f0f0f0;
}

.login-text {
    color: #666666;
    font-size: 12px;
    font-weight: 500;
}

.login-link {
    color: #133a54;
    font-weight: 700;
    font-size: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}

.login-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #133a54, #1a5a80);
    transition: width 0.3s ease;
}

.login-link:hover {
    color: #1a5a80;
}

.login-link:hover::after {
    width: 100%;
}

/* ============================================
   ERROR MESSAGES
   ============================================ */
:deep(.text-red-600) {
    color: #dc2626;
    font-size: 12px;
    margin-top: 4px;
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 576px) {
    .form-fields {
        gap: 0.6rem;
    }
}
</style>
