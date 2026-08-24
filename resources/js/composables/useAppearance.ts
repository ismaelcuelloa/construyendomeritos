import { onMounted, ref } from 'vue';

type Appearance = 'light';

export function updateTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Siempre modo claro
    document.documentElement.classList.remove('dark');
}

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Siempre modo claro
    updateTheme();
}

const appearance = ref<Appearance>('light');

export function useAppearance() {
    onMounted(() => {
        // Siempre modo claro
        appearance.value = 'light';
    });

    function updateAppearance() {
        // Siempre modo claro, ignorar cambios
        appearance.value = 'light';
        updateTheme();
    }

    return {
        appearance,
        updateAppearance,
    };
}
