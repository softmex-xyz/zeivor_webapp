<script setup lang="ts">
import Zeivor1 from "../../../images/Zeivor1.jpeg";
import Zeivor4 from "../../../images/Zeivor4.jpeg";
import registraIcon from "../../../images/iconos/registra.svg";
import solicitaIcon from "../../../images/iconos/solicita.svg";
import contrataIcon from "../../../images/iconos/contrata.svg";

interface StepItem {
    id: string;
    title: string;
    description: string;
}

const steps: StepItem[] = [
    {
        id: "registra",
        title: "REGISTRA",
        description: "El experto en su oficio descarga la APP y se registra.",
    },
    {
        id: "solicita",
        title: "SOLICITA",
        description:
            "El usuario solicita un experto desde la APP para realizar el trabajo.",
    },
    {
        id: "contrata",
        title: "CONTRATA",
        description:
            "Una vez hecho el acuerdo entre experto y usuario, el pago es directo.",
    },
];

const images: { src: string; alt: string }[] = [
    {
        src: Zeivor1,
        alt: "Conoce a los profesionales de Zeivor",
    },
    {
        src: Zeivor4,
        alt: "Encuentra al experto ideal en Zeivor",
    },
];
</script>

<template>
    <section
        aria-labelledby="how-it-works-title"
        class="w-full bg-white px-4 py-14 sm:px-6 lg:px-8"
    >
        <div class="mx-auto max-w-6xl">
            <h2
                id="how-it-works-title"
                class="text-center text-3xl font-normal tracking-tight text-neutral-900 sm:text-5xl"
            >
                ¿Cómo funciona Zeivor?
            </h2>

            <div
                class="relative mx-auto mt-12 flex max-w-4xl items-center justify-center gap-4 overflow-hidden rounded-2xl"
            >
                <img
                    :src="images[0].src"
                    :alt="images[0].alt"
                    class="h-64 w-1/2 object-contain transition-opacity duration-500 sm:h-96"
                />
                <img
                    :src="images[1].src"
                    :alt="images[1].alt"
                    class="h-64 w-1/2 object-contain transition-opacity duration-500 sm:h-96"
                />
            </div>
            <p
                class="mx-auto mt-8 max-w-4xl text-center text-base leading-relaxed text-neutral-800 sm:text-lg"
            >
                <strong class="font-semibold text-neutral-900">En Zeivor</strong
                >, los profesionales pueden
                <strong class="font-semibold text-neutral-900"
                    >dar a conocer su experiencia</strong
                >
                y más clientes pueden encontrarlos.
                <strong class="font-semibold text-neutral-900"
                    >¿Buscas una solución?</strong
                >
                Elige al experto ideal de una lista de profesionales y encuentra
                la ayuda que necesitas.
                <span class="block mt-1">
                    <strong class="font-semibold text-neutral-900"
                        >Zeivor:</strong
                    >
                    conecta con quien puede ayudarte.
                </span>
            </p>

            <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-3">
                <article
                    v-for="step in steps"
                    :key="step.id"
                    class="rounded-xl bg-[#fbd5da] p-7 text-left transition hover:bg-[#f9c7ce]"
                >
                    <div class="mb-4 text-[#ff5a6e]">
                        <!-- Icono REGISTRA -->
                        <img
                            v-if="step.id === 'registra'"
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                            alt="Registra"
                            :src="registraIcon"
                        />

                        <img
                            v-else-if="step.id === 'solicita'"
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                            alt="Solicita"
                            :src="solicitaIcon"
                        />

                        <!-- Icono CONTRATA -->
                        <img
                            v-else
                            class="h-7 w-7"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                            alt="Contrata"
                            :src="contrataIcon"
                        />
                    </div>

                    <h3
                        class="text-sm font-bold uppercase tracking-wide text-neutral-900"
                    >
                        {{ step.title }}
                    </h3>

                    <p class="mt-2 text-sm leading-relaxed text-neutral-800">
                        {{ step.description }}
                    </p>
                </article>
            </div>
        </div>
    </section>
</template>
