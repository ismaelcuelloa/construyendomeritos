<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input, InputError } from '@/components/ui/input';
import Container from '@/components/ui/Container.vue';
import Toast from '@/composables/toast';
import AppLayout from '@/layouts/AppLayout.vue';
import { Client } from '@/lib/client';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');
const saving = ref(false);

const errorCurrentPassword = ref('');
const errorNewPassword = ref('');
const errorConfirmPassword = ref('');

const resetErrors = () => {
    errorCurrentPassword.value = '';
    errorNewPassword.value = '';
    errorConfirmPassword.value = '';
};

const validate = (): boolean => {
    resetErrors();
    let isValid = true;

    if (currentPassword.value.trim() === '') {
        errorCurrentPassword.value = 'La contraseña actual es requerida';
        isValid = false;
    }

    if (newPassword.value.trim() === '') {
        errorNewPassword.value = 'La nueva contraseña es requerida';
        isValid = false;
    } else if (newPassword.value.length < 8) {
        errorNewPassword.value = 'La contraseña debe tener al menos 8 caracteres';
        isValid = false;
    }

    if (confirmPassword.value.trim() === '') {
        errorConfirmPassword.value = 'Debe confirmar la nueva contraseña';
        isValid = false;
    } else if (newPassword.value !== confirmPassword.value) {
        errorConfirmPassword.value = 'Las contraseñas no coinciden';
        isValid = false;
    }

    return isValid;
};

const handleSave = async () => {
    if (!validate()) return;

    saving.value = true;

    try {
        await Client.post('/users/change-password', {
            current_password: currentPassword.value,
            new_password: newPassword.value,
            new_password_confirmation: confirmPassword.value,
        });

        Toast.success('Contraseña actualizada exitosamente. Redirigiendo...');

        // Limpiar el formulario
        currentPassword.value = '';
        newPassword.value = '';
        confirmPassword.value = '';
        resetErrors();

        // Redirigir a la página principal después de 2 segundos
        setTimeout(() => {
            router.visit('/');
        }, 2000);
    } catch (error: any) {
        if (error.response?.data?.message) {
            Toast.error(error.response.data.message);
        } else if (error.response?.data?.errors) {
            const errors = error.response.data.errors;
            if (errors.current_password) {
                errorCurrentPassword.value = errors.current_password[0];
            }
            if (errors.new_password) {
                errorNewPassword.value = errors.new_password[0];
            }
        } else {
            Toast.error('Error al cambiar la contraseña');
        }
        saving.value = false;
    }
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <AppLayout>
        <Container>
            <div class="change-password-wrapper">
                <div class="row justify-content-center">
                    <div class="col-lg-8 col-xl-6">
                        <Card class="change-password-card">
                            <CardHeader>
                                <div class="header-content">
                                    <CardTitle class="card-title-main">
                                        <i class="feather-lock"></i>
                                        <div class="title-text">
                                            <span class="main-title">Cambiar Contraseña</span>
                                            <span class="card-subtitle">Actualiza tu contraseña de forma segura</span>
                                        </div>
                                    </CardTitle>
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div class="change-password-form">
                                    <Input
                                        :disabled="saving"
                                        title="Contraseña Actual"
                                        type="password"
                                        v-model="currentPassword"
                                        placeholder="Ingresa tu contraseña actual"
                                    >
                                        <template v-if="errorCurrentPassword" #description>
                                            <InputError :text="errorCurrentPassword" />
                                        </template>
                                    </Input>

                                    <Input
                                        :disabled="saving"
                                        title="Nueva Contraseña"
                                        type="password"
                                        v-model="newPassword"
                                        placeholder="Mínimo 8 caracteres"
                                    >
                                        <template v-if="errorNewPassword" #description>
                                            <InputError :text="errorNewPassword" />
                                        </template>
                                    </Input>

                                    <Input
                                        :disabled="saving"
                                        title="Confirmar Nueva Contraseña"
                                        type="password"
                                        v-model="confirmPassword"
                                        placeholder="Confirma tu nueva contraseña"
                                    >
                                        <template v-if="errorConfirmPassword" #description>
                                            <InputError :text="errorConfirmPassword" />
                                        </template>
                                    </Input>

                                    <div class="form-actions">
                                        <Button :disabled="saving" variant="outline" @click="goBack" size="default" class="btn-cancel">
                                            <i class="feather-arrow-left"></i>
                                            <span>Cancelar</span>
                                        </Button>
                                        <Button :loading="saving" @click="handleSave" size="default" class="btn-save">
                                            <i class="feather-check"></i>
                                            <span>Guardar Cambios</span>
                                        </Button>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>

<style scoped>
.change-password-wrapper {
    padding: 60px 0;
    min-height: 60vh;
}

.change-password-card {
    border: 2px solid rgba(19, 58, 84, 0.15) !important;
    border-radius: 20px !important;
    box-shadow: 0 8px 24px rgba(19, 58, 84, 0.1) !important;
    background: #ffffff !important;
    overflow: hidden;
}

.change-password-card :deep(.card-header) {
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(26, 90, 128, 0.05) 100%) !important;
    border-bottom: 2px solid rgba(19, 58, 84, 0.2) !important;
    padding: 32px !important;
}

.card-title-main {
    font-size: 28px !important;
    font-weight: 900 !important;
    color: #1a1a1a !important;
    letter-spacing: -0.8px !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
}

.card-title-main i {
    font-size: 28px;
    color: #133a54;
    flex-shrink: 0;
}

.title-text {
    display: flex;
    align-items: baseline;
    gap: 8px;
    flex-wrap: wrap;
}

.main-title {
    font-size: 28px;
    font-weight: 900;
    color: #1a1a1a;
}

.card-subtitle {
    font-size: 15px;
    color: #666;
    font-weight: 400;
}

.change-password-form {
    padding: 32px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.form-actions {
    display: flex;
    gap: 16px;
    justify-content: flex-end;
    margin-top: 16px;
    padding-top: 24px;
    border-top: 1px solid rgba(19, 58, 84, 0.1);
}

/* Button Styles */
.btn-cancel {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 12px 28px !important;
    font-size: 15px !important;
    font-weight: 600 !important;
    border-radius: 10px !important;
    border: 2px solid #e0e0e0 !important;
    color: #666 !important;
    background: #ffffff !important;
    transition: all 0.3s ease !important;
}

.btn-cancel:hover:not(:disabled) {
    border-color: #133a54 !important;
    color: #133a54 !important;
    background: rgba(19, 58, 84, 0.05) !important;
    transform: translateY(-2px);
}

.btn-cancel i {
    font-size: 18px;
}

.btn-save {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    padding: 12px 32px !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    border-radius: 10px !important;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    border: none !important;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.25) !important;
    transition: all 0.3s ease !important;
}

.btn-save:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.35) !important;
}

.btn-save i {
    font-size: 18px;
}

/* Input Styles */
:deep(.input-wrapper) {
    margin-bottom: 0;
}

:deep(.input-label) {
    color: #151515 !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    letter-spacing: 0.5px !important;
    margin-bottom: 10px !important;
    text-transform: uppercase;
}

:deep(.input-field) {
    border: 2px solid #e0e0e0 !important;
    border-radius: 10px !important;
    padding: 14px 18px !important;
    font-size: 15px !important;
    transition: all 0.3s ease !important;
    background: #ffffff !important;
}

:deep(.input-field:focus) {
    border-color: #133a54 !important;
    box-shadow: 0 0 0 4px rgba(19, 58, 84, 0.1) !important;
    outline: none !important;
}

:deep(.input-field:disabled) {
    background: #f5f5f5 !important;
    color: #999 !important;
    cursor: not-allowed !important;
}

@media (max-width: 768px) {
    .change-password-wrapper {
        padding: 40px 0;
    }

    .change-password-form {
        padding: 24px;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn-cancel,
    .btn-save {
        width: 100%;
        justify-content: center !important;
    }
}
</style>
