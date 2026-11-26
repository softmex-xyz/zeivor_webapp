<script setup lang="ts">
import nosotrosImg from "../../../images/nosotros.png";

export interface PricingPlan {
    id: string;
    name: string;
    price: string;
    period: string;
    features: string[];
    ctaLabel: string;
    featured?: boolean;
    disabled?: boolean;
}

interface Props {
    plans?: PricingPlan[];
}

withDefaults(defineProps<Props>(), {
    plans: () => [
        {
            id: "basic",
            name: "Zeivor Básico",
            price: "$2",
            period: "/ mensual",
            features: [
                "Acceso a miles de clientes",
                "Soporte las 24 horas",
                "Las opciones más cercanas a tu ubicación",
            ],
            ctaLabel: "Registrarse",
            featured: true,
            disabled: false,
        },
        {
            id: "plus",
            name: "Zeivor Plus",
            price: "$5",
            period: "/ mensual",
            features: [
                "Acceso a miles de clientes",
                "Soporte las 24 horas",
                "Las opciones más cercanas a tu ubicación",
            ],
            ctaLabel: "Registrarse",
            featured: true,
            disabled: false,
        },
    ],
});
</script>

<template>
    <section
        aria-labelledby="pricing-title"
        class="w-full bg-white px-4 py-14 sm:px-6 lg:px-8"
    >
        <div class="mx-auto max-w-6xl">
            <h2
                id="pricing-title"
                class="text-center text-3xl font-normal tracking-tight text-neutral-900 sm:text-5xl"
            >
                Precios
            </h2>

            <!-- Bloque Nosotros (sin imágenes) -->
            <div class="mx-auto mt-10 max-w-5xl px-4">
                <div
                    class="relative rounded-2xl bg-[#fbd5da] px-6 py-10 sm:px-10 lg:py-16"
                >
                    <div class="flex flex-col items-center gap-8 md:flex-row">
                        <!-- Texto -->
                        <div class="w-full text-center md:w-1/2 md:text-left">
                            <h3
                                class="text-2xl font-bold text-neutral-900 sm:text-3xl"
                            >
                                Nosotros
                            </h3>

                            <p
                                class="mt-4 text-sm leading-relaxed text-neutral-800 sm:text-base"
                            >
                                Con la visión de conectar el talento local con
                                quienes buscan soluciones inmediatas,
                                desarrollamos una plataforma impulsada por
                                geolocalización que une de forma directa, rápida
                                y segura a usuarios con miles de expertos en
                                oficios y profesiones desde plomeros y
                                carpinteros hasta diseñadores o psicólogos
                                eliminando intermediarios y devolviéndole
                                tranquilidad y tiempo a la vida diaria.
                            </p>
                        </div>

                        <!-- Imagen -->
                        <div class="w-full md:w-1/2">
                            <img
                                :src="nosotrosImg"
                                alt="Plataforma Zeivor"
                                class="mx-auto w-full max-w-lg object-contain md:scale-110"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de planes -->
            <div
                class="mx-auto mt-12 grid max-w-4xl grid-cols-1 gap-8 md:grid-cols-2"
            >
                <article
                    v-for="plan in plans"
                    :key="plan.id"
                    :class="[
                        'flex flex-col justify-between rounded-2xl bg-white p-8 shadow-sm transition',
                        plan.featured
                            ? 'border border-[#ff5a6e] shadow-md'
                            : 'border border-neutral-200',
                    ]"
                >
                    <div>
                        <h3 class="text-base font-bold text-neutral-900">
                            {{ plan.name }}
                        </h3>

                        <div class="mt-4 flex items-baseline gap-1">
                            <span
                                class="text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl"
                            >
                                {{ plan.price }}
                            </span>
                            <span class="text-sm text-neutral-500">
                                {{ plan.period }}
                            </span>
                        </div>

                        <ul class="mt-6 space-y-3 text-sm text-neutral-700">
                            <li
                                v-for="feature in plan.features"
                                :key="feature"
                                class="flex items-start gap-2.5"
                            >
                                <svg
                                    class="mt-0.5 h-4 w-4 shrink-0 text-neutral-800"
                                    viewBox="0 0 20 20"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <polyline points="4 10 8 14 16 6" />
                                </svg>
                                <span>{{ feature }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8">
                        <button
                            type="button"
                            :disabled="plan.disabled"
                            :class="[
                                'w-full rounded-lg px-4 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2',
                                plan.disabled
                                    ? 'cursor-not-allowed bg-[#c8c6d7] text-white'
                                    : 'bg-[#ff5a6e] text-white hover:bg-[#f0475c] focus:ring-[#ff5a6e]',
                            ]"
                        >
                            {{ plan.ctaLabel }}
                        </button>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
