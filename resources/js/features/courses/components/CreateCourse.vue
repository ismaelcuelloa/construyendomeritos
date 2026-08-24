<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input, InputError } from '@/components/ui/input';
import Modal from '@/components/ui/modal/Modal.vue';
import SelectCategories from '@/features/catalog/components/selects/SelectCategories.vue';
import SelectSubcategories from '@/features/catalog/components/selects/SelectSubcategories.vue';
import TextArea from '@/components/ui/text-area/TextArea.vue';
import { Client } from '@/lib/client';
import { computed, ref, watch } from 'vue';

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

const code = ref('');
const grado = ref('');
const saving = ref(false);
const tittle = ref('');
const description = ref('');
const price = ref('0');
const category = ref('');
const subcategory = ref('');
const showSubcategorySelector = ref(false);

const courseCodes = ref<string[]>([]);
const newCode = ref('');

const description_code = ref('');
const description_grado = ref('');

const description_title = ref('');
const description_description = ref('');
const description_price = ref('');
const description_category = ref('');

const price_formatted = computed(() => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(price.value));
});

const save = async () => {
    if (validate()) {
        saving.value = true;

        try {
            const params = {
                code: courseCodes.value[0] || null,
                grado: grado.value || null,
                title: tittle.value,
                description: description.value,
                price: price.value,
                category_id: category.value,
                subcategory_id: subcategory.value || null,
                codes: courseCodes.value,
            };
            const response = await Client.post(Client.ADMIN_COURSES, params);
            emit('onSave', response.data.course);
            close();
            reset();
        } catch (e) {
            console.log(e);
        }

        saving.value = false;
    }
};

const validate = () => {
    let validate = true;
    resetDescriptionsFields();

    if (category.value == '') {
        description_category.value = 'La categoría es requerida';
        validate = false;
    }

    if (tittle.value.trim() == '') {
        description_title.value = 'El campo es requerido';
        validate = false;
    }

    if (price.value === '') {
        description_price.value = 'El campo es requerido';
        validate = false;
    }

    return validate;
};

const reset = () => {
    console.log('reset');
    if (!saving.value) {
        resetFields();
        resetDescriptionsFields();
    }
};

const resetFields = () => {
    code.value = '';
    grado.value = '';
    tittle.value = '';
    description.value = '';
    price.value = '';
    category.value = '';
    subcategory.value = '';
    showSubcategorySelector.value = false;
    courseCodes.value = [];
    newCode.value = '';
};
const resetDescriptionsFields = () => {
    description_code.value = '';
    description_grado.value = '';
    description_title.value = '';
    description_description.value = '';
    description_price.value = '';
    description_category.value = '';
};

const addCode = () => {
    const trimmed = newCode.value.trim();
    if (trimmed && !courseCodes.value.includes(trimmed)) {
        courseCodes.value.push(trimmed);
    }
    newCode.value = '';
};

const removeCode = (c: string) => {
    courseCodes.value = courseCodes.value.filter(item => item !== c);
};

watch(category, async (newCategoryId) => {
    subcategory.value = '';
    if (newCategoryId) {
        try {
            const response = await Client.get(`${Client.ADMIN_CATEGORIES}/${newCategoryId}`);
            showSubcategorySelector.value = response.data?.category?.enable_subcategories ?? false;
        } catch {
            showSubcategorySelector.value = false;
        }
    } else {
        showSubcategorySelector.value = false;
    }
});
</script>

<template>
    <Modal :show="isOpen" @update:show="(val) => (isOpen = val)" title="Nuevo Material de Estudio" size="lg">
        <div class="create-course-modal-body">
            <SelectCategories v-model="category" class="mb-4">
                <template v-if="description_category.trim() != ''" #description>
                    <InputError :text="description_category" />
                </template>
            </SelectCategories>

            <SelectSubcategories v-if="showSubcategorySelector" :category-id="category" v-model="subcategory" class="mb-4" />

            <div class="course-field mb-4">
                <label class="field-label">Códigos de Convocatoria</label>
                <div class="codes-list">
                    <div v-for="(c, idx) in courseCodes" :key="idx" class="code-tag">
                        <span>{{ c }}</span>
                        <button type="button" class="code-tag-remove" @click="removeCode(c)" :disabled="saving">&times;</button>
                    </div>
                    <div v-if="courseCodes.length === 0" class="codes-empty">
                        No hay códigos adicionales.
                    </div>
                </div>
                <div class="codes-add-row mt-2">
                    <input
                        :disabled="saving"
                        type="text"
                        v-model="newCode"
                        class="codes-input"
                        placeholder="Ej: 108-2026"
                        @keyup.enter="addCode"
                    />
                    <Button :disabled="saving || !newCode.trim()" @click="addCode" size="sm" class="btn-add-code-modal">
                        Agregar
                    </Button>
                </div>
                <small class="info-text-modal d-block mt-2">
                    <i class="feather-info"></i>
                    Agregue todos los códigos de convocatoria asociados a este curso.
                </small>
            </div>

            <Input :disabled="saving" title="Código y Grado" v-model="grado" placeholder="Ej: 3PU-15">
                <template v-if="description_grado.trim() != ''" #description>
                    <InputError :text="description_grado" />
                </template>
            </Input>

            <Input :disabled="saving" title="Titulo" v-model="tittle">
                <template v-if="description_title.trim() != ''" #description>
                    <InputError :text="description_title" />
                </template>
            </Input>

            <TextArea :disabled="saving" title="Descripcion" v-model="description">
                <template #description>
                    <small class="info-text-modal d-block mt-2">
                        <i class="feather-info"></i>
                        Se permite solo texto sin formato, no emojis. Este campo se utiliza para búsquedas, así que por favor, sea descriptivo.
                    </small>
                </template>
            </TextArea>

            <Input :disabled="saving" type="number" title="Precio" v-model="price">
                <template v-if="description_price.trim() != '' && price == ''" #description>
                    <InputError :text="description_price" />
                </template>
                <template v-else #description>
                    <small class="price-formatted-modal d-block mt-2">
                        {{ price_formatted }}
                    </small>
                </template>
            </Input>
        </div>

        <template #footer>
            <div class="footer-buttons-modal">
                <Button :disabled="saving" @click="close" variant="outline" size="sm" class="btn-cancel-modal">
                    <i class="feather-x"></i> Cancelar
                </Button>
                <Button :loading="saving" @click="save" size="sm" class="btn-save-modal"> <i class="feather-save"></i> Guardar </Button>
            </div>
        </template>
    </Modal>
</template>

<style scoped>
.create-course-modal-body {
    padding: 10px 0;
}

.info-text-modal {
    color: #666 !important;
    font-size: 13px !important;
    display: flex;
    align-items: flex-start;
    gap: 6px;
    padding: 10px 12px;
    background: rgba(19, 58, 84, 0.05);
    border-radius: 8px;
    border-left: 3px solid #133a54;
    line-height: 1.5;
}

.info-text-modal i {
    color: #133a54;
    font-size: 16px;
    margin-top: 2px;
    flex-shrink: 0;
}

.price-formatted-modal {
    color: #133a54 !important;
    font-weight: 700 !important;
    font-size: 16px !important;
    padding: 8px 14px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.1) 0%, rgba(26, 90, 128, 0.05) 100%);
    border-radius: 8px;
    display: inline-block;
    border: 2px solid rgba(19, 58, 84, 0.2);
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

.course-field {
    margin-bottom: 16px;
}

.field-label {
    font-size: 13px;
    font-weight: 700;
    color: #1a1a1a;
    display: block;
    margin-bottom: 8px;
}

.codes-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    min-height: 36px;
}

.code-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #e8f0fe;
    border: 1px solid #c4d7f2;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #11364f;
}

.code-tag-remove {
    background: none;
    border: none;
    color: #dc2626;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    padding: 0 2px;
}

.code-tag-remove:hover {
    color: #b91c1c;
}

.codes-empty {
    color: #94a3b8;
    font-size: 13px;
    font-style: italic;
    padding: 4px 0;
}

.codes-add-row {
    display: flex;
    gap: 8px;
    align-items: center;
}

.codes-input {
    flex: 1;
    padding: 8px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    color: #11364f;
    outline: none;
    transition: border-color 0.2s ease;
}

.codes-input:focus {
    border-color: #133a54;
    box-shadow: 0 0 0 2px rgba(19, 58, 84, 0.1);
}

.btn-add-code-modal {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%) !important;
    color: #ffffff !important;
    border: none !important;
    padding: 8px 16px !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    font-size: 13px !important;
}

.btn-add-code-modal:hover {
    background: linear-gradient(135deg, #1a5a80 0%, #133a54 100%) !important;
}
</style>
