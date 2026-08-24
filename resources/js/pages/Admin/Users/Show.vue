<script setup lang="ts">
import { Accordion, AccordionItem } from '@/components/ui/accordion';
import Link from '@/components/ui/link/Link.vue';
import AppAdminLayout from '@/layouts/AppAdminLayout.vue';
// @ts-expect-error - Vue3Datatable does not have TypeScript declarations
import Vue3Datatable from '@bhplugin/vue3-datatable';

import AppSelectCourses from '@/features/courses/components/AppSelectCourses.vue';
import { Button } from '@/components/ui/button';
import { Input, InputError } from '@/components/ui/input';
import Confirmation from '@/components/ui/modal/Confirmation.vue';
import SelectRoles from '@/features/admin/components/selects/SelectRoles.vue';
import Toast from '@/composables/toast';
import { Client } from '@/lib/client';
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import * as Subscriptions from '@/features/users/stores/user';

const props = defineProps<{
    user: any;
}>();

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

// Verificar si el usuario autenticado es super_user
const isSuperUser = computed(() => {
    return authUser.value?.roles?.some((role: any) => role.name === 'super_user');
});

const isOpenSelectCourses = ref(false);
const addingCourse = ref(false);
const showDeleteConfirmation = ref(false);
const itemToDelete = ref<{ id: string; type: 'subscription' | 'order' } | null>(null);

const saving = ref(false);
const names = ref(props.user.name);
const email = ref(props.user.email);
const role = ref(props.user.roles[0].name);
const newPassword = ref('');

const description_names = ref('');
const description_email = ref('');
const description_role = ref('');
const description_password = ref('');

const title = 'Usuarios';

const deleteConfirmationTitle = computed(() => {
    if (!itemToDelete.value) return 'Eliminar';
    return itemToDelete.value.type === 'order' ? 'Eliminar Compra WATI' : 'Eliminar Suscripción';
});

const deleteConfirmationMessage = computed(() => {
    if (!itemToDelete.value) return '';
    return itemToDelete.value.type === 'order'
        ? '¿Estás seguro de que deseas eliminar esta compra WATI? El usuario perderá acceso inmediato al material de estudio.'
        : '¿Estás seguro de que deseas eliminar esta suscripción? Esta acción no se puede deshacer y el usuario perderá acceso inmediato al material de estudio.';
});

const save = async () => {
    if (validate()) {
        saving.value = true;

        try {
            const params: any = {
                name: names.value,
                email: email.value,
            };

            // Solo incluir el rol si es super_user
            if (isSuperUser.value) {
                params.role = role.value;

                // Solo incluir la contraseña si se proporciona
                if (newPassword.value.trim() !== '') {
                    params.password = newPassword.value;
                }
            }

            await Client.put(`${Client.ADMIN_USERS}/${props.user.id}`, params);
            Toast.success('Usuario actualizado con exito');

            // Limpiar el campo de contraseña después de guardar
            newPassword.value = '';
        } catch (e) {
            Toast.error('Error al actualizar el usuario');
            console.log(e);
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

    // Solo validar rol si es super_user
    if (isSuperUser.value && role.value.trim() === '') {
        description_role.value = 'El campo es requerido';
        validate = false;
    }

    // Validar contraseña si se proporciona (mínimo 8 caracteres)
    if (isSuperUser.value && newPassword.value.trim() !== '' && newPassword.value.length < 8) {
        description_password.value = 'La contraseña debe tener al menos 8 caracteres';
        validate = false;
    }

    return validate;
};

const resetDescriptionsFields = () => {
    description_names.value = '';
    description_email.value = '';
    description_role.value = '';
    description_password.value = '';
};

const openSelectCourses = (value: boolean = true) => {
    isOpenSelectCourses.value = value;
};

const onSelectedCourses = async (course: any) => {
    addingCourse.value = true;
    try {
        const params = {
            user_id: props.user.id,
            course_id: course.id,
        };

        await Client.post(`${Client.ADMIN_SUBSCRIPCIONS}`, params);
        Subscriptions.getCourses();

        Toast.success('Suscripcion exitosa!');
    } catch (e) {
        Toast.error('Error al suscribir el curso.');
        console.log(e);
    }

    addingCourse.value = false;
};

const deleteItem = (itemId: string, isOrder: boolean) => {
    itemToDelete.value = {
        id: itemId,
        type: isOrder ? 'order' : 'subscription',
    };
    showDeleteConfirmation.value = true;
};

const confirmDeleteSubscription = async () => {
    if (!itemToDelete.value) return;

    try {
        let success = false;
        
        if (itemToDelete.value.type === 'subscription') {
            success = await Subscriptions.deleteSubscription(itemToDelete.value.id);
            if (success) {
                Toast.success('Suscripción eliminada con éxito');
            }
        } else {
            success = await Subscriptions.deleteOrder(itemToDelete.value.id);
            if (success) {
                Toast.success('Compra eliminada con éxito');
            }
        }

        if (!success) {
            Toast.error('Error al eliminar');
        }
    } catch (e) {
        Toast.error('Error al eliminar');
        console.log(e);
    }

    itemToDelete.value = null;
    showDeleteConfirmation.value = false;
};

onMounted(() => {
    Subscriptions.userOrders.value = props.user.orders || [];
    Subscriptions.getCourses();
});

watch(
    props.user,
    (value: any) => {
        Subscriptions.userID.value = value.id;
        Subscriptions.userOrders.value = value.orders || [];
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <AppAdminLayout :title="title">
        <Accordion>
            <AccordionItem id="1" :open="true" title="Información de Usuario">
                <div class="row">
                    <div class="col-xs-12 col-md-1 col-lg-2 col-xl-2 col-12"></div>
                    <div class="col-xs-12 col-md-10 col-lg-8 col-xl-8 col-12">
                        <Input :disabled="saving" title="Nombres" v-model="names">
                            <template v-if="description_names.trim() != ''" #description>
                                <InputError :text="description_names" />
                            </template>
                        </Input>

                        <Input :disabled="saving" title="Correo" v-model="email">
                            <template v-if="description_email.trim() != ''" #description>
                                <InputError :text="description_email" />
                            </template>
                        </Input>

                        <SelectRoles v-if="isSuperUser" v-model="role">
                            <template v-if="description_role.trim() != ''" #description>
                                <InputError :text="description_role" />
                            </template>
                        </SelectRoles>

                        <div v-else class="role-readonly">
                            <label class="role-label">Rol</label>
                            <div class="role-value">{{ props.user.roles[0]?.description || 'N/A' }}</div>
                            <p class="role-note">Solo super usuarios pueden cambiar roles</p>
                        </div>

                        <Input
                            v-if="isSuperUser"
                            :disabled="saving"
                            title="Nueva Contraseña"
                            type="password"
                            v-model="newPassword"
                            placeholder="Dejar en blanco para no cambiar"
                        >
                            <template v-if="description_password.trim() != ''" #description>
                                <InputError :text="description_password" />
                            </template>
                        </Input>

                        <div class="mt-5">
                            <Button :loading="saving" @click="save" size="sm" class="w-fit">Guardar</Button>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-1 col-lg-2 col-xl-2 col-12"></div>
                </div>
            </AccordionItem>

            <AccordionItem id="2" :open="true" title="Cursos suscritos">
                <template #header>
                    <Button :loading="addingCourse" variant="outline" @click="openSelectCourses" class="w-fit">Suscribir Curso</Button>
                </template>

                <div class="mt-4">
                    <vue3-datatable
                        :ref="Subscriptions.table.table"
                        :loading="Subscriptions.table.loading"
                        :rows="Subscriptions.table.rows.value"
                        :columns="Subscriptions.table.cols"
                        :totalRows="Subscriptions.table.total_rows.value"
                        :isServerMode="true"
                        :page="Subscriptions.table.params.current_page"
                        :pageSize="Subscriptions.table.params.pagesize"
                        :showPageSize="false"
                        :sortable="true"
                        :sortColumn="Subscriptions.table.params.sort_column"
                        :sortDirection="Subscriptions.table.params.sort_direction"
                        :search="Subscriptions.table.params.search"
                        :hasCheckbox="false"
                        :columnFilter="false"
                        noDataContent="No cursos encontrados"
                        @change="Subscriptions.changeServer"
                        skin="bh-table-striped bh-table-hover"
                    >
                        <template #title="data: any">
                            {{ data.value.course.title }}
                        </template>

                        <template #source="data: any">
                            <span :class="
                                data.value.is_expired ? 'badge bg-danger' :
                                data.value.is_demo ? 'badge bg-warning' :
                                data.value.is_order ? 'badge bg-success' : 'badge bg-primary'
                            ">
                                {{ data.value.source || 'Suscripción' }}
                            </span>
                        </template>

                        <template #options="data: any">
                            <div class="rbt-button-group justify-content-end">
                                <Link :href="Subscriptions.goToCourse(data.value.course_id)"><i class="feather-eye"></i> Ver</Link>
                                <button 
                                    @click="deleteItem(data.value.id, data.value.is_order)" 
                                    class="btn-delete-subscription"
                                    :title="data.value.is_order ? 'Eliminar compra WATI' : 'Eliminar suscripción'"
                                >
                                    <i class="feather-trash-2"></i> Eliminar
                                </button>
                            </div>
                        </template>
                    </vue3-datatable>
                </div>
            </AccordionItem>
        </Accordion>
    </AppAdminLayout>
    <AppSelectCourses @update:open="openSelectCourses" :open="isOpenSelectCourses" @onSelect="onSelectedCourses" :user_id="props.user.id" />
    <Confirmation
        :show="showDeleteConfirmation"
        @update:show="(val) => (showDeleteConfirmation = val)"
        @yes="confirmDeleteSubscription"
        :title="deleteConfirmationTitle"
        :message="deleteConfirmationMessage"
        textYes="Sí, eliminar"
        textNo="Cancelar"
    />
</template>

<style scoped>
/* Acordeón Premium */
:deep(.accordion) {
    gap: 12px;
}

:deep(.accordion-item) {
    border: 1.5px solid rgba(19, 58, 84, 0.15) !important;
    border-radius: 12px !important;
    overflow: hidden !important;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.08) !important;
    background: #ffffff !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

:deep(.accordion-item:hover) {
    border-color: rgba(19, 58, 84, 0.25) !important;
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.12) !important;
}

:deep(.accordion-button) {
    background: linear-gradient(180deg, rgba(19, 58, 84, 0.08) 0%, rgba(19, 58, 84, 0.03) 100%) !important;
    color: #151515 !important;
    font-weight: 800 !important;
    font-size: 15px !important;
    letter-spacing: -0.5px !important;
    padding: 18px 24px !important;
    border-bottom: 1px solid rgba(19, 58, 84, 0.1) !important;
    transition: all 0.3s ease !important;
}

:deep(.accordion-button:hover:not(.collapsed)) {
    background: linear-gradient(180deg, rgba(19, 58, 84, 0.12) 0%, rgba(19, 58, 84, 0.08) 100%) !important;
}

:deep(.accordion-button::after) {
    content: '' !important;
    position: absolute !important;
    width: 32px !important;
    height: 32px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.15) 0%, rgba(19, 58, 84, 0.08) 100%) !important;
    border: 1.5px solid rgba(19, 58, 84, 0.25) !important;
    right: 24px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    background-image: none !important;
}

:deep(.accordion-body) {
    padding: 24px !important;
    background: #ffffff !important;
}

/* Inputs Premium */
:deep(.input-wrapper) {
    margin-bottom: 20px;
}

:deep(.input-label) {
    color: #151515 !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    letter-spacing: 0.5px !important;
    margin-bottom: 8px !important;
    text-transform: uppercase;
}

:deep(.input-field) {
    border: 1.5px solid #e0e0e0 !important;
    border-radius: 8px !important;
    padding: 12px 16px !important;
    font-size: 14px !important;
    transition: all 0.3s ease !important;
    background: #ffffff !important;
}

:deep(.input-field:focus) {
    border-color: #133a54 !important;
    box-shadow: 0 0 0 3px rgba(19, 58, 84, 0.1) !important;
    outline: none !important;
}

:deep(.input-field:disabled) {
    background: #f5f5f5 !important;
    color: #999 !important;
    cursor: not-allowed !important;
}

/* Select Roles Premium */
:deep(.select-wrapper) {
    margin-bottom: 20px;
}

:deep(.select-trigger) {
    border: 1.5px solid #e0e0e0 !important;
    border-radius: 8px !important;
    padding: 12px 16px !important;
    font-size: 14px !important;
    transition: all 0.3s ease !important;
    background: #ffffff !important;
}

:deep(.select-trigger:focus) {
    border-color: #133a54 !important;
    box-shadow: 0 0 0 3px rgba(19, 58, 84, 0.1) !important;
}

/* Botones Premium */
:deep([data-slot='button'].w-fit) {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    border: none !important;
    font-weight: 700 !important;
    padding: 12px 28px !important;
    border-radius: 8px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.25) !important;
    position: relative !important;
    overflow: visible !important;
    font-size: 14px !important;
    letter-spacing: 0.5px !important;
    min-height: 44px !important;
}

:deep([data-slot='button'].w-fit::before) {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.6s ease;
    z-index: 1;
    border-radius: 8px !important;
}

:deep([data-slot='button'].w-fit:hover::before) {
    left: 100%;
}

:deep([data-slot='button'].w-fit:hover) {
    transform: translateY(-2px) !important;
    box-shadow: 0 12px 30px rgba(19, 58, 84, 0.35) !important;
}

:deep([data-slot='button'].w-fit:active) {
    transform: translateY(0) !important;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.2) !important;
}

/* Tabla de Cursos Premium */
:deep(.bh-table-striped) {
    border-collapse: collapse;
    width: 100%;
}

:deep(.bh-table-striped thead) {
    background: linear-gradient(180deg, rgba(19, 58, 84, 0.08) 0%, rgba(19, 58, 84, 0.03) 100%);
}

:deep(.bh-table-striped thead th) {
    color: #151515 !important;
    font-weight: 800 !important;
    font-size: 13px !important;
    letter-spacing: 0.5px !important;
    padding: 16px 12px !important;
    border-bottom: 2px solid #133a54 !important;
    text-transform: uppercase;
}

:deep(.bh-table-striped tbody tr) {
    border-bottom: 1px solid rgba(19, 58, 84, 0.1) !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

:deep(.bh-table-striped tbody tr:hover) {
    background: rgba(19, 58, 84, 0.05) !important;
    box-shadow: inset 0 0 10px rgba(19, 58, 84, 0.08);
}

:deep(.bh-table-striped tbody td) {
    padding: 16px 12px !important;
    color: #666 !important;
    font-weight: 500;
}

/* Botones de Tabla */
:deep(.rbt-button-group a) {
    color: #133a54 !important;
    text-decoration: none !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    transition: all 0.3s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 8px 12px !important;
    border-radius: 6px !important;
    border: 1px solid transparent !important;
}

:deep(.rbt-button-group a:hover) {
    color: #ffffff !important;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    border-color: #133a54 !important;
    transform: translateX(2px);
}

:deep(.rbt-button-group a i) {
    font-size: 16px !important;
    transition: transform 0.3s ease !important;
}

:deep(.rbt-button-group a:hover i) {
    transform: translateX(2px);
}

:deep(.color-danger) {
    color: #dc3545 !important;
}

:deep(.color-danger:hover) {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    border-color: #dc3545 !important;
    color: #ffffff !important;
}

/* Botón Eliminar Suscripción */
.btn-delete-subscription {
    color: #ef4444 !important;
    background: rgba(239, 68, 68, 0.08) !important;
    border: 1px solid rgba(239, 68, 68, 0.2) !important;
    padding: 8px 12px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1) !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    cursor: pointer;
}

.btn-delete-subscription:hover {
    background: rgba(239, 68, 68, 0.15) !important;
    border-color: rgba(239, 68, 68, 0.4) !important;
    transform: translateY(-1px);
}

.btn-delete-subscription i {
    font-size: 14px;
}

/* Rol de solo lectura */
.role-readonly {
    margin-bottom: 20px;
}

.role-label {
    display: block;
    color: #151515 !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    letter-spacing: 0.5px !important;
    margin-bottom: 8px !important;
    text-transform: uppercase;
}

.role-value {
    border: 1.5px solid #e0e0e0 !important;
    border-radius: 8px !important;
    padding: 12px 16px !important;
    font-size: 14px !important;
    background: #f5f5f5 !important;
    color: #666 !important;
    font-weight: 600;
}

.role-note {
    margin-top: 6px;
    font-size: 12px;
    color: #999;
    font-style: italic;
}
</style>
