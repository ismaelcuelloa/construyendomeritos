<script setup lang="ts">
import InputError from '@/components/shared/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const passwordsMatch = computed(() => {
    if (!form.password || !form.password_confirmation) return true;
    return form.password === form.password_confirmation;
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout title="Restablecer Contraseña" description="Ingresa tu nueva contraseña para restablecer el acceso a tu cuenta">
        <Head title="Restablecer Contraseña" />

        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email" class="improved-label">Correo Electrónico</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="email"
                        v-model="form.email"
                        class="improved-input bg-gray-50"
                        readonly
                    />
                    <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="improved-label">Nueva Contraseña</Label>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        v-model="form.password"
                        class="improved-input"
                        autofocus
                        placeholder="Ingresa tu nueva contraseña"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" class="improved-label">Confirmar Contraseña</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        v-model="form.password_confirmation"
                        class="improved-input"
                        :class="{
                            'border-red-500': !passwordsMatch && form.password && form.password_confirmation,
                            'border-green-500': passwordsMatch && form.password && form.password_confirmation,
                        }"
                        placeholder="Confirma tu nueva contraseña"
                    />
                    <InputError :message="form.errors.password_confirmation" />

                    <!-- Alerta de contraseñas no coinciden -->
                    <div v-if="!passwordsMatch && form.password && form.password_confirmation" class="password-mismatch-badge">
                        Las contraseñas no coinciden.
                    </div>

                    <!-- Alerta de contraseñas coinciden -->
                    <div v-if="passwordsMatch && form.password && form.password_confirmation" class="password-match-badge">
                        Las contraseñas coinciden.
                    </div>
                </div>

                <Button type="submit" class="improved-submit-btn" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                    <span v-if="!form.processing">Restablecer Contraseña</span>
                    <span v-else>Procesando...</span>
                </Button>

                <div class="mt-4 text-center">
                    <a href="/login" class="improved-link"> Volver al inicio de sesión </a>
                </div>
            </div>
        </form>
    </AuthLayout>
</template>

<style scoped>
.improved-label {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.9rem;
}

.improved-input {
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
}

.improved-input:focus {
    border-color: #ff6b35;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
}

.improved-submit-btn {
    background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
    color: white;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    margin-top: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.25);
}

.improved-submit-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #ff5722 0%, #f57c00 100%);
    box-shadow: 0 6px 16px rgba(255, 107, 53, 0.35);
    transform: translateY(-2px);
}

.improved-submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.improved-link {
    color: #ff6b35;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    transition: color 0.2s ease;
}

.improved-link:hover {
    color: #f7931e;
    text-decoration: underline;
}

.password-mismatch-badge {
    display: block;
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.7) 0%, rgba(220, 38, 38, 0.7) 100%);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    margin-top: 0.5rem;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
}

.password-match-badge {
    display: block;
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.7) 0%, rgba(22, 163, 74, 0.7) 100%);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    margin-top: 0.5rem;
    margin-left: auto;
    margin-right: auto;
    text-align: center;
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
}
</style>
