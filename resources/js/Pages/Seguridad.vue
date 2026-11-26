<script setup lang="ts">
import InfoCard from "../Components/InfoCard.vue";
import AppLayout from "../Layouts/AppLayout.vue";
import expertoImg from "../../images/experto.svg";

interface SecurityPoint {
    title: string;
    description: string;
    shape: "circle" | "diamond" | "triangle";
}

interface FAQItem {
    question: string;
    answer: string;
}

const pointsInfoExperts: SecurityPoint[] = [
    {
        title: "Clientes verificados",
        description:
            "Conoce el perfil y los datos básicos de quien solicita tu servicio antes de acudir.",
        shape: "circle",
    },
    {
        title: "Calificaciones mutuas",
        description:
            "Revisa la reputación y comentarios de otros expertos sobre cada cliente para aceptar trabajos con total confianza.",
        shape: "diamond",
    },
    {
        title: "Registro geolocalizado",
        description:
            "Cada solicitud y ubicación queda respaldada dentro de la plataforma para mayor control y trazabilidad.",
        shape: "triangle",
    },
    {
        title: "Trato y pago directo",
        description:
            "Acuerda los detalles y recibe tu pago sin intermediarios ni retenciones inesperadas.",
        shape: "triangle",
    },
];

const pointsInfoUsers: SecurityPoint[] = [
    {
        title: "Expertos verificados",
        description:
            "Contamos con un filtro de registro y validación de identidad para que sepas exactamente a quién le abres las puertas de tu hogar u oficina",
        shape: "circle",
    },
    {
        title: "Reputación transparente",
        description:
            "Consulta las calificaciones, reseñas y trabajos previos de otros usuarios antes de elegir al profesional ideal",
        shape: "diamond",
    },
    {
        title: "Acuerdo claro y sin sorpresas",
        description:
            "Comunícate directamente con el experto para definir detalles, tiempos y costos antes de confirmar el servicio",
        shape: "triangle",
    },
    {
        title: "Respaldo y soporte activo",
        description:
            "Nuestro equipo de atención está disponible para acompañarte y ayudarte ante cualquier duda o inconveniente",
        shape: "triangle",
    },
];

const faqs: FAQItem[] = [
    {
        question: "¿Cómo puedo descargar la aplicación?",
        answer: "Por medio de las tiendas App Store y Google Play Store.",
    },
    {
        question: "¿La aplicación es gratuita?",
        answer: "Sí, es gratuita para todo el público en general que desee contratar a un experto.",
    },
    {
        question:
            "¿Cuáles son las precauciones de seguridad que debo tomar al contratar a un experto?",
        answer: "Es esencial verificar la identificación oficial de cualquier desconocido antes de permitirle entrar a tu casa. Asegúrate de que coincida con la presentada en la plataforma. Ten en cuenta que los acuerdos se establecen entre las partes, por lo que debes ejercer la misma precaución que si estuvieras contratando a alguien completamente nuevo. Además, las calificaciones y recomendaciones de otros usuarios respaldan la experiencia del experto.",
    },
    {
        question: "¿Hay alguna obligación de cobro?",
        answer: "En absoluto. Tanto expertos como clientes comprenden que las estimaciones y visitas no implican un compromiso de contratación. Sin embargo, recomendamos que antes de contratar observes con atención si el experto genera algún tipo de cobro por la primera visita.",
    },
    {
        question: "¿Cuántas categorías existen?",
        answer: "Existen más de 50 categorías, que incluyen oficios, profesiones y otras actividades.",
    },
    {
        question:
            "¿Qué información necesito proporcionar durante el proceso de registro?",
        answer: "Necesitas proporcionar tu correo electrónico, identificación oficial y una selfie de registro.",
    },
    {
        question:
            "¿Cómo puedo encontrar ayuda si tengo problemas con la aplicación?",
        answer: "Puedes comunicarte con nuestro equipo de soporte a través de WhatsApp.",
    },
    {
        question:
            "¿Qué pasa si no encuentro el oficio, profesión o actividad que realizo?",
        answer: "La aplicación cuenta con un apartado donde puedes agregar el oficio, profesión o actividad comercial que realizas. Una vez que sea autorizado, se verá reflejado en la app.",
    },
    {
        question: "¿Cómo puedo realizar pagos dentro de la aplicación?",
        answer: "Puedes realizar tus pagos a través de PayPal y Stripe.",
    },
];
</script>

<template>
    <AppLayout current-path="/seguridad">
        <section
            aria-labelledby="security-experts-title"
            class="relative overflow-hidden bg-white py-12 sm:py-16 lg:py-20"
        >
            <div class="relative mx-auto max-w-6xl px-6">
                <div
                    class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16"
                >
                    <div class="flex flex-col items-center text-center">
                        <h1
                            id="security-experts-title"
                            class="max-w-sm text-3xl font-bold tracking-tight text-neutral-950 sm:text-4xl lg:text-[42px] lg:leading-tight"
                        >
                            Seguridad para los expertos
                        </h1>

                        <div class="mt-10 w-full max-w-md">
                            <img
                                :src="expertoImg"
                                alt="Seguridad para los expertos en Zeivor"
                                class="mx-auto w-full max-w-md object-contain"
                            />
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 sm:gap-5">
                        <InfoCard
                            v-for="point in pointsInfoExperts"
                            :key="point.title"
                            :title="point.title"
                            :description="point.description"
                            :shape="point.shape"
                        />
                    </div>
                </div>
            </div>

            <!-- Seguridad para los usuarios -->
            <div class="relative mx-auto max-w-6xl px-6 mt-10">
                <div
                    class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16"
                >
                    <div
                        class="order-1 flex flex-col items-center text-center lg:order-2"
                    >
                        <h2
                            id="security-users-title"
                            class="max-w-sm text-3xl font-bold tracking-tight text-neutral-950 sm:text-4xl lg:text-[42px] lg:leading-tight"
                        >
                            Seguridad para los usuarios
                        </h2>

                        <div class="mt-10 w-full max-w-md">
                            <img
                                :src="expertoImg"
                                alt="Seguridad para los usuarios en Zeivor"
                                class="mx-auto w-full max-w-md object-contain"
                            />
                        </div>
                    </div>

                    <div
                        class="order-2 flex flex-col gap-4 sm:gap-5 lg:order-1"
                    >
                        <InfoCard
                            v-for="point in pointsInfoUsers"
                            :key="point.title"
                            :title="point.title"
                            :description="point.description"
                            :shape="point.shape"
                        />
                    </div>
                </div>
            </div>

            <!-- Preguntas frecuentes -->
            <section
                id="preguntas-frecuentes"
                class="relative mx-auto mt-24 max-w-6xl px-6 pb-20"
            >
                <div class="mb-14 text-center">
                    <h2
                        class="text-3xl font-bold tracking-tight text-neutral-950 sm:text-4xl"
                    >
                        Preguntas frecuentes
                    </h2>
                    <p
                        class="mx-auto mt-4 max-w-2xl text-base leading-7 text-neutral-500"
                    >
                        Conoce más sobre Zeivor y resuelve tus dudas sobre
                        nuestra plataforma.
                    </p>
                </div>

                <div class="space-y-6">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="faq.question"
                        class="flex w-full"
                        :class="
                            index % 2 === 0 ? 'justify-start' : 'justify-end'
                        "
                    >
                        <div
                            class="w-full rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm transition-all duration-300 hover:shadow-md sm:w-[85%] lg:w-[75%]"
                        >
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-bold text-white"
                                    style="background-color: rgb(253, 73, 92)"
                                >
                                    {{ index + 1 }}
                                </div>

                                <div class="flex-1">
                                    <h3
                                        class="text-lg font-bold leading-snug text-neutral-950"
                                    >
                                        {{ faq.question }}
                                    </h3>

                                    <p
                                        class="mt-3 text-sm leading-7 text-neutral-600 sm:text-base"
                                    >
                                        {{ faq.answer }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </AppLayout>
</template>
