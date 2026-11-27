<script setup lang="ts">
interface Props {
    title?: string;
    subtitle?: string;
    ctaLabel?: string;
    href?: string;
}

withDefaults(defineProps<Props>(), {
    title: "Soluciones de expertos",
    subtitle:
        "Solicita un experto en su área de trabajo para arreglar un problema de casa, trabajo u oficina.",
    ctaLabel: "Conviertete en experto",
    href: "/experto",
});

const emit = defineEmits<{
    (e: "click"): void;
}>();

const handleClick = (event: MouseEvent) => {
    if (!event.defaultPrevented) {
        emit("click");
    }
};
</script>

<template>
    <section
        aria-labelledby="expert-banner-title"
        class="w-full bg-white px-4 py-12 sm:px-6 lg:px-8"
    >
        <div class="mx-auto flex max-w-5xl flex-col items-center text-center">
            <h1
                id="expert-banner-title"
                class="text-3xl font-normal tracking-tight text-neutral-900 sm:text-5xl"
            >
                {{ title }}
            </h1>

            <p
                class="mt-4 max-w-3xl text-base font-normal text-neutral-900 sm:text-lg"
            >
                {{ subtitle }}
            </p>

            <div class="mt-6">
                <a
                    v-if="href"
                    :href="href"
                    @click="handleClick"
                    class="inline-flex items-center justify-center rounded-xl bg-[#ff5a6e] px-6 py-3 text-sm font-medium text-white transition hover:bg-[#f0475c] focus:outline-none focus:ring-2 focus:ring-[#ff5a6e] focus:ring-offset-2"
                >
                    {{ ctaLabel }}
                </a>

                <button
                    v-else
                    type="button"
                    @click="handleClick"
                    class="inline-flex items-center justify-center rounded-xl bg-[#ff5a6e] px-6 py-3 text-sm font-medium text-white transition hover:bg-[#f0475c] focus:outline-none focus:ring-2 focus:ring-[#ff5a6e] focus:ring-offset-2"
                >
                    {{ ctaLabel }}
                </button>
            </div>
        </div>
    </section>
</template>
