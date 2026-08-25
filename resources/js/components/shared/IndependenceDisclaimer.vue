<script setup lang="ts">
withDefaults(defineProps<{
    variant?: 'hero' | 'banner' | 'compact' | 'footer' | 'card' | 'legal';
    showIcon?: boolean;
}>(), {
    variant: 'banner',
    showIcon: true,
});
</script>

<template>
    <!-- Hero Variant: highly visible inside hero section -->
    <div v-if="variant === 'hero'" class="disclaimer disclaimer--hero">
        <div class="disclaimer__icon" v-if="showIcon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="disclaimer__content">
            <strong class="disclaimer__title">Aviso de Independencia</strong>
            <p class="disclaimer__text">
                Este sitio web <strong>meritoconstruyendoexcelencia.com</strong> es una plataforma <strong>privada e independiente</strong>. <strong>NO</strong> formamos parte de la <strong>Procuraduría General de la Nación</strong>, ni tenemos vínculo institucional, patrocinio o relación comercial con la convocatoria oficial. El contenido ofrecido consiste <strong>exclusivamente en material pedagógico de estudio y preparación general</strong> para aspirantes. No comercializamos inscripciones, certificados ni influimos en los resultados del proceso de selección.
            </p>
        </div>
    </div>

    <!-- Banner Variant: horizontal alert -->
    <div v-else-if="variant === 'banner'" class="disclaimer disclaimer--banner" role="alert">
        <div class="disclaimer__icon" v-if="showIcon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <p class="disclaimer__text">
            <strong>Aviso:</strong> Sitio web privado e independiente de la Procuraduría General de la Nación. No realizamos trámites ni inscripciones; ofrecemos únicamente <strong>material de estudio</strong> para la convocatoria.
        </p>
    </div>

    <!-- Compact Variant: single line for emails/checkout -->
    <div v-else-if="variant === 'compact'" class="disclaimer disclaimer--compact">
        <p class="disclaimer__text">
            <strong>Aviso:</strong> Sitio web privado e independiente de la Procuraduría. No realizamos trámites ni inscripciones; ofrecemos únicamente material de estudio para la convocatoria.
        </p>
    </div>

    <!-- Footer Variant: subtle but always visible -->
    <div v-else-if="variant === 'footer'" class="disclaimer disclaimer--footer">
        <div class="disclaimer__icon" v-if="showIcon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <p class="disclaimer__text">
            <strong>Aviso Legal:</strong> meritoconstruyendoexcelencia.com es una plataforma <strong>privada e independiente</strong>. No formamos parte de la Procuraduría General de la Nación ni tenemos vínculo institucional con la convocatoria oficial. Ofrecemos exclusivamente <strong>material de preparación independiente</strong> para postulantes. No comercializamos inscripciones ni influimos en los resultados.
        </p>
    </div>

    <!-- Card Variant: for product pages and cart -->
    <div v-else-if="variant === 'card'" class="disclaimer disclaimer--card">
        <div class="disclaimer__icon" v-if="showIcon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="disclaimer__content">
            <strong class="disclaimer__title">Material de preparación independiente</strong>
            <p class="disclaimer__text">
                Sitio web privado e independiente de la Procuraduría General de la Nación. No realizamos trámites ni inscripciones; ofrecemos únicamente material de estudio para la convocatoria.
            </p>
        </div>
    </div>

    <!-- Legal Variant: for Terms/Policies pages -->
    <div v-else-if="variant === 'legal'" class="disclaimer disclaimer--legal">
        <div class="disclaimer__icon" v-if="showIcon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
        </div>
        <div class="disclaimer__content">
            <strong class="disclaimer__title">Aviso de Independencia y Descargo de Responsabilidad</strong>
            <p class="disclaimer__text">
                Este sitio web (meritoconstruyendoexcelencia.com) es una plataforma privada e independiente. <strong>NO formamos parte de la Procuraduría General de la Nación</strong>, ni tenemos vínculo institucional, patrocinio o relación comercial con la convocatoria oficial. El contenido ofrecido consiste exclusivamente en material pedagógico de estudio y preparación general para aspirantes. No comercializamos inscripciones, certificados ni influimos en los resultados del proceso de selección.
            </p>
            <p class="disclaimer__text disclaimer__text--secondary">
                Evitamos el uso de logotipos, escudos o identidad oficial de la Procuraduría. Todo material se comercializa como <em>"Material de preparación independiente"</em> para postulantes.
            </p>
        </div>
    </div>
</template>

<style scoped>
.disclaimer {
    display: flex;
    gap: 12px;
    border-radius: 12px;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    line-height: 1.6;
}

.disclaimer__icon {
    flex-shrink: 0;
    display: flex;
    align-items: flex-start;
    padding-top: 2px;
}

.disclaimer__title {
    display: block;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 4px;
}

.disclaimer__text {
    margin: 0;
    font-size: 13px;
}

.disclaimer__text--secondary {
    margin-top: 8px;
    font-size: 12px;
    opacity: 0.85;
}

/* HERO */
.disclaimer--hero {
    max-width: 780px;
    margin: 0 auto;
    margin-top: 24px;
    padding: 16px 20px;
    background: rgba(255,255,255,0.92);
    border: 1.5px solid rgba(17,54,79,0.15);
    backdrop-filter: blur(8px);
    box-shadow: 0 8px 24px rgba(17,54,79,0.08);
    color: #11364f;
    text-align: left;
    animation: fadeInUp 0.8s ease-out 0.4s backwards;
}

.disclaimer--hero .disclaimer__icon {
    color: #b45309;
}

.disclaimer--hero .disclaimer__title {
    color: #92400e;
}

.disclaimer--hero .disclaimer__text {
    font-size: 13px;
    color: #334155;
}

.disclaimer--hero .disclaimer__text strong {
    color: #11364f;
}

@media (max-width: 768px) {
    .disclaimer--hero {
        margin-top: 16px;
        padding: 14px 16px;
    }
    .disclaimer--hero .disclaimer__text {
        font-size: 12px;
    }
}

/* BANNER */
.disclaimer--banner {
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 10px 16px;
    background: #fef3c7;
    border: 1px solid #fde68a;
    border-left: 4px solid #f59e0b;
    border-radius: 8px;
    color: #92400e;
}

.disclaimer--banner .disclaimer__text {
    font-size: 12.5px;
}

.disclaimer--banner .disclaimer__icon {
    color: #d97706;
}

/* COMPACT */
.disclaimer--compact {
    padding: 8px 12px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 6px;
    color: #92400e;
    font-size: 11px;
    text-align: center;
}

/* FOOTER */
.disclaimer--footer {
    margin-top: 24px;
    padding: 16px 20px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 10px;
    color: rgba(255,255,255,0.85);
    align-items: flex-start;
}

.disclaimer--footer .disclaimer__icon {
    color: #f5e42c;
}

.disclaimer--footer .disclaimer__text {
    font-size: 12px;
    line-height: 1.7;
    color: rgba(255,255,255,0.75);
}

.disclaimer--footer .disclaimer__text strong {
    color: #f5e42c;
    font-weight: 700;
}

/* CARD */
.disclaimer--card {
    padding: 14px 16px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    color: #78350f;
}

.disclaimer--card .disclaimer__icon {
    color: #d97706;
}

.disclaimer--card .disclaimer__title {
    font-size: 12px;
    color: #92400e;
}

.disclaimer--card .disclaimer__text {
    font-size: 12px;
    color: #92400e;
    margin-top: 2px;
}

/* LEGAL */
.disclaimer--legal {
    padding: 20px 24px;
    background: #fffbeb;
    border: 1.5px solid #f59e0b;
    border-left: 4px solid #d97706;
    border-radius: 12px;
    color: #78350f;
    margin-bottom: 32px;
}

.disclaimer--legal .disclaimer__icon {
    color: #d97706;
}

.disclaimer--legal .disclaimer__title {
    font-size: 14px;
    color: #92400e;
    margin-bottom: 8px;
}

.disclaimer--legal .disclaimer__text {
    font-size: 14px;
    color: #78350f;
    line-height: 1.7;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
