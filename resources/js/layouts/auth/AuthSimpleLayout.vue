<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="auth-container">
        <!-- Fondo decorativo -->
        <div class="auth-background"></div>

        <!-- Botón de regreso al inicio -->
        <Link :href="route('home')" class="back-to-home">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="home-icon">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"
                />
            </svg>
            <span>Inicio</span>
        </Link>

        <!-- Contenido principal -->
        <div class="auth-content">
            <div class="auth-card">
                <!-- Header con logo -->
                <div class="auth-header">
                    <Link :href="route('home')" class="logo-container">
                        <img src="/assets/images/logo/logo-color.png" alt="Construyendo Méritos con Excelencia" class="main-logo" />
                    </Link>
                </div>

                <!-- Título y descripción -->
                <div class="auth-title-section">
                    <h1 class="auth-title">{{ title }}</h1>
                    <p class="auth-description">{{ description }}</p>
                </div>

                <!-- Slot para el formulario -->
                <div class="auth-form-section">
                    <slot />
                </div>
            </div>

            <!-- Footer -->
            <div class="auth-footer">
                <p class="footer-text">© 2025 Construyendo Méritos con Excelencia. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.auth-container {
    display: flex;
    min-height: 100vh;
    min-height: 100dvh;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    background:
        linear-gradient(135deg, rgba(245, 228, 44, 0.3) 0%, rgba(17, 54, 79, 0.15) 100%), url('/assets/images/bg/bg-merito.svg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    padding: 20px 16px;
    position: relative;
    overflow-y: auto;
    overflow-x: hidden;
}

.auth-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background:
        radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
}

.back-to-home {
    position: absolute;
    top: 24px;
    left: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    padding: 10px 16px;
    color: #133a54;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    z-index: 10;
}

.back-to-home:hover {
    background: rgba(255, 255, 255, 1);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.home-icon {
    width: 20px;
    height: 20px;
}

.auth-content {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 480px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin: auto;
}

.auth-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
    padding: 20px 28px;
    transition: all 0.3s ease;
}

.auth-card:hover {
    box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3);
    transform: translateY(-2px);
}

.auth-header {
    display: flex;
    justify-content: center;
    margin-bottom: 12px;
}

.logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
}

.logo-container:hover {
    transform: scale(1.05);
}

.main-logo {
    height: 38px;
    width: auto;
    filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
    transition: all 0.3s ease;
}

.main-logo:hover {
    filter: drop-shadow(0 6px 10px rgba(0, 0, 0, 0.15));
}

.auth-title-section {
    text-align: center;
    margin-bottom: 16px;
}

.auth-title {
    font-size: 22px;
    font-weight: 800;
    color: #133a54;
    margin: 0 0 5px 0;
    letter-spacing: -0.5px;
}

.auth-description {
    font-size: 12px;
    color: #133a54;
    margin: 0;
    line-height: 1.3;
    max-width: 360px;
    margin-left: auto;
    margin-right: auto;
}

.auth-form-section {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.auth-footer {
    text-align: center;
    padding: 0 24px;
}

.footer-text {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    letter-spacing: 0.3px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Responsive Design */
@media (max-width: 640px) {
    .auth-container {
        padding: 12px;
        justify-content: flex-start;
        padding-top: 60px;
    }

    .back-to-home {
        top: 12px;
        left: 12px;
        padding: 8px 12px;
        font-size: 12px;
    }

    .home-icon {
        width: 16px;
        height: 16px;
    }

    .auth-content {
        gap: 16px;
        padding-bottom: 20px;
    }

    .auth-card {
        padding: 20px 16px;
        border-radius: 16px;
    }

    .auth-header {
        margin-bottom: 8px;
    }

    .main-logo {
        height: 32px;
    }

    .auth-title {
        font-size: 20px;
    }

    .auth-title-section {
        margin-bottom: 12px;
    }

    .auth-description {
        font-size: 12px;
    }

    .auth-footer {
        padding: 0 16px;
        margin-bottom: 12px;
    }

    .footer-text {
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .auth-card {
        padding: 16px 12px;
    }

    .auth-title {
        font-size: 18px;
    }
}
</style>
