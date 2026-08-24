<script setup lang="ts">
import TextLink from '@/components/shared/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import InputError from '@/components/ui/input/InputError.vue';
import { Label } from '@/components/ui/label';
import { InertiaForm, useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

interface Emits {
    submit: [value: InertiaForm<any>];
}

const emits = defineEmits<Emits>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    emits('submit', form);
};

onMounted(() => {
    console.log(form.errors.email);
});
</script>

<template>
    <form @submit.prevent="submit" class="flex flex-col gap-6">
        <div class="grid gap-6">
            <div class="grid gap-2">
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
                >
                    <template #description>
                        <InputError :text="form.errors.email" />
                    </template>
                </Input>
            </div>

            <div class="grid gap-2">
                <Label for="password">Contraseña</Label>
                <Input
                    id="password"
                    type="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    v-model="form.password"
                    placeholder="Contraseña"
                >
                    <template #description>
                        <InputError :text="form.errors.password" />
                    </template>
                </Input>
            </div>

            <div class="flex items-center justify-between">
                <Checkbox title="Recuerdame" v-model="form.remember" :tabindex="3" />
            </div>

            <TextLink v-if="canResetPassword" :href="route('password.request')" class="forgot-password-link" :tabindex="5">
                ¿Olvidaste tu contraseña?
            </TextLink>

            <Button type="submit" class="mt-4 w-100" :tabindex="4" :loading="form.processing"> Ingresar </Button>
        </div>
    </form>
</template>

<style scoped>
.forgot-password-link {
    font-size: 13px !important;
    color: #133a54 !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    transition: color 0.3s ease !important;
    margin-top: 4px !important;
    display: inline-block !important;
}

.forgot-password-link:hover {
    color: #1a5a80 !important;
}
</style>
