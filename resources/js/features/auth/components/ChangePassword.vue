<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input, InputError } from '@/components/ui/input';
import Toast from '@/composables/toast';
import { Client } from '@/lib/client';
import { ref, watch } from 'vue';

interface Props {
    open: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:open']);

const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');
const saving = ref(false);

const errorCurrentPassword = ref('');
const errorNewPassword = ref('');
const errorConfirmPassword = ref('');

const resetForm = () => {
    currentPassword.value = '';
    newPassword.value = '';
    confirmPassword.value = '';
    errorCurrentPassword.value = '';
    errorNewPassword.value = '';
    errorConfirmPassword.value = '';
};

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

        Toast.success('Contraseña actualizada exitosamente');
        resetForm();
        emit('update:open', false);
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
    } finally {
        saving.value = false;
    }
};

watch(
    () => props.open,
    (newValue) => {
        if (!newValue) {
            resetForm();
        }
    },
);
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="change-password-modal">
            <DialogHeader>
                <DialogTitle>Cambiar Contraseña</DialogTitle>
                <DialogDescription>Ingresa tu contraseña actual y tu nueva contraseña</DialogDescription>
            </DialogHeader>

            <div class="form-content">
                <Input :disabled="saving" title="Contraseña Actual" type="password" v-model="currentPassword">
                    <template v-if="errorCurrentPassword" #description>
                        <InputError :text="errorCurrentPassword" />
                    </template>
                </Input>

                <Input :disabled="saving" title="Nueva Contraseña" type="password" v-model="newPassword">
                    <template v-if="errorNewPassword" #description>
                        <InputError :text="errorNewPassword" />
                    </template>
                </Input>

                <Input :disabled="saving" title="Confirmar Nueva Contraseña" type="password" v-model="confirmPassword">
                    <template v-if="errorConfirmPassword" #description>
                        <InputError :text="errorConfirmPassword" />
                    </template>
                </Input>
            </div>

            <DialogFooter>
                <Button :disabled="saving" variant="outline" @click="emit('update:open', false)">Cancelar</Button>
                <Button :loading="saving" @click="handleSave">Guardar</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.change-password-modal {
    max-width: 500px;
}

.form-content {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin: 1.5rem 0;
}

:deep(.input-wrapper) {
    margin-bottom: 0;
}
</style>
