<script setup lang="ts">
import AppLayout from "../Layouts/AppLayout.vue";
import descargaImg from "../../images/usuarios/descarga.svg";
import buscaImg from "../../images/usuarios/busca.svg";
import conectaImg from "../../images/usuarios/conecta.svg";
import resuelveImg from "../../images/usuarios/resuelve.svg";

interface UseAppStep {
    title: string;
    description: string;
    image: string;
}

const dataApp: UseAppStep[] = [
    {
        title: "Descarga y regístrate",
        description:
            "Baja la app gratis en tu celular, crea tu perfil en minutos y activa tu ubicación",
        image: descargaImg,
    },
    {
        title: "Busca lo que necesitas:",
        description:
            "Busca el oficio o profesión que requieres y explora a los expertos verificados más cercanos a ti",
        image: buscaImg,
    },
    {
        title: "Conecta y acuerda:",
        description:
            "Envía tu solicitud o espera que el experto te contacte, revisa las calificaciones del profesional y define directamente con él los detalles, horario y costo del servicio.",
        image: conectaImg,
    },
    {
        title: "Resuelve y califica:",
        description:
            "Recibe al experto en tu hogar u oficina, realiza el pago directo al finalizar y califica tu experiencia para ayudar a la comunidad.",
        image: resuelveImg,
    },
];
</script>

<template>
    <AppLayout current-path="/usuarios">
        <section
            aria-labelledby="app-usage-title"
            class="bg-white py-12 sm:py-16 lg:py-20"
        >
            <div class="mx-auto max-w-6xl px-6">
                <header class="text-center">
                    <h1
                        id="app-usage-title"
                        class="text-3xl font-bold tracking-tight text-neutral-950 sm:text-4xl lg:text-[42px] lg:leading-tight"
                    >
                        Uso de la aplicación
                    </h1>
                </header>

                <div class="mt-12 space-y-12 sm:mt-16 lg:space-y-16">
                    <article
                        v-for="(point, index) in dataApp"
                        :key="point.title"
                        class="grid grid-cols-1 items-center gap-8 md:grid-cols-2 lg:gap-14"
                    >
                        <div
                            :class="[
                                'flex h-64 w-full items-center justify-center overflow-hidden rounded-xl p-6 sm:h-80',
                                index % 2 === 1 ? 'md:order-2' : '',
                            ]"
                        >
                            <img
                                :src="point.image"
                                :alt="point.title"
                                class="h-full w-full object-contain"
                            />
                        </div>

                        <div :class="index % 2 === 1 ? 'md:order-1' : ''">
                            <h2
                                class="mt-4 text-2xl font-bold leading-snug tracking-tight text-neutral-950 sm:text-3xl"
                            >
                                {{ point.title }}
                            </h2>

                            <p
                                class="mt-3 max-w-md text-sm leading-relaxed text-neutral-600 sm:text-base"
                            >
                                {{ point.description }}
                            </p>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
