<script setup lang="ts">
import AppSelectCourses from '@/features/courses/components/AppSelectCourses.vue';
import { Button } from '@/components/ui/button';
import { Input, InputError } from '@/components/ui/input';
import Modal from '@/components/ui/modal/Modal.vue';
import SelectOrderStatus from '@/features/orders/components/selects/SelectOrderStatus.vue';
import SelectRoles from '@/features/admin/components/selects/SelectRoles.vue';
import { Client } from '@/lib/client';
import { ref, watch } from 'vue';

const emit = defineEmits<{
    (e: 'onSave', user: any): void;
    (e: 'update:open', value: boolean): void;
}>();

const props = defineProps<{
    open?: boolean;
}>();

const isOpen = ref(props.open || false);

watch(
    () => props.open,
    (newValue) => {
        isOpen.value = newValue ?? false;
        if (newValue) {
            reset();
        }
    },
);

const close = () => {
    emit('update:open', false);
};

const saving = ref(false);
const names = ref('');
const email = ref('');
const phone = ref('');
const password = ref('');
const password_confirmation = ref('');
const role = ref('');
const orderStatus = ref('2'); // Por defecto Pagado
const selectedCourses = ref<any[]>([]);
const isOpenSelectCourses = ref(false);

const description_names = ref('');
const description_email = ref('');
const description_phone = ref('');
const description_password = ref('');
const description_password_confirmation = ref('');
const description_role = ref('');

const save = async () => {
    if (validate()) {
        saving.value = true;

        try {
            const params = {
                name: names.value,
                email: email.value,
                phone: phone.value,
                password: password.value,
                role: role.value,
                order_status: orderStatus.value,
                courses: selectedCourses.value.map((course) => course.id),
            };

            console.log('Enviando datos:', params);
            const response = await Client.post(Client.ADMIN_USERS, params);
            emit('onSave', response.data.user);
            close();
            reset();
        } catch (e: any) {
            console.error('Error completo:', e);
            console.error('Response data:', e.response?.data);
            console.error('Response status:', e.response?.status);

            // Manejar errores de validación del backend (422)
            if (e.response?.status === 422 && e.response?.data?.errors) {
                const errors = e.response.data.errors;
                if (errors.name) description_names.value = errors.name[0];
                if (errors.email) description_email.value = errors.email[0];
                if (errors.phone) description_phone.value = errors.phone[0];
                if (errors.password) description_password.value = errors.password[0];
                if (errors.role) description_role.value = errors.role[0];
            } else if (e.response?.status === 500) {
                // Error del servidor
                const errorMessage = e.response?.data?.message || 'Error interno del servidor';
                alert(`Error 500: ${errorMessage}\n\nRevisa los logs del servidor para más detalles.`);
            } else {
                // Error genérico
                const errorMessage = e.response?.data?.message || e.message || 'Error desconocido';
                alert(`Error al crear el usuario: ${errorMessage}`);
            }
        }

        saving.value = false;
    }
};

const validate = () => {
    let validate = true;
    resetDescriptionsFields();
    if (names.value.trim() == '') {
        description_names.value = 'El campo es requerido';
        validate = false;
    }

    if (email.value.trim() == '') {
        description_email.value = 'El campo es requerido';
        validate = false;
    }

    if (phone.value.trim() == '') {
        description_phone.value = 'El campo es requerido';
        validate = false;
    }

    if (password.value.trim() == '') {
        description_password.value = 'El campo es requerido';
        validate = false;
    }

    if (password_confirmation.value.trim() == '') {
        description_password_confirmation.value = 'El campo es requerido';
        validate = false;
    }

    if (password.value.trim() != password_confirmation.value.trim()) {
        description_password.value = 'Las contraseñas no coinciden';
        description_password_confirmation.value = 'Las contraseñas no coinciden';
        validate = false;
    }

    if (role.value.trim() === '') {
        description_role.value = 'El campo es requerido';
        validate = false;
    }

    return validate;
};

const reset = () => {
    if (!saving.value) {
        resetFields();
        resetDescriptionsFields();
    }
};

const resetFields = () => {
    names.value = '';
    email.value = '';
    phone.value = '';
    password.value = '';
    password_confirmation.value = '';
    orderStatus.value = '2'; // Resetear a Pagado
    role.value = '';
    selectedCourses.value = [];
};

const resetDescriptionsFields = () => {
    description_names.value = '';
    description_email.value = '';
    description_phone.value = '';
    description_password.value = '';
    description_password_confirmation.value = '';
    description_role.value = '';
};

const openSelectCourses = (value: boolean = true) => {
    isOpenSelectCourses.value = value;
};

const onSelectedCourse = (course: any) => {
    // Verificar si el curso ya está seleccionado
    const exists = selectedCourses.value.find((c) => c.id === course.id);
    if (!exists) {
        selectedCourses.value.push(course);
    }
};

const removeCourse = (courseId: number) => {
    selectedCourses.value = selectedCourses.value.filter((c) => c.id !== courseId);
};
</script>

<template>
    <Modal :show="isOpen" @update:show="(val) => (isOpen = val)" title="Crear Nuevo Usuario" size="lg">
        <div class="create-user-modal-body">
            <div class="form-section">
                <div class="section-label">Información Personal</div>

                <Input :disabled="saving" title="Nombres" v-model="names" placeholder="Ej: Juan Carlos García" class="mb-4">
                    <template v-if="description_names.trim() != ''" #description>
                        <InputError :text="description_names" />
                    </template>
                </Input>

                <Input :disabled="saving" title="Correo Electrónico" v-model="email" type="email" placeholder="Ej: usuario@ejemplo.com" class="mb-4">
                    <template v-if="description_email.trim() != ''" #description>
                        <InputError :text="description_email" />
                    </template>
                </Input>

                <Input :disabled="saving" title="Número de Teléfono" v-model="phone" type="tel" placeholder="Ej: 3001234567" class="mb-4">
                    <template v-if="description_phone.trim() != ''" #description>
                        <InputError :text="description_phone" />
                    </template>
                </Input>
            </div>

            <div class="form-section">
                <div class="section-label">Credenciales de Acceso</div>

                <Input :disabled="saving" type="password" title="Contraseña" v-model="password" placeholder="Mínimo 8 caracteres" class="mb-4">
                    <template v-if="description_password.trim() != ''" #description>
                        <InputError :text="description_password" />
                    </template>
                </Input>

                <Input
                    :disabled="saving"
                    type="password"
                    title="Confirmar Contraseña"
                    v-model="password_confirmation"
                    placeholder="Repite tu contraseña"
                    class="mb-4"
                >
                    <template v-if="description_password_confirmation.trim() != ''" #description>
                        <InputError :text="description_password_confirmation" />
                    </template>
                </Input>
            </div>

            <div class="form-section">
                <div class="section-label">Rol y Permisos</div>

                <SelectRoles :disabled="saving" v-model="role">
                    <template v-if="description_role.trim() != ''" #description>
                        <InputError :text="description_role" />
                    </template>
                </SelectRoles>
            </div>

            <div v-if="selectedCourses.length > 0" class="form-section">
                <div class="section-label">
                    <i class="feather-credit-card"></i>
                    Estado de Pago de la Orden
                </div>

                <SelectOrderStatus :disabled="saving" v-model="orderStatus" />

                <small class="info-text">
                    <i class="feather-info"></i>
                    Estado que tendrá la orden creada al matricular cursos
                </small>
            </div>

            <div class="form-section">
                <div class="section-label">
                    <i class="feather-book-open"></i>
                    Matricular Cursos (Opcional)
                </div>

                <Button :disabled="saving" @click="openSelectCourses()" size="default" class="btn-add-course">
                    <div class="btn-add-course-content">
                        <i class="feather-plus-circle"></i>
                        <div class="btn-add-course-text">
                            <span class="btn-add-course-title">Agregar Curso</span>
                            <span class="btn-add-course-subtitle">Selecciona cursos para matricular automáticamente</span>
                        </div>
                    </div>
                </Button>

                <div v-if="selectedCourses.length > 0" class="selected-courses-list">
                    <div class="courses-header">
                        <span class="courses-count">
                            <i class="feather-layers"></i>
                            {{ selectedCourses.length }} curso{{ selectedCourses.length !== 1 ? 's' : '' }} seleccionado{{
                                selectedCourses.length !== 1 ? 's' : ''
                            }}
                        </span>
                        <span class="courses-total">
                            Total: ${{ new Intl.NumberFormat('es-CO').format(selectedCourses.reduce((sum, c) => sum + Number(c.price), 0)) }} COP
                        </span>
                    </div>

                    <div v-for="course in selectedCourses" :key="course.id" class="course-card">
                        <div class="course-card-image">
                            <img v-if="course.image" :src="course.image" :alt="course.title" />
                            <div v-else class="course-card-image-placeholder">
                                <i class="feather-book-open"></i>
                            </div>
                        </div>
                        <div class="course-card-content">
                            <div class="course-card-header">
                                <h4 class="course-card-title">{{ course.title }}</h4>
                                <button @click="removeCourse(course.id)" class="btn-remove-course" type="button" title="Eliminar curso">
                                    <i class="feather-x"></i>
                                </button>
                            </div>
                            <div class="course-card-meta">
                                <span v-if="course.category" class="course-card-category">
                                    <i class="feather-tag"></i>
                                    {{ course.category.name }}
                                </span>
                                <span class="course-card-price">
                                    <i class="feather-dollar-sign"></i>
                                    ${{ course.price.toLocaleString() }} COP
                                </span>
                            </div>
                            <p v-if="course.short_description" class="course-card-description">
                                {{ course.short_description }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="selectedCourses.length === 0" class="no-courses-message">
                    <div class="no-courses-icon">
                        <i class="feather-book"></i>
                    </div>
                    <div class="no-courses-text">
                        <span class="no-courses-title">Sin cursos seleccionados</span>
                        <span class="no-courses-subtitle">Haz clic en "Agregar Curso" para matricular al usuario automáticamente</span>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="footer-buttons-modal">
                <Button :disabled="saving" @click="close" variant="outline" size="sm" class="btn-cancel-modal">
                    <i class="feather-x"></i> Cancelar
                </Button>
                <Button :loading="saving" @click="save" size="sm" class="btn-save-modal"> <i class="feather-check"></i> Crear Usuario </Button>
            </div>
        </template>
    </Modal>

    <AppSelectCourses
        :open="isOpenSelectCourses"
        :user_id="0"
        :exclude_courses_ids="selectedCourses.map((c) => c.id)"
        @update:open="openSelectCourses"
        @onSelect="onSelectedCourse"
    />
</template>

<style scoped>
.create-user-modal-body {
    padding: 10px 0;
}

.form-section {
    margin-bottom: 20px;
}

.form-section:last-child {
    margin-bottom: 0;
}

.section-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #133a54;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 8px;
    border-bottom: 2px solid rgba(19, 58, 84, 0.2);
}

.section-label i {
    font-size: 14px;
}

.btn-add-course {
    width: 100%;
    margin-bottom: 20px;
    padding: 16px 20px !important;
    height: auto !important;
    border: 4px solid #d66800 !important;
    background: linear-gradient(135deg, #133a54 0%, #ff9933 100%) !important;
    transition: all 0.3s ease !important;
    color: white !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 15px rgba(19, 58, 84, 0.25) !important;
}

.btn-add-course:hover {
    border-color: #a85400 !important;
    background: linear-gradient(135deg, #d66800 0%, #133a54 100%) !important;
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(19, 58, 84, 0.45) !important;
}

.btn-add-course-content {
    display: flex;
    align-items: center;
    gap: 16px;
    width: 100%;
}

.btn-add-course-content i {
    font-size: 32px;
    color: white;
}

.btn-add-course-text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    text-align: left;
}

.btn-add-course-title {
    font-size: 15px;
    font-weight: 700;
    color: white;
}

.btn-add-course-subtitle {
    font-size: 12px;
    font-weight: 400;
    color: rgba(255, 255, 255, 0.9);
}

.selected-courses-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.courses-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: linear-gradient(135deg, #133a54 0%, #ff9933 100%);
    border-radius: 8px;
    margin-bottom: 8px;
}

.courses-count {
    display: flex;
    align-items: center;
    gap: 8px;
    color: white;
    font-weight: 600;
    font-size: 14px;
}

.courses-count i {
    font-size: 16px;
}

.courses-total {
    color: white;
    font-weight: 700;
    font-size: 16px;
}

.course-card {
    display: flex;
    gap: 16px;
    padding: 16px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.course-card:hover {
    border-color: #133a54;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.15);
    transform: translateY(-2px);
}

.course-card-image {
    flex-shrink: 0;
    width: 120px;
    height: 90px;
    border-radius: 8px;
    overflow: hidden;
}

.course-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.course-card-image-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.course-card-image-placeholder i {
    font-size: 32px;
    color: #adb5bd;
}

.course-card-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.course-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
}

.course-card-title {
    flex: 1;
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #333;
    line-height: 1.4;
}

.course-card-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.course-card-category {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: #e7f3ff;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #0056b3;
}

.course-card-category i {
    font-size: 12px;
}

.course-card-price {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
    font-weight: 700;
    color: #28a745;
}

.course-card-price i {
    font-size: 14px;
}

.course-card-description {
    margin: 0;
    font-size: 13px;
    color: #666;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.btn-remove-course {
    background: transparent;
    border: none;
    color: #dc3545;
    cursor: pointer;
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.btn-remove-course:hover {
    background: #dc3545;
    color: white;
    transform: scale(1.1);
}

.btn-remove-course i {
    font-size: 18px;
}

.no-courses-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 32px 20px;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    text-align: center;
}

.no-courses-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #e7f3ff 0%, #cce5ff 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.no-courses-icon i {
    font-size: 28px;
    color: #0056b3;
}

.no-courses-text {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.no-courses-title {
    font-size: 15px;
    font-weight: 700;
    color: #333;
}

.no-courses-subtitle {
    font-size: 13px;
    color: #666;
    max-width: 400px;
}

.footer-buttons-modal {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    width: 100%;
}

.btn-cancel-modal {
    background: #ffffff !important;
    color: #666 !important;
    border: 2px solid #ddd !important;
    padding: 10px 20px !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    transition: all 0.3s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.btn-cancel-modal:hover {
    background: #f5f5f5 !important;
    border-color: #999 !important;
    color: #333 !important;
}

.btn-save-modal {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    border: 2px solid #133a54 !important;
    padding: 10px 24px !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.25) !important;
    transition: all 0.3s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.btn-save-modal:hover {
    background: linear-gradient(135deg, #1a5a80 0%, #133a54 100%) !important;
    box-shadow: 0 6px 16px rgba(19, 58, 84, 0.35) !important;
    transform: translateY(-2px);
}

.btn-save-modal i,
.btn-cancel-modal i {
    font-size: 16px;
}
</style>
