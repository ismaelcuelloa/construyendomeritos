<script setup lang="ts">
import IndependenceDisclaimer from '@/components/shared/IndependenceDisclaimer.vue';
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';

const currentYear = computed(() => new Date().getFullYear());

onMounted(() => {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px',
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    const elements = document.querySelectorAll('.footer-animate');
    elements.forEach((el) => observer.observe(el));

    onUnmounted(() => {
        elements.forEach((el) => observer.unobserve(el));
    });
});
</script>

<template>
    <footer class="site-footer">
        <div class="footer-main">
            <div class="container">
                <!-- Aviso Legal arriba de Construyendo Méritos con Excelencia -->
                <div class="footer-disclaimer-wrapper footer-disclaimer-wrapper--top">
                    <IndependenceDisclaimer variant="footer" />
                </div>
                <div class="footer-grid">
                    <!-- Logo y descripción -->
                    <div class="footer-col footer-col--brand footer-animate">
                        <a href="/" class="footer-logo">
                            <img src="/assets/images/logo/logo-blanco.png" alt="Construyendo Méritos con Excelencia" />
                        </a>
                        <p class="footer-desc">
                            Construyendo Méritos con Excelencia<br />
                            <strong>Material de preparación independiente</strong> para la Procuraduría General de la Nación 2026. Simulacros, guías y recursos actualizados para alcanzar tu cargo ideal.
                        </p>
                        <div class="footer-social">
                            <a href="https://www.facebook.com/" target="_blank" title="Facebook" class="social-link">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://www.instagram.com/" target="_blank" title="Instagram" class="social-link">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://wa.me/573236871881" target="_blank" title="WhatsApp" class="social-link">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Información de la Empresa -->
                    <div class="footer-col footer-animate">
                        <h4 class="footer-heading">Información de la Empresa</h4>
                        <ul class="footer-list footer-list--info">
                            <li><strong>Razón social:</strong> PLAZA EDUCATIVA GYS S.A.S</li>
                            <li><strong>NIT:</strong> 902024002-0</li>
                            <li><strong>Correo:</strong> <a href="mailto:meritoconstruyendoexcelencia@gmail.com">meritoconstruyendoexcelencia@gmail.com</a></li>
                        </ul>
                    </div>

                    <!-- Información -->
                    <div class="footer-col footer-animate">
                        <h4 class="footer-heading">Información</h4>
                        <ul class="footer-list">
                            <li><Link href="/login">Iniciar Sesión</Link></li>
                            <li><Link href="/register">Crear Cuenta</Link></li>
                            <li><Link href="/mis_cursos">Mis Materiales</Link></li>
                        </ul>
                    </div>

                    <!-- Contacto -->
                    <div class="footer-col footer-animate">
                        <h4 class="footer-heading">Contacto</h4>
                        <ul class="footer-list footer-list--contact">
                            <li>
                                <i class="fab fa-whatsapp"></i>
                                <div>
                                    <span class="contact-label">Atención:</span>
                                    <a href="https://wa.me/573236871881?text=Hola,%20necesito%20atenci%C3%B3n%20para%20un%20material%20de%20estudio" target="_blank" rel="noopener noreferrer">+57 3236871881</a>
                                </div>
                            </li>
                            <li>
                                <i class="fab fa-whatsapp"></i>
                                <div>
                                    <span class="contact-label">Soporte:</span>
                                    <a href="https://wa.me/573236871881?text=Hola,%20necesito%20soporte%20t%C3%A9cnico" target="_blank" rel="noopener noreferrer">+57 3236871881</a>
                                </div>
                            </li>
                        </ul>
                        <a class="footer-btn" href="https://wa.me/573236871881" target="_blank">
                            Contáctanos
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-inner">
                    <p class="footer-copy">
                        &copy; {{ currentYear }} Construyendo Méritos con Excelencia. Todos los derechos reservados.
                    </p>
                    <ul class="footer-legal">
                        <li><Link href="/terminos-de-servicio">Términos de servicio</Link></li>
                        <li><Link href="/politica-de-privacidad">Política de privacidad</Link></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.site-footer {
    background: #11364f;
    color: #ffffff;
}

.footer-main {
    padding: 28px 0 20px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}

.footer-grid {
    display: grid;
    grid-template-columns: 1.4fr 1.2fr 0.9fr 1.1fr;
    gap: 24px;
}

.footer-col {
    min-width: 0;
}

.footer-animate {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.footer-animate.visible {
    opacity: 1;
    transform: translateY(0);
}

.footer-logo {
    display: inline-block;
    margin-bottom: 16px;
    transition: opacity 0.3s ease;
}

.footer-logo:hover {
    opacity: 0.85;
}

.footer-logo img {
    height: 28px;
    width: auto;
}

.footer-desc {
    color: rgba(255, 255, 255, 0.7);
    font-size: 12px;
    line-height: 1.6;
    margin: 0 0 12px;
}

.footer-social {
    display: flex;
    gap: 10px;
}

.social-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    font-size: 16px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: #f5e42c;
    color: #11364f;
    transform: translateY(-2px);
}

.footer-heading {
    font-size: 12px;
    font-weight: 700;
    color: #f5e42c;
    margin: 0 0 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    padding-bottom: 8px;
}

.footer-heading::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 32px;
    height: 3px;
    background: #f5e42c;
    border-radius: 2px;
}

.footer-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-list li {
    margin-bottom: 6px;
}

.footer-list a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-block;
}

.footer-list a:hover {
    color: #f5e42c;
    transform: translateX(4px);
}

.footer-list--info li {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    line-height: 1.5;
}

.footer-list--info strong {
    color: rgba(255, 255, 255, 0.9);
    font-weight: 600;
}

.footer-list--info a {
    color: #f5e42c;
    font-size: 11px;
}

.footer-list--info a:hover {
    color: #ffe566;
    transform: none;
}

.footer-list--contact li {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 8px;
}

.footer-list--contact li > i {
    font-size: 14px;
    color: #f5e42c;
    margin-top: 2px;
    flex-shrink: 0;
    width: 16px;
}

.footer-list--contact .contact-label {
    display: block;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
}

.footer-list--contact a {
    font-size: 11px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.9);
}

.footer-list--contact a:hover {
    color: #f5e42c;
    transform: none;
}

.footer-list--contact span {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.9);
}

.footer-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f5e42c;
    color: #11364f;
    font-weight: 700;
    font-size: 12px;
    padding: 8px 18px;
    border-radius: 6px;
    text-decoration: none;
    margin-top: 6px;
    transition: all 0.3s ease;
}

.footer-btn:hover {
    background: #ffe566;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 228, 44, 0.3);
}

.footer-btn i {
    font-size: 14px;
    transition: transform 0.3s ease;
}

.footer-btn:hover i {
    transform: translateX(4px);
}

.footer-disclaimer-wrapper {
    padding: 0 0 24px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    padding-top: 24px;
}

.footer-disclaimer-wrapper--top {
    border-top: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding-top: 0;
    padding-bottom: 24px;
    margin-bottom: 32px;
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding: 20px 0;
}

.footer-bottom-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.footer-copy {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
}

.footer-legal {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    gap: 20px;
}

.footer-legal a {
    color: rgba(255, 255, 255, 0.6);
    text-decoration: none;
    font-size: 13px;
    transition: color 0.3s ease;
}

.footer-legal a:hover {
    color: #f5e42c;
}

@media (max-width: 900px) {
    .footer-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 600px) {
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }

    .footer-main {
        padding: 40px 0 30px;
    }

    .footer-bottom-inner {
        flex-direction: column;
        text-align: center;
    }

    .footer-legal {
        justify-content: center;
    }
}
</style>
