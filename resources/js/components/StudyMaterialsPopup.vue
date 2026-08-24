<script setup lang="ts">
import { onMounted, ref } from 'vue';

const showPopup = ref(false);
const isVisible = ref(false);

const closePopup = () => {
    isVisible.value = false;
    setTimeout(() => {
        showPopup.value = false;
        sessionStorage.setItem('studyMaterialsPopupShown', 'true');
    }, 300);
};

onMounted(() => {
    const hasShown = sessionStorage.getItem('studyMaterialsPopupShown');

    if (!hasShown) {
        showPopup.value = true;
        setTimeout(() => {
            isVisible.value = true;
        }, 500);
    }
});
</script>

<template>
    <Transition name="popup">
        <div v-if="showPopup" class="popup-overlay" :class="{ active: isVisible }" @click="closePopup">
            <div class="popup-card" :class="{ active: isVisible }" @click.stop>
                <button @click="closePopup" class="popup-close">
                    <i class="feather-x"></i>
                </button>

                <div class="popup-icon-wrapper">
                    <img src="/assets/images/logo/logo-color.png" alt="Logo" class="popup-logo" />
                </div>

                <div class="popup-content">
                    <p class="popup-message">
                        Proximamente encontraras los materiales de estudio en nuestra plataforma.
                    </p>
                    <p class="popup-submessage">
                        Estamos trabajando para ofrecerte la mejor experiencia de aprendizaje.
                    </p>
                </div>

                <div class="popup-features">
                    <div class="feature">
                        <i class="feather-check-circle"></i>
                        <span>Guias actualizadas</span>
                    </div>
                    <div class="feature">
                        <i class="feather-check-circle"></i>
                        <span>Simulacros reales</span>
                    </div>
                    <div class="feature">
                        <i class="feather-check-circle"></i>
                        <span>Acceso 24/7</span>
                    </div>
                </div>

                <button @click="closePopup" class="popup-btn">
                    Entendido
                    <i class="feather-arrow-right"></i>
                </button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.35s ease;
}

.popup-overlay.active {
    opacity: 1;
}

.popup-card {
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 32px;
    padding: 55px 60px 50px;
    max-width: 720px;
    width: 100%;
    position: relative;
    transform: translateY(40px) scale(0.92);
    transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow:
        0 25px 80px rgba(0, 0, 0, 0.25),
        0 0 0 1px rgba(19, 58, 84, 0.08);
    overflow: hidden;
}

.popup-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #133a54 0%, #1a5276 30%, #f5e42c 70%, #f7d72e 100%);
}

.popup-card.active {
    transform: translateY(0) scale(1);
}

.popup-close {
    position: absolute;
    top: 18px;
    right: 18px;
    background: rgba(0, 0, 0, 0.04);
    border: none;
    color: #94a3b8;
    cursor: pointer;
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
    width: 40px;
    height: 40px;
    z-index: 2;
}

.popup-close:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    transform: rotate(90deg);
}

.popup-close i {
    font-size: 22px;
}

.popup-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
}

.popup-logo {
    height: 80px;
    width: auto;
}

.popup-content {
    text-align: center;
    margin-bottom: 32px;
}

.popup-message {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 12px 0;
    line-height: 1.5;
}

.popup-submessage {
    font-size: 17px;
    color: #64748b;
    margin: 0;
    line-height: 1.6;
}

.popup-features {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    margin-bottom: 35px;
    padding: 24px;
    background: linear-gradient(135deg, rgba(19, 58, 84, 0.03) 0%, rgba(245, 228, 44, 0.03) 100%);
    border-radius: 16px;
    border: 1px solid rgba(19, 58, 84, 0.06);
}

.feature {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #334155;
    font-size: 15px;
    font-weight: 600;
}

.feature i {
    color: #22c55e;
    font-size: 18px;
}

.popup-btn {
    width: 100%;
    padding: 18px 24px;
    background: linear-gradient(135deg, #133a54 0%, #0f2d42 100%);
    color: white;
    border: none;
    border-radius: 16px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(19, 58, 84, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.popup-btn i {
    font-size: 20px;
    transition: transform 0.3s ease;
}

.popup-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 35px rgba(19, 58, 84, 0.4);
    background: linear-gradient(135deg, #1a4d6e 0%, #133a54 100%);
}

.popup-btn:hover i {
    transform: translateX(5px);
}

.popup-enter-active,
.popup-leave-active {
    transition: opacity 0.3s ease;
}

.popup-enter-from,
.popup-leave-to {
    opacity: 0;
}

@media (max-width: 576px) {
    .popup-card {
        padding: 40px 24px 35px;
        border-radius: 24px;
    }

    .popup-icon-wrapper {
        margin-bottom: 24px;
    }

    .popup-logo {
        height: 60px;
    }

    .popup-icon-ring {
        inset: -6px;
    }

    .popup-message {
        font-size: 18px;
    }

    .popup-submessage {
        font-size: 15px;
    }

    .popup-features {
        gap: 14px;
        padding: 18px;
        margin-bottom: 28px;
    }

    .feature {
        font-size: 14px;
    }

    .popup-btn {
        font-size: 16px;
        padding: 15px 20px;
    }
}
</style>
