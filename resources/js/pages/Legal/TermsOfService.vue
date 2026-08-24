<script setup lang="ts">
import SeoHead from '@/components/shared/SeoHead.vue';
import AppWebLayout from '@/layouts/AppWebLayout.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <SeoHead
        title="Términos de Servicio - Construyendo Méritos con Excelencia"
        description="Lee nuestros términos de servicio para entender las normas de uso de la plataforma de Construyendo Méritos con Excelencia."
        :url="page.url"
    />
    <AppWebLayout>
        <div class="legal-content-wrapper">
            <div class="container">
                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <div class="legal-header">
                            <h1>Términos y Condiciones de Servicio</h1>
                            <h2 class="subtitle">CONSTRUYENDO MÉRITOS CON EXCELENCIA</h2>
                            <p class="last-update">Última actualización: 20 de diciembre de 2025</p>
                        </div>

                        <div class="legal-content">
                            <section class="legal-section">
                                <h3>1. Identificación del prestador del servicio</h3>
                                <p>
                                    CONSTRUYENDO MÉRITOS CON EXCELENCIA es una plataforma educativa operada por PLAZA EDUCATIVA GYS S.A.S., sociedad legalmente
                                    constituida en la República de Colombia, identificada con NIT 902024002-0, con domicilio en Sincelejo, Sucre.
                                </p>
                                <p>
                                    Para efectos de estos términos, se entenderá como "la Plataforma" a Construyendo Méritos con Excelencia, y como "el Usuario" a toda
                                    persona que acceda, navegue, se registre o adquiera cualquiera de los productos o servicios ofrecidos.
                                </p>
                            </section>

                            <section class="legal-section">
                                <h3>2. Aceptación de los términos</h3>
                                <p>
                                    Al acceder, registrarse o adquirir cualquier producto o servicio ofrecido por CONSTRUYENDO MÉRITOS CON EXCELENCIA, el Usuario
                                    declara haber leído, comprendido y aceptado de manera expresa estos Términos y Condiciones, así como la Política
                                    de Privacidad.
                                </p>
                                <p>Si el Usuario no está de acuerdo con estos términos, deberá abstenerse de utilizar la Plataforma.</p>
                            </section>

                            <section class="legal-section">
                                <h3>3. Objeto del servicio</h3>
                                <p>
                                    CONSTRUYENDO MÉRITOS CON EXCELENCIA ofrece material educativo digital, guías de estudio, simulacros tipo prueba, contenidos de
                                    apoyo académico y, en algunos casos, cursos virtuales, orientados a la preparación para convocatorias públicas,
                                    concursos de mérito y procesos de selección en Colombia.
                                </p>
                                <p>
                                    Los contenidos tienen fines pedagógicos y de entrenamiento académico, y no constituyen garantía de aprobación,
                                    nombramiento o resultado favorable en ninguna convocatoria.
                                </p>
                            </section>

                            <section class="legal-section">
                                <h3>4. Registro y uso de la plataforma</h3>
                                <p>
                                    Para acceder a ciertos contenidos, el Usuario deberá crear una cuenta proporcionando información veraz, completa y
                                    actualizada.
                                </p>
                                <p>El Usuario es responsable de:</p>
                                <ul>
                                    <li>Mantener la confidencialidad de sus credenciales.</li>
                                    <li>Todas las actividades realizadas desde su cuenta.</li>
                                    <li>No compartir, ceder o revender el acceso a la plataforma.</li>
                                </ul>
                                <p>CONSTRUYENDO MÉRITOS CON EXCELENCIA podrá suspender o cancelar cuentas que evidencien uso indebido o fraudulento.</p>
                            </section>

                            <section class="legal-section">
                                <h3>5. Acceso a los contenidos</h3>
                                <p>El acceso a los contenidos es personal, intransferible y por tiempo limitado, según el producto adquirido.</p>
                                <ul>
                                    <li>El tiempo de acceso será el informado al momento de la compra.</li>
                                    <li>Finalizado el plazo, el Usuario perderá el acceso automáticamente, sin necesidad de aviso previo.</li>
                                    <li>El material no es descargable y solo se puede visualizar desde nuestra plataforma.</li>
                                </ul>
                            </section>

                            <section class="legal-section">
                                <h3>6. Precios y pagos</h3>
                                <p>
                                    Los precios se encuentran expresados en pesos colombianos (COP) e incluyen los impuestos aplicables, salvo
                                    indicación expresa en contrario.
                                </p>
                                <ul>
                                    <li>El pago se realiza a través de los medios habilitados en la Plataforma.</li>
                                    <li>Una vez confirmado el pago, se habilitará el acceso al contenido correspondiente.</li>
                                </ul>
                            </section>

                            <section class="legal-section">
                                <h3>7. Política de no reembolso</h3>
                                <p>Debido a la naturaleza digital e inmediata de los contenidos ofrecidos:</p>
                                <ul>
                                    <li>
                                        No se realizan reembolsos, devoluciones ni cancelaciones, una vez el Usuario haya obtenido acceso al material,
                                        salvo obligación legal expresa.
                                    </li>
                                    <li>El Usuario acepta esta condición al momento de realizar la compra.</li>
                                </ul>
                            </section>

                            <section class="legal-section">
                                <h3>8. Propiedad intelectual</h3>
                                <p>
                                    Todos los contenidos disponibles en CONSTRUYENDO MÉRITOS CON EXCELENCIA (textos, preguntas, simulacros, videos, documentos,
                                    estructura pedagógica, marcas, logotipos y diseños) son propiedad exclusiva de PLAZA EDUCATIVA GYS S.A.S. o
                                    cuentan con licencia para su uso.
                                </p>
                                <p>Está estrictamente prohibido:</p>
                                <ul>
                                    <li>Copiar, reproducir, distribuir o revender los contenidos.</li>
                                    <li>Compartir capturas, archivos o accesos.</li>
                                    <li>Usar los contenidos con fines comerciales sin autorización escrita.</li>
                                </ul>
                                <p>El incumplimiento podrá dar lugar a acciones legales.</p>
                            </section>

                            <section class="legal-section">
                                <h3>9. Uso permitido y prohibido</h3>
                                <p>El Usuario se compromete a:</p>
                                <ul>
                                    <li>Usar la Plataforma únicamente con fines personales y académicos.</li>
                                    <li>No realizar actos que afecten la seguridad, funcionamiento o integridad del sistema.</li>
                                </ul>
                                <p>Se prohíbe expresamente:</p>
                                <ul>
                                    <li>El uso automatizado o masivo de la plataforma.</li>
                                    <li>La suplantación de identidad.</li>
                                    <li>La difusión de contenido falso, ofensivo o ilegal.</li>
                                </ul>
                            </section>

                            <section class="legal-section">
                                <h3>10. Limitación de responsabilidad</h3>
                                <p>
                                    CONSTRUYENDO MÉRITOS CON EXCELENCIA no garantiza resultados específicos, puntajes mínimos, ni aprobación en procesos de selección.
                                </p>
                                <p>La Plataforma no se hace responsable por:</p>
                                <ul>
                                    <li>Decisiones adoptadas por el Usuario con base en los contenidos.</li>
                                    <li>Cambios normativos o en convocatorias oficiales.</li>
                                    <li>Fallas técnicas ajenas a su control.</li>
                                </ul>
                            </section>

                            <section class="legal-section">
                                <h3>11. Soporte y atención al usuario</h3>
                                <p>
                                    El soporte se brinda a través de los canales oficiales informados en la Plataforma, dentro de los horarios
                                    establecidos.
                                </p>
                                <p>
                                    CONSTRUYENDO MÉRITOS CON EXCELENCIA no está obligada a brindar asesorías personalizadas, salvo que estas hagan parte expresa del
                                    producto adquirido.
                                </p>
                            </section>

                            <section class="legal-section">
                                <h3>12. Modificaciones</h3>
                                <p>CONSTRUYENDO MÉRITOS CON EXCELENCIA se reserva el derecho de modificar estos Términos y Condiciones en cualquier momento.</p>
                                <p>Las modificaciones entrarán en vigor desde su publicación en la Plataforma.</p>
                            </section>

                            <section class="legal-section">
                                <h3>13. Legislación aplicable y jurisdicción</h3>
                                <p>Estos Términos se rigen por las leyes de la República de Colombia.</p>
                                <p>Cualquier controversia será sometida a los jueces competentes del domicilio del prestador del servicio.</p>
                            </section>

                            <section class="legal-section">
                                <h3>14. Contacto</h3>
                                <p>
                                    Para consultas, quejas o reclamos, el Usuario podrá comunicarse a través de los canales oficiales de GUÍAS Y
                                    SIMULACROS.
                                </p>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppWebLayout>
</template>

<style scoped>
.legal-content-wrapper {
    padding: 60px 0 80px;
    background: #ffffff;
}

.legal-header {
    text-align: center;
    margin-bottom: 50px;
    padding-top: 20px;
}

.legal-header h1 {
    font-size: 42px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.legal-header h2.subtitle {
    font-size: 28px;
    font-weight: 600;
    color: #ff751f;
    margin-bottom: 12px;
}

.legal-header .last-update {
    font-size: 14px;
    color: #666;
    margin-top: 8px;
}

.legal-content {
    padding: 0;
}

.legal-section {
    margin-bottom: 35px;
}

.legal-section:last-child {
    margin-bottom: 0;
}

.legal-section h2 {
    font-size: 20px;
    font-weight: 600;
    color: #ff751f;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 2px solid #ff751f;
    display: inline-block;
}

.legal-section h3 {
    font-size: 20px;
    font-weight: 600;
    color: #ff751f;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 2px solid #ff751f;
    display: inline-block;
}

.legal-section p {
    font-size: 15px;
    line-height: 1.7;
    color: #333;
    margin-bottom: 12px;
}

.legal-section p:last-child {
    margin-bottom: 0;
}

.legal-section ul {
    margin: 12px 0;
    padding-left: 25px;
}

.legal-section ul li {
    font-size: 15px;
    line-height: 1.7;
    color: #333;
    margin-bottom: 8px;
}

@media (max-width: 768px) {
    .legal-header h1 {
        font-size: 32px;
    }

    .legal-header h2.subtitle {
        font-size: 22px;
    }

    .legal-content-wrapper {
        padding: 40px 0 60px;
    }

    .legal-section h2,
    .legal-section h3 {
        font-size: 18px;
    }

    .legal-section p,
    .legal-section ul li {
        font-size: 14px;
    }
}
</style>
