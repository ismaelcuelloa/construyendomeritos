<script setup lang="ts">
import InputError from '@/components/shared/InputError.vue';
import TextLink from '@/components/shared/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle, ShoppingCart } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms_accepted: false,
    data_treatment_accepted: false,
});

const showCheckoutMessage = ref(false);

onMounted(() => {
    // Verificar si viene del checkout
    const checkoutRedirect = localStorage.getItem('checkout_redirect');
    if (checkoutRedirect === 'true') {
        showCheckoutMessage.value = true;
        localStorage.removeItem('checkout_redirect');
    }
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Crea tu cuenta" description="Únete y comienza a prepararte para tus exámenes">
        <Head title="Registrarse" />

        <!-- Mensaje para usuarios que vienen del checkout -->
        <div v-if="showCheckoutMessage" class="checkout-message">
            <div class="message-icon">
                <ShoppingCart :size="20" />
            </div>
            <div class="message-content">
                <h5 class="message-title">¡Un paso más!</h5>
                <p class="message-text">Para comprar este material necesitas crear una cuenta. Es rápido y gratis.</p>
            </div>
        </div>

        <form @submit.prevent="submit" class="register-form">
            <div class="form-fields">
                <div class="field-group">
                    <Label for="name">Nombre completo</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        v-model="form.name"
                        placeholder="Tu nombre completo"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="field-group">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email" placeholder="email@example.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="field-group">
                    <Label for="phone">Teléfono</Label>
                    <Input id="phone" type="tel" required :tabindex="3" autocomplete="tel" v-model="form.phone" placeholder="300 123 4567" />
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="field-group">
                    <Label for="password">Contraseña</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        v-model="form.password"
                        placeholder="Mínimo 8 caracteres"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="field-group">
                    <Label for="password_confirmation">Confirmar contraseña</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="5"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        placeholder="Confirma tu contraseña"
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <div class="terms-section">
                    <div class="terms-checkbox-group">
                        <label for="terms_accepted" class="terms-label">
                            <input type="checkbox" id="terms_accepted" v-model="form.terms_accepted" :tabindex="6" required />
                            <span>
                                Acepto los
                                <a href="/terminos-de-servicio" target="_blank" class="terms-link">términos y condiciones.</a>
                            </span>
                        </label>
                        <InputError :message="form.errors.terms_accepted" />
                    </div>

                    <div class="terms-checkbox-group">
                        <label for="data_treatment_accepted" class="terms-label">
                            <input type="checkbox" id="data_treatment_accepted" v-model="form.data_treatment_accepted" :tabindex="7" required />
                            <span>
                                Autorizo el
                                <a href="/politica-de-privacidad" target="_blank" class="terms-link">tratamiento de datos personales</a>
                                conforme a las finalidades establecidas en su política de tratamiento de datos personales.
                            </span>
                        </label>
                        <InputError :message="form.errors.data_treatment_accepted" />
                    </div>
                </div>

                <Button type="submit" class="submit-btn" tabindex="8" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="loader-icon" />
                    <span v-if="!form.processing">CREAR CUENTA</span>
                    <span v-else>Creando cuenta...</span>
                </Button>
            </div>

            <div class="login-section">
                <span class="login-text">¿Ya tienes una cuenta?</span>
                <TextLink :href="route('login')" class="login-link" :tabindex="9">Iniciar sesión</TextLink>
            </div>
        </form>
    </AuthBase>
</template>

<style scoped>
/* ============================================
   CHECKOUT MESSAGE
   ============================================ */
.checkout-message {
    display: flex;
    gap: 12px;
    padding: 16px;
    background: linear-gradient(135deg, rgba(245, 228, 44, 0.08) 0%, rgba(26, 90, 128, 0.04) 100%);
    border: 2px solid rgba(245, 228, 44, 0.2);
    border-radius: 12px;
    margin-bottom: 20px;
    animation: slideDown 0.4s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-icon {
    width: 40px;
    height: 40px;
    background: #f5e42c;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    flex-shrink: 0;
}

.message-content {
    flex: 1;
}

.message-title {
    font-size: 14px;
    font-weight: 700;
    color: #133a54;
    margin: 0 0 4px 0;
}

.message-text {
    font-size: 13px;
    color: #133a54;
    margin: 0;
    line-height: 1.5;
}

/* ============================================
   FORM CONTAINER
   ============================================ */
.register-form {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.form-fields {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
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
    color: #133a54;
    font-weight: 600;
    font-size: 12px;
    display: block;
}

/* ============================================
   INPUTS PREMIUM
   ============================================ */
:deep(input[type='text']),
:deep(input[type='email']),
:deep(input[type='password']) {
    border: 2px solid #e8e8e8 !important;
    border-radius: 10px !important;
    padding: 9px 12px !important;
    font-size: 13px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    background: #fafafa !important;
    width: 100%;
}

:deep(input[type='text']:focus),
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
   SUBMIT BUTTON PREMIUM
   ============================================ */
.submit-btn {
    background: linear-gradient(135deg, #f5e42c 0%, #ffe566 100%) !important;
    color: #ffffff !important;
    border: none !important;
    font-weight: 700 !important;
    padding: 10px 28px !important;
    border-radius: 10px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    box-shadow: 0 10px 25px rgba(245, 228, 44, 0.3) !important;
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
    margin-top: 0.15rem;
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
    color: #133a54;
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
    color: #133a54;
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
    font-size: 13px;
    margin-top: 4px;
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 640px) {
    .checkout-message {
        padding: 12px;
        font-size: 12px;
        gap: 10px;
    }

    .message-icon {
        width: 36px;
        height: 36px;
    }

    .message-title {
        font-size: 13px;
    }

    .message-text {
        font-size: 12px;
    }

    .form-fields {
        gap: 0.5rem;
    }

    .field-group {
        gap: 3px;
    }

    :deep(label) {
        font-size: 11px;
    }

    :deep(input[type='text']),
    :deep(input[type='email']),
    :deep(input[type='tel']),
    :deep(input[type='password']) {
        padding: 8px 10px !important;
        font-size: 14px !important;
        border-radius: 8px !important;
    }

    .submit-btn {
        padding: 11px 24px !important;
        font-size: 12px !important;
        border-radius: 8px !important;
        min-height: 42px;
    }

    .terms-section {
        margin: 0.75rem 0;
        gap: 10px;
    }

    .terms-label {
        font-size: 10px;
        gap: 6px;
    }

    .terms-section input[type='checkbox'] {
        width: 15px !important;
        height: 15px !important;
        min-width: 15px !important;
        max-width: 15px !important;
        min-height: 15px !important;
        max-height: 15px !important;
    }

    .login-section {
        padding-top: 0.5rem;
    }

    .login-text {
        font-size: 11px;
    }

    .login-link {
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .field-group {
        gap: 2px;
    }

    :deep(input[type='text']),
    :deep(input[type='email']),
    :deep(input[type='tel']),
    :deep(input[type='password']) {
        padding: 9px 12px !important;
        font-size: 16px !important;
    }
}
</style>
