<script setup lang="ts">
const whatsappNumber = '+573122372052';
const whatsappUrl = `https://wa.me/${whatsappNumber.replace(/\D/g, '')}?text=Hola`;
</script>

<template>
    <div class="whatsapp-wrapper">
        <a :href="whatsappUrl" target="_blank" rel="noopener noreferrer" class="whatsapp-button" title="Comunícate con nosotros por WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
        <div class="whatsapp-tooltip">¿Necesitas ayuda?</div>
    </div>
</template>

<style scoped>
.whatsapp-wrapper {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 999;
}

.whatsapp-button {
    width: 60px;
    height: 60px;
    background: #133a54;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 30px;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.4);
    transition: all 0.3s ease;
    text-decoration: none;
}

.whatsapp-button:hover {
    transform: scale(1.1) translateY(-5px);
    box-shadow: 0 8px 20px rgba(19, 58, 84, 0.6);
}

.whatsapp-button:active {
    transform: scale(0.95);
}

.whatsapp-tooltip {
    position: absolute;
    right: 80px;
    top: 50%;
    transform: translateY(-50%) translateX(20px);
    background: #133a54;
    color: white;
    padding: 10px 16px;
    border-radius: 18px;
    font-size: 14px;
    font-weight: 600;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(19, 58, 84, 0.3);
    opacity: 0;
    transition: all 0.3s ease;
    pointer-events: none;
}

.whatsapp-tooltip::after {
    content: '';
    position: absolute;
    right: -8px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 8px solid #133a54;
    border-top: 6px solid transparent;
    border-bottom: 6px solid transparent;
}

.whatsapp-wrapper:hover .whatsapp-tooltip {
    opacity: 1;
    transform: translateY(-50%) translateX(0);
}

/* Responsive */
@media (max-width: 768px) {
    .whatsapp-wrapper {
        bottom: 1rem;
        right: 1rem;
    }

    .whatsapp-button {
        width: 50px;
        height: 50px;
        font-size: 24px;
    }

    .whatsapp-tooltip {
        font-size: 12px;
        padding: 6px 12px;
    }
}
</style>
