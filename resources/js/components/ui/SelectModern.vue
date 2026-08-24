<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { ChevronDown, Check } from 'lucide-vue-next';

interface SelectOption {
    value: number | string;
    text: string;
    icon?: string;
}

interface Props {
    modelValue: number | string;
    options: SelectOption[];
    label?: string;
    disabled?: boolean;
}

const props = defineProps<Props>();
const emits = defineEmits<{
    (e: 'update:modelValue', value: number | string): void;
}>();

const isOpen = ref(false);
const selectRef = ref<HTMLDivElement>();

const selectedOption = computed(() => {
    return props.options.find(opt => opt.value === props.modelValue);
});

const selectOption = (value: number | string) => {
    if (!props.disabled) {
        emits('update:modelValue', value);
        isOpen.value = false;
    }
};

const toggleDropdown = () => {
    if (!props.disabled) {
        isOpen.value = !isOpen.value;
    }
};

// Cerrar dropdown cuando se hace clic fuera
const handleClickOutside = (event: MouseEvent) => {
    if (selectRef.value && !selectRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

</script>

<template>
    <div class="select-modern-wrapper" ref="selectRef">
        <label v-if="label" class="select-modern-label">{{ label }}</label>
        
        <div 
            class="select-modern-trigger" 
            :class="{ 'is-open': isOpen, 'is-disabled': disabled }"
            @click="toggleDropdown"
        >
            <div class="select-modern-value">
                <span v-if="selectedOption">{{ selectedOption.text }}</span>
                <span v-else class="placeholder">Seleccionar...</span>
            </div>
            <ChevronDown 
                class="select-modern-icon" 
                :class="{ 'rotate': isOpen }" 
                :size="20" 
            />
        </div>

        <Transition name="dropdown">
            <div v-if="isOpen" class="select-modern-dropdown">
                <div 
                    v-for="option in options" 
                    :key="option.value"
                    class="select-modern-option"
                    :class="{ 'is-selected': option.value === modelValue }"
                    @click="selectOption(option.value)"
                >
                    <span class="option-text">{{ option.text }}</span>
                    <Check 
                        v-if="option.value === modelValue" 
                        class="check-icon" 
                        :size="18" 
                    />
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.select-modern-wrapper {
    position: relative;
    width: 100%;
}

.select-modern-label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-heading);
    margin-bottom: 10px;
    letter-spacing: 0.3px;
}

.select-modern-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    min-height: 52px;
}

.select-modern-trigger:hover:not(.is-disabled) {
    border-color: #133a54;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.1);
    transform: translateY(-1px);
}

.select-modern-trigger.is-open {
    border-color: #133a54;
    background: #ffffff;
    box-shadow: 0 6px 16px rgba(19, 58, 84, 0.15);
}

.select-modern-trigger.is-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: #f3f4f6;
}

.select-modern-value {
    flex: 1;
    font-size: 15px;
    font-weight: 500;
    color: var(--color-heading);
}

.placeholder {
    color: #9ca3af;
}

.select-modern-icon {
    color: #6b7280;
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
}

.select-modern-icon.rotate {
    transform: rotate(180deg);
}

.select-modern-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    z-index: 1000;
    max-height: 320px;
    overflow-y: auto;
    padding: 6px;
}

.select-modern-dropdown::-webkit-scrollbar {
    width: 6px;
}

.select-modern-dropdown::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 10px;
}

.select-modern-dropdown::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 10px;
}

.select-modern-dropdown::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

.select-modern-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.2s ease;
    font-size: 15px;
    font-weight: 500;
    color: var(--color-body);
    margin-bottom: 2px;
}

.select-modern-option:last-child {
    margin-bottom: 0;
}

.select-modern-option:hover {
    background: linear-gradient(135deg, #FFF5EB 0%, #FFE8D6 100%);
    color: #133a54;
    transform: translateX(4px);
}

.select-modern-option.is-selected {
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: #ffffff;
    font-weight: 600;
}

.select-modern-option.is-selected:hover {
    background: linear-gradient(135deg, #D96D00 0%, #133a54 100%);
    transform: translateX(0);
}

.option-text {
    flex: 1;
}

.check-icon {
    flex-shrink: 0;
    color: currentColor;
}

/* Animaciones */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-enter-from {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
}

.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
}

.dropdown-enter-to,
.dropdown-leave-from {
    opacity: 1;
    transform: translateY(0) scale(1);
}

/* Responsive */
@media (max-width: 768px) {
    .select-modern-trigger {
        padding: 12px 16px;
        min-height: 48px;
    }

    .select-modern-value {
        font-size: 14px;
    }

    .select-modern-option {
        padding: 11px 13px;
        font-size: 14px;
    }

    .select-modern-dropdown {
        max-height: 260px;
    }
}
</style>
