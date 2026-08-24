<script setup lang="ts">
import { onMounted, ref } from 'vue';

const showNotification = ref(false);
const whatsappNumber = '+57 312 2372052';
const whatsappUrl = 'https://wa.me/573122372052?text=Hola';

const closeNotification = () => {
    showNotification.value = false;
    localStorage.setItem('welcomeNotificationShown', 'true');
};

onMounted(() => {
    // Verificar si ya se mostró la notificación
    const hasShown = localStorage.getItem('welcomeNotificationShown');

    if (!hasShown) {
        // Mostrar después de 2 segundos
        setTimeout(() => {
            showNotification.value = true;
        }, 2000);
    }
});
</script>

<template>
    <Transition name="notification">
        <div v-if="showNotification" class="welcome-notification-overlay" @click="closeNotification">
            <div class="welcome-notification" @click.stop>
                <button @click="closeNotification" class="close-btn">
                    <i class="feather-x"></i>
                </button>

                <div class="notification-icon">
                    <i class="fab fa-whatsapp"></i>
                </div>

                <div class="notification-content">
                    <h3 class="notification-title">¡Bienvenido!</h3>
                    <p class="notification-message">Para atención y soporte, por favor:</p>

                    <div class="contact-box">
                        <div class="contact-label">
                            <i class="fab fa-whatsapp"></i>
                            WhatsApp
                        </div>
                        <a :href="whatsappUrl" target="_blank" class="whatsapp-link">
                            <i class="fab fa-whatsapp"></i>
                            {{ whatsappNumber }}
                        </a>
                    </div>
                </div>

                <button @click="closeNotification" class="btn-understood">Entendido</button>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.welcome-notification-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
    backdrop-filter: blur(4px);
}

.welcome-notification {
    background: white;
    border-radius: 20px;
    padding: 40px 30px 30px;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
    animation: slideUp 0.5s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: transparent;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
    width: 32px;
    height: 32px;
}

.close-btn:hover {
    background: #f5f5f5;
    color: #333;
}

.close-btn i {
    font-size: 20px;
}

.notification-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    box-shadow: 0 8px 25px rgba(37, 211, 102, 0.4);
}

.notification-icon i {
    font-size: 40px;
    color: white;
}

.notification-content {
    text-align: center;
    margin-bottom: 25px;
}

.notification-title {
    font-size: 28px;
    font-weight: 800;
    color: #333;
    margin: 0 0 15px 0;
}

.notification-message {
    font-size: 16px;
    color: #666;
    margin: 0 0 25px 0;
    line-height: 1.6;
}

.contact-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 20px;
    border: 2px solid #25d366;
}

.contact-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: #25d366;
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.contact-label i {
    font-size: 16px;
}

.whatsapp-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 15px 25px;
    background: linear-gradient(135deg, #25d366 0%, #128c7e 100%);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-size: 18px;
    font-weight: 700;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
}

.whatsapp-link:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(37, 211, 102, 0.5);
    color: white;
}

.whatsapp-link i {
    font-size: 24px;
}

.btn-understood {
    width: 100%;
    padding: 15px;
    background: linear-gradient(135deg, #133a54 0%, #1a5a80 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(19, 58, 84, 0.3);
}

.btn-understood:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(19, 58, 84, 0.4);
}

/* Transiciones */
.notification-enter-active,
.notification-leave-active {
    transition: opacity 0.3s ease;
}

.notification-enter-from,
.notification-leave-to {
    opacity: 0;
}

/* Responsive */
@media (max-width: 576px) {
    .welcome-notification {
        padding: 30px 20px 20px;
    }

    .notification-icon {
        width: 70px;
        height: 70px;
        margin-bottom: 20px;
    }

    .notification-icon i {
        font-size: 35px;
    }

    .notification-title {
        font-size: 24px;
    }

    .notification-message {
        font-size: 14px;
    }

    .whatsapp-link {
        font-size: 16px;
        padding: 12px 20px;
    }

    .contact-box {
        padding: 15px;
    }
}
</style>
